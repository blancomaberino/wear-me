import { test as setup, expect } from '@playwright/test';

const authFile = 'tests/e2e/.auth/user.json';

setup('authenticate', async ({ page }) => {
  // Go to register page
  await page.goto('/register');

  // Fill registration form
  const email = `testuser-${Date.now()}@example.com`;
  await page.getByLabel('Name').fill('Test User');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password', { exact: true }).fill('password123');
  await page.getByLabel('Confirm Password').fill('password123');

  // Submit
  await page.getByRole('button', { name: 'Create Account' }).click();

  // Wait for dashboard
  await page.waitForURL('/dashboard');
  await expect(page).toHaveTitle(/Dashboard/);

  // Save auth state
  await page.context().storageState({ path: authFile });
});
