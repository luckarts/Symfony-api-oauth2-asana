# Roadmap — Symfony API OAuth2 Asana (Task Manager)

> Ordre d'exécution optimal · chaque phase produit une app deployable et testable.
> Ref schéma DB → `docs/database-evolution.md`

---

## Fondation (parallèles)

| ID | Feature | Contenu |
|----|---------|---------|
| F001 | User OAuth2 + Auth | SecurityUser, token endpoint, AuthController |
| CI001 | CI Workflow | GitHub Actions 3 niveaux (lint / test / e2e) |
| ARCH009 | BC Services Split | Co-localisation config par BC |

---

## POC

| ID | Feature | Contenu |
|----|---------|---------|
| TM00-POC | User + OAuth2 | Auth, SecurityUser, token endpoint |
| TM03-POC | Task POC | title, status enum, user_id direct |
| TM04-POC | Comment POC | task_id direct, pas de polymorphisme |

---

## MVP Perso

> App personnelle fonctionnelle et déployable — sans organisation multi-tenant.

| ID | Feature | Contenu |
|----|---------|---------|
| TM02-MVP | Project perso | `project.user_id` (sans org) |
| TM02-COL | BoardColumn | lié au project |
| TM03-MVP | Task enrichie | `due_date`, `is_completed`, `order_index`, `column_id` |
| TM03-F002 | Subtasks | `parent_task_id` FK nullable (1 niveau) → materialized path si nesting illimité requis |
| TM06-MVP | Tag | CRUD + Task-Tag M2M |
| TM04-MVP | Comment | lié à task, simple |
| TM07-MVP | Symfony Workflow | `todo → in_progress → in_review → done` |

```
┌─ [EVENT-001] refactoring — après TM07-MVP ────────────────────────┐
│  EventDispatcherInterface en place, 0 consumer externe            │
│  Events : TaskCreated · TaskStatusChanged (Workflow natif)        │
│           SubtaskCompleted → recalcul % complétion parent         │
│  → listener interne synchrone dans BC Task                        │
└───────────────────────────────────────────────────────────────────┘
```

→ **app perso fonctionnelle, déployable**

---

## Phase 2.5 — Dépendances

| ID | Feature | Contenu |
|----|---------|---------|
| TM08 | TaskDependency | DAG + cycle detection |

```
┌─ [EVENT-003] feature — avec TM08 ─────────────────────────────────┐
│  Events : TaskBlocked · TaskUnblocked                              │
│  → consumer : notifier assignee (synchrone)                       │
└───────────────────────────────────────────────────────────────────┘
```

→ **app perso complète avant pivot multi-tenant**

---

## Pivot SaaS

| ID | Feature | Contenu |
|----|---------|---------|
| TM01-F001 | Organization | name, slug |
| TM01-F002 | OrganizationMember | OWNER / ADMIN / MEMBER |
| TM01-F003 | Invitation | email → rejoindre org |

```
┌─ [EVENT-002] feature — avec TM01-F003 ────────────────────────────┐
│  1ers consumers réels                                              │
│  Events : MemberInvited → Symfony Mailer (synchrone)              │
│           MemberJoined · ProjectMemberAdded                       │
│                                                                    │
│  [EVENT-006-a] signal possible ici : email bloque la réponse API  │
│  → MemberInvited migre en async (Messenger) si contention         │
└───────────────────────────────────────────────────────────────────┘
```

| ID | Feature | Contenu |
|----|---------|---------|
| TM02-UPD | Migration | `project.user_id → project.org_id` (Expand/Contract) |
| TM02-F002 | ProjectMember | qui voit quoi |

---

## Phase 3 — Collaboration

| ID | Feature | Contenu |
|----|---------|---------|
| TM04-ADV | Comment avancé | mentions, reactions |
| ATT01 | Attachment | upload / download / delete |

```
┌─ [EVENT-004] refactoring — avant ACT001 ──────────────────────────┐
│  Retrofit tous les events précédents → ActivityFeedProjection      │
│  EVENT-001 : TaskCreated · TaskStatusChanged · SubtaskCompleted    │
│  EVENT-002 : MemberInvited · MemberJoined                         │
│  EVENT-003 : TaskBlocked · TaskUnblocked                          │
│  Nouveaux  : CommentCreated · CommentMentioned · TaskAssigned      │
│                                                                    │
│  [EVENT-006-b] signal possible ici : volume Activity Feed élevé   │
│  → writes ActivityFeed migrent en async si contention             │
└───────────────────────────────────────────────────────────────────┘
```

| ID | Feature | Contenu |
|----|---------|---------|
| ACT001 | Activity Feed | domain events → timeline |

```
┌─ [EVENT-005] feature — avec NOT01 ────────────────────────────────┐
│  Consumer SSE sur events existants (CommentMentioned, TaskAssigned)│
│  Pas de nouvel event — branche sur EVENT-004                      │
│                                                                    │
│  [EVENT-006-c] signal possible ici : SSE push timeout             │
│  → push Mercure migre en async si saturation                      │
└───────────────────────────────────────────────────────────────────┘
```

| ID | Feature | Contenu |
|----|---------|---------|
| NOT01 | Notifications | Mercure SSE |

---

## Phase 3 — Infra

| ID | Feature | Contenu |
|----|---------|---------|
| CQRS001 | Command/Query buses | Messenger |
| ASYNC001 | Async Messenger | infrastructure workers → prérequis pour tout EVENT-006-x |

```
┌─ [EVENT-006] refactoring — incrémental sur signal ────────────────┐
│  ASYNC001 configure l'infra une fois (transports + workers)        │
│  Chaque event migre indépendamment quand il cause un problème :    │
│                                                                    │
│  EVENT-006-a  MemberInvited      email I/O bloquant               │
│  EVENT-006-b  ActivityFeed writes volume élevé sous charge         │
│  EVENT-006-c  SSE push           Mercure saturé                   │
│  EVENT-006-d  exports            si feature export existe          │
└───────────────────────────────────────────────────────────────────┘
```

| ID | Feature | Contenu |
|----|---------|---------|
| ARCH001 | Tenant Isolation | Doctrine SQLFilter |

```
┌─ [ARCH002] refactoring — sur signal concret ──────────────────────┐
│  Read Model DBAL quand une query Doctrine devient inconfortable    │
│  Signal probable : Activity Feed listings ou listings multi-org    │
└───────────────────────────────────────────────────────────────────┘
```

| ID | Feature | Contenu |
|----|---------|---------|
| TM01-PH3 | IAM Advanced | Teams, rôles fins |

---

## Catalogue des Domain Events

| ID | Type | Déclencheur | Consumer initial |
|----|------|-------------|-----------------|
| EVENT-001 | refactoring | après Symfony Workflow | listener interne BC Task |
| EVENT-002 | feature | avec Invitation | Mailer synchrone |
| EVENT-003 | feature | avec TaskDependency | notifier assignee sync |
| EVENT-004 | refactoring | avant Activity Feed | ActivityFeedProjection |
| EVENT-005 | feature | avec Notifications | Mercure SSE |
| EVENT-006-a/b/c/d | refactoring | sur signal contention | workers async Messenger |
| ARCH002 | refactoring | sur signal query complexe | DBAL read model |
