import { expect, test } from '@playwright/test'

test.describe('@smoke Signup', () => {
  test('affichage — 3 champs + bouton submit visible', async ({ page }) => {
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await expect(page.getByLabel('Nom complet')).toBeVisible()
    await expect(page.getByLabel('Email')).toBeVisible()
    await expect(page.getByLabel('Mot de passe')).toBeVisible()
    await expect(page.getByRole('button', { name: 'Créer mon compte' })).toBeVisible()
  })

  test('validation client — nom vide bloque le submit', async ({ page }) => {
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    await expect(page.getByText('Le nom est requis')).toBeVisible()
  })

  test('validation client — email invalide', async ({ page }) => {
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('Test User')
    await page.getByLabel('Email').fill('pas-un-email')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    await expect(page.getByText('Adresse email invalide')).toBeVisible()
  })

  test('validation client — password trop court', async ({ page }) => {
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('Test User')
    await page.getByLabel('Email').fill('test@example.com')
    await page.getByLabel('Mot de passe').fill('court')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    await expect(page.getByText('au moins 8 caractères')).toBeVisible()
  })

  test('happy path — signup réussi redirige vers /', async ({ page }) => {
    test.skip(!!process.env.CI, 'Backend Symfony non disponible en CI')

    const email = `e2e-signup-${Date.now()}@test.com`
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('E2E User')
    await page.getByLabel('Email').fill(email)
    await page.getByLabel('Mot de passe').fill('E2eStr0ng!Pass')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    await expect(page.getByRole('alert').filter({ hasText: 'Bienvenue !' })).toBeVisible()
    await expect(page).toHaveURL('/')
  })

  test('toast succès — "Bienvenue !" affiché après signup réussi', async ({ page }) => {
    await page.route('**/api/users', (route) =>
      route.fulfill({
        status: 201,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 'fake-uuid',
          email: 'e2e@test.com',
          firstName: 'E2E',
          lastName: 'User',
          roles: ['ROLE_USER'],
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString(),
        }),
      })
    )
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('E2E User')
    await page.getByLabel('Email').fill('e2e@test.com')
    await page.getByLabel('Mot de passe').fill('motdepasse123')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    await expect(page.getByRole('alert').filter({ hasText: 'Bienvenue !' })).toBeVisible()
  })

  test('toast erreur — 422 sans violations affiche "Email déjà utilisé"', async ({ page }) => {
    await page.route('**/api/users', (route) =>
      route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({ message: 'Unprocessable Entity' }),
      })
    )
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('E2E User')
    await page.getByLabel('Email').fill('taken@test.com')
    await page.getByLabel('Mot de passe').fill('motdepasse123')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    const alert = page.getByRole('alert').filter({ hasText: 'Erreur de connexion' })
    await expect(alert).toBeVisible()
    await expect(alert).toContainText('Email déjà utilisé')
  })

  test('toast erreur — 500 affiche "Erreur serveur"', async ({ page }) => {
    await page.route('**/api/users', (route) =>
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ message: 'Internal Server Error' }),
      })
    )
    await page.goto('/auth/signup')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Nom complet').fill('E2E User')
    await page.getByLabel('Email').fill('e2e@test.com')
    await page.getByLabel('Mot de passe').fill('motdepasse123')
    await page.getByRole('button', { name: 'Créer mon compte' }).click()
    const alert = page.getByRole('alert').filter({ hasText: 'Erreur de connexion' })
    await expect(alert).toBeVisible()
    await expect(alert).toContainText('Erreur serveur')
  })
})
