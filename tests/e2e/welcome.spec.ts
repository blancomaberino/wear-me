import { test, expect } from '@playwright/test';

// Don't use stored auth for welcome page tests
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Welcome Page', () => {
  test('shows landing page', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByText('WearMe')).toBeVisible();
  });

  test('has login and register links', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('link', { name: /Log in/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Sign up|Register|Get Started/i })).toBeVisible();
  });

  test('login link navigates to login page', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('link', { name: /Log in/i }).click();
    await page.waitForURL(/\/login/);
    await expect(page).toHaveTitle(/Log in/);
  });

  test('register/get started link navigates to register page', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('link', { name: /Sign up|Register|Get Started/i }).first().click();
    await page.waitForURL(/\/register/);
    await expect(page).toHaveTitle(/Register/);
  });
});
