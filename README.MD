# Symfony API OAuth2 — Task Manager

API REST Symfony 7.3 · DDD / Hexagonal · CQRS · OAuth2 · API Platform 4.1 · PostgreSQL

## Objectif

Task manager inspiré d'Asana — progression POC → MVP perso → SaaS multi-tenant.

| Phase | Statut | Contenu |
|-------|--------|---------|
| POC | ✅ | User, OAuth2, Task/Comment basiques |
| MVP Perso | 🔄 | Projects, BoardColumns, Tasks, Tags, Comments, Symfony Workflow |
| Pivot SaaS | ⬜ | Organizations, multi-tenant, invitations |
| Phase 3 | ⬜ | Collaboration, Activity Feed, Notifications SSE |

## Structure du dépôt

```
.
├── backend/      # Application Symfony (source principale)
│   └── README.md # Documentation complète
└── .github/      # CI/CD (quality, tests, audit, deploy)
```

## Documentation

- [`backend/README.md`](backend/README.md) — Architecture, Quick Start, Endpoints, Tests
- [`backend/docs/roadmap.md`](backend/docs/roadmap.md) — Roadmap détaillée + Domain Events
- [`backend/docs/database-evolution.md`](backend/docs/database-evolution.md) — Schéma DB (MVP → SaaS → Phase 3)
