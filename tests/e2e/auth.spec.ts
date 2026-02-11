import { test, expect } from '@playwright/test';

// These tests don't use stored auth - they test the auth flows themselves
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Authentication', () => {
  test('shows login page with correct elements', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle(/Log in/);
    await expect(page.getByText('Welcome back')).toBeVisible();
    await expect(page.getByLabel('Email')).toBeVisible();
    await expect(page.getByLabel('Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Log in' })).toBeVisible();
    await expect(page.getByText('Continue with Google')).toBeVisible();
    await expect(page.getByText("Don't have an account?")).toBeVisible();
  });

  test('shows register page with correct elements', async ({ page }) => {
    await page.goto('/register');
    await expect(page).toHaveTitle(/Register/);
    await expect(page.getByText('Create your account')).toBeVisible();
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByLabel('Email')).toBeVisible();
    await expect(page.getByLabel('Password', { exact: true })).toBeVisible();
    await expect(page.getByLabel('Confirm Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Create Account' })).toBeVisible();
  });

  test('shows validation errors on empty login', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('button', { name: 'Log in' }).click();
    // Wait for validation errors (Inertia server-side validation)
    await expect(page.getByText(/email.*required|The email field is required/i)).toBeVisible({ timeout: 10000 });
  });

  test('shows validation errors on invalid registration', async ({ page }) => {
    await page.goto('/register');
    await page.getByLabel('Name').fill('A');
    await page.getByLabel('Email').fill('invalid');
    await page.getByLabel('Password', { exact: true }).fill('short');
    await page.getByLabel('Confirm Password').fill('mismatch');
    await page.getByRole('button', { name: 'Create Account' }).click();
    // Wait for validation errors
    await expect(page.getByText(/email.*valid|The email field must be a valid/i)).toBeVisible({ timeout: 10000 });
  });

  test('can register a new user', async ({ page }) => {
    await page.goto('/register');
    const email = `e2e-register-${Date.now()}@example.com`;
    await page.getByLabel('Name').fill('E2E Register Test');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password', { exact: true }).fill('password123');
    await page.getByLabel('Confirm Password').fill('password123');
    await page.getByRole('button', { name: 'Create Account' }).click();
    await page.waitForURL('/dashboard');
    await expect(page).toHaveTitle(/Dashboard/);
  });

  test('can login with valid credentials', async ({ page }) => {
    // First register
    await page.goto('/register');
    const email = `e2e-login-${Date.now()}@example.com`;
    await page.getByLabel('Name').fill('E2E Login Test');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password', { exact: true }).fill('password123');
    await page.getByLabel('Confirm Password').fill('password123');
    await page.getByRole('button', { name: 'Create Account' }).click();
    await page.waitForURL('/dashboard');

    // Logout
    // The sidebar has a logout button in the user section at bottom
    // On desktop, click the sidebar logout
    await page.goto('/login');

    // Login with same credentials
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password').fill('password123');
    await page.getByRole('button', { name: 'Log in' }).click();
    await page.waitForURL('/dashboard');
    await expect(page).toHaveTitle(/Dashboard/);
  });

  test('redirects to login when accessing protected pages', async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForURL(/\/login/);
    await expect(page).toHaveTitle(/Log in/);
  });

  test('login link from register page works', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('link', { name: 'Log in' }).click();
    await page.waitForURL('/login');
    await expect(page).toHaveTitle(/Log in/);
  });

  test('register link from login page works', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('link', { name: 'Sign up' }).click();
    await page.waitForURL('/register');
    await expect(page).toHaveTitle(/Register/);
  });
});
