.PHONY: dev-up dev-down dev-logs dev-wait test-e2e-signup test-e2e install-browsers

DOCKER_LOCAL = docker compose --env-file backend/.env.docker.local -f backend/docker-compose.local.yml

# ─── Backend Docker ───────────────────────────────────────
dev-up:
	$(DOCKER_LOCAL) up -d
	@echo "✅ Backend disponible sur http://localhost:8000"

dev-down:
	$(DOCKER_LOCAL) down

dev-logs:
	$(DOCKER_LOCAL) logs -f symfony

dev-wait:
	@echo "⏳ Attente du backend..."
	@until curl -s http://localhost:8000 > /dev/null 2>&1; do sleep 1; done
	@echo "✅ Backend prêt"

# ─── Frontend ────────────────────────────────────────────
install-browsers:
	cd frontend && pnpm exec playwright install --with-deps chromium

# ─── Tests E2E ───────────────────────────────────────────
# Prérequis : pnpm dev (terminal séparé)
test-e2e-signup: dev-up dev-wait
	cd frontend && PLAYWRIGHT_START_SERVER=false pnpm exec playwright test tests/e2e/smoke/auth-signup.spec.ts --project=smoke-tests

test-e2e:
	cd frontend && PLAYWRIGHT_START_SERVER=false pnpm test:e2e:smoke
