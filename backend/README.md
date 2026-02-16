# Symfony API OAuth2 DDD CQRS

🚀 **API Symfony 7.3** avec architecture **Domain-Driven Design (DDD)**, **Hexagonal Architecture**, **CQRS** et authentification **OAuth2**.

## 📋 Table des matières

- [🏗️ Architecture](#️-architecture)
- [⚡ Quick Start](#-quick-start)
- [🔐 Sécurité & OAuth2](#-sécurité--oauth2)
- [🛠️ Développement](#️-développement)
- [🧪 Tests](#-tests)
- [📊 Qualité du code](#-qualité-du-code)
- [🚀 Déploiement](#-déploiement)
- [🗺️ Roadmap](#️-roadmap)

## 🏗️ Architecture

### Structure DDD/Hexagonal

```
src/
├── Shared/          ✅  # Kernel partagé — Bus, Value Objects, Events
├── Security/        ✅  # OAuth2 — tokens, clés RSA, scopes
├── Auth/            ✅  # Authentification — Register, Login, Logout, Refresh
├── User/            ✅  # Identité — profil utilisateur, rôles
├── Organization/    ✅  # Multi-tenant — Org, Member, Invitation
└── Project/         ✅  # Projets Kanban — Project, BoardColumn, ProjectMember
```

Chaque Bounded Context suit la structure DDD/Hexagonale :

```
{BC}/
├── Domain/          # Entités, Aggregates, Events, Contracts (interfaces)
├── Application/     # Services applicatifs, Use Cases
└── Infrastructure/
    ├── ApiPlatform/ # Resources, Providers, Processors, Mapping YAML
    └── Doctrine/    # Repositories Doctrine
```

### Principes appliqués

- **DDD** : Bounded contexts, Aggregates, Domain Events
- **Hexagonal** : Isolation du domaine, Ports & Adapters  
- **CQRS** : Séparation Command/Query avec Symfony Messenger
- **Clean Architecture** : Dépendances vers le domaine uniquement
- **Single Responsibility** : Un bounded context = une responsabilité métier
- **Aggregate Root** : Credentials (Auth) + UserProfile (User) séparés
- **Event Sourcing** : Events pour communication inter-contextes (UserRegistered)
- **CQRS strict** : Commands pour écriture, Queries pour lecture, Events pour sync

## ⚡ Quick Start

### Prérequis

- PHP 8.3+
- Composer
- Docker (optionnel)
- PostgreSQL

### Installation

```bash
# Cloner le projet
git clone https://github.com/luckarts/Symfony-api-oauth2-asana.git

# Installation des dépendances
composer install

# Configuration environnement
cp .env.dist .env
# ✏️ Éditer .env avec vos paramètres

# Base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Génération des clés OAuth2
php bin/console league:oauth2-server:generate-keypair

# Serveur de développement
symfony serve
# ou
php -S localhost:8000 -t public/
```

### Docker (Alternative)

```bash
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:migrate
```

## 🔐 Sécurité & OAuth2

### Configuration OAuth2

Le projet utilise `league/oauth2-server-bundle` avec :

- **Grant Types** : Authorization Code, Client Credentials, Password
- **JWT Tokens** avec RSA256
- **Refresh Tokens** activés
- **Scopes** personnalisés

### Endpoints

| Endpoint | Méthode | Description | Scope |
|----------|---------|-------------|-------|
| `/oauth2/authorize` | GET | Authorization endpoint | - |
| `/oauth2/token` | POST | Token endpoint | - |
| `/oauth2/revoke` | POST | Révocation token | - |
| `/api/auth/register` | POST | Inscription utilisateur | - |
| `/api/auth/login` | POST | Connexion utilisateur | - |
| `/api/auth/logout` | POST | Déconnexion utilisateur | `auth:write` |
| `/api/auth/refresh` | POST | Renouvellement token | `auth:refresh` |


### Exemple d'utilisation

```bash
# 1. Inscription d'un utilisateur
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "secure-password",
    "username": "john_doe"
  }'

# 2. Obtenir un token (Password Grant)
curl -X POST http://localhost:8000/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&username=user@example.com&password=secure-password&client_id=YOUR_CLIENT_ID&scope=user:read user:write"

# 3. Utiliser le token
curl -X GET http://localhost:8000/api/user/profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"


## 🛠️ Développement

### Workflow Git sécurisé

Le projet utilise un workflow Git avec protection de `main` :

```bash
# Initialiser la sécurité Git
./setup-git-security.sh

# Créer une fonctionnalité
git feature ma-nouvelle-feature

# Développer, commit sur develop
git checkout develop
git add .
git commit -m "feat: ma nouvelle fonctionnalité"

# Merger vers main (production)
git to-prod

# Créer une PR
git pr
```

### Commandes de développement

```bash
# Vérifications qualité complètes
./check-all.sh

# Vérifications avec tests
./check-all.sh --with-tests

# Serveur de développement avec rechargement
symfony serve --watch

# Console Symfony
php bin/console

# Créer un client OAuth2
php bin/console league:oauth2-server:create-client
```

### Architecture Decision Records (ADRs)

#### ADR-001: User comme Bounded Context unique
**Décision** : Garder User comme contexte principal pour auth + profil  
**Raison** : User est l'Aggregate Root naturel, évite duplication  
**Impact** : Use cases organisés par domaine fonctionnel  

#### ADR-002: CQRS avec Symfony Messenger
**Décision** : Commands pour écriture, Queries pour lecture  
**Raison** : Séparation claire responsabilités, scalabilité  
**Impact** : Handlers dédiés, bus séparés  

## 🧪 Tests

### Structure des tests

```
tests/
└── E2E/                       # Tests end-to-end par Bounded Context
    ├── User/
    ├── Organization/
    └── Project/
```

### Commandes de test

```bash
# Tests E2E (requiert DB — CI uniquement)
make test-e2e

# Tous les tests
composer test

# Tests avec couverture
composer test-coverage
```

## 📊 Qualité du code

### Outils intégrés

- **ECS** : Style de code PSR-12 + Symfony
- **PHPStan** : Analyse statique niveau 6
- **Deptrac** : Validation architecture DDD
- **PHPUnit** : Tests avec couverture

### Métriques qualité

```bash
# Style de code
vendor/bin/ecs check

# Analyse statique
vendor/bin/phpstan analyse

# Architecture DDD
vendor/bin/deptrac analyse

# Métriques combinées
./check-all.sh
```

### Standards respectés

- ✅ PSR-1, PSR-12 (Coding Standards)
- ✅ PSR-4 (Autoloading)  
- ✅ PSR-7, PSR-15 (HTTP)
- ✅ Symfony Coding Standards
- ✅ DDD Architectural Patterns

## 🚀 Déploiement

### Environnements

- **dev** : Développement local
- **test** : Tests automatisés  
- **prod** : Production


### CI/CD (GitHub Actions)

```yaml
# .github/workflows/quality.yml
- name: Quality checks
  run: |
    composer install --no-dev --optimize-autoloader
    ./check-all.sh
    vendor/bin/phpunit
```

### Variables d'environnement production

```bash
APP_ENV=prod
APP_SECRET=your-production-secret
DATABASE_URL=postgresql://user:pass@db:5432/prod_db
OAUTH_PRIVATE_KEY=/path/to/private.pem
OAUTH_PUBLIC_KEY=/path/to/public.pem
```
## 🗺️ Roadmap

| Phase | Statut | Contenu |
|-------|--------|---------|
| POC | ✅ | User, OAuth2, Task/Comment basiques |
| MVP Perso | 🔄 | Tasks enrichies, Tags, Comments, Symfony Workflow |
| Pivot SaaS | ⬜ | Organizations multi-tenant, invitations |
| Phase 3 | ⬜ | Collaboration, Activity Feed, Notifications SSE |

Voir [`docs/roadmap.md`](docs/roadmap.md) pour le détail complet et [`docs/database-evolution.md`](docs/database-evolution.md) pour l'évolution du schéma DB.

---

## 📄 Licence

Ce projet est sous licence MIT. Voir [LICENSE](LICENSE) pour plus de détails.

## 🔗 Liens utiles

- [Symfony 7.3 Documentation](https://symfony.com/doc/7.3/)
- [DDD Reference](https://domainlanguage.com/ddd/)
- [OAuth2 Server Bundle](https://github.com/thephpleague/oauth2-server-bundle)
- [API Platform](https://api-platform.com/)
