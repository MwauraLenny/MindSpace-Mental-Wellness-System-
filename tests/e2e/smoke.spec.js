import { expect, test } from '@playwright/test';

test.describe('public pages', () => {
  test('home page renders', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/MindSpace|Laravel/i);
  });

  test('login page renders form', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/password/i)).toBeVisible();
  });

  test('register page renders form', async ({ page }) => {
    await page.goto('/register');
    await expect(page.getByLabel(/name/i)).toBeVisible();
    await expect(page.getByLabel(/email/i)).toBeVisible();
  });
});

test.describe('availability endpoints', () => {
  test('health endpoint responds with ok', async ({ request }) => {
    const response = await request.get('/health');
    expect(response.ok()).toBeTruthy();

    const payload = await response.json();
    expect(payload.status).toBe('ok');
  });

  test('readiness endpoint responds with expected shape', async ({ request }) => {
    const response = await request.get('/ready');
    expect([200, 503]).toContain(response.status());

    const payload = await response.json();
    expect(['ready', 'degraded']).toContain(payload.status);
    expect(payload.checks).toBeTruthy();
  });
});

test.describe('responsive navigation', () => {
  test('login form remains usable on mobile and tablet', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/login');
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/password/i)).toBeVisible();

    await page.setViewportSize({ width: 834, height: 1194 });
    await page.goto('/login');
    await expect(page.getByLabel(/email/i)).toBeVisible();
    await expect(page.getByLabel(/password/i)).toBeVisible();
  });
});
