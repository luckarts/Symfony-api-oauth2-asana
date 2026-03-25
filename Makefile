.PHONY: dev-up dev-down dev-logs test-e2e-signup test-e2e install-browsers

DOCKER_LOCAL = docker compose --env-file backend/.env.docker.local -f backend/docker-compose.local.yml

# ─── Backend Docker ───────────────────────────────────────
dev-up:
	$(DOCKER_LOCAL) up -d
	@echo "✅ Backend disponible sur http://localhost:8000"

dev-down:
	$(DOCKER_LOCAL) down

dev-logs:
	$(DOCKER_LOCAL) logs -f symfony

# ─── Frontend ────────────────────────────────────────────
install-browsers:
	cd frontend && pnpm exec playwright install --with-deps chromium

# ─── Tests E2E ───────────────────────────────────────────
# Prérequis : make dev-up + pnpm dev (terminal séparé)
test-e2e-signup:
	cd frontend && PLAYWRIGHT_START_SERVER=false pnpm test:e2e:smoke --grep @smoke

test-e2e:
	cd frontend && PLAYWRIGHT_START_SERVER=false pnpm test:e2e:smoke
