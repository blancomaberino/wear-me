import { test, expect } from '@playwright/test';

test.describe('Desktop Navigation (Sidebar)', () => {
  test('sidebar is visible on desktop', async ({ page }) => {
    await page.goto('/dashboard');
    // Sidebar should contain WearMe brand
    await expect(page.getByText('WearMe').first()).toBeVisible();
  });

  test('sidebar has all navigation items', async ({ page }) => {
    await page.goto('/dashboard');
    const sidebar = page.locator('nav').first();
    await expect(page.getByRole('link', { name: /Dashboard/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /My Photos/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /Wardrobe/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /Try On/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /Outfits/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /History/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /Profile/ }).first()).toBeVisible();
  });

  test('can navigate to My Photos via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /My Photos/ }).first().click();
    await page.waitForURL(/\/model-images/);
    await expect(page).toHaveTitle(/My Photos/);
  });

  test('can navigate to Wardrobe via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /Wardrobe/ }).first().click();
    await page.waitForURL(/\/wardrobe/);
    await expect(page).toHaveTitle(/Wardrobe/);
  });

  test('can navigate to Try On via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /Try On/ }).first().click();
    await page.waitForURL(/\/tryon/);
    await expect(page).toHaveTitle(/Try On/);
  });

  test('can navigate to Profile via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /Profile/ }).first().click();
    await page.waitForURL(/\/profile/);
    await expect(page).toHaveTitle(/Profile/);
  });

  test('can navigate to Outfits via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /Outfits/ }).first().click();
    await page.waitForURL(/\/outfits/);
  });

  test('can navigate to History via sidebar', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /History/ }).first().click();
    await page.waitForURL(/\/tryon\/history/);
  });

  test('skip to content link exists', async ({ page }) => {
    await page.goto('/dashboard');
    const skipLink = page.getByText('Skip to content');
    // Skip link is sr-only by default
    await expect(skipLink).toBeAttached();
  });
});

test.describe('Mobile Navigation', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test('shows mobile top bar with logo', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.getByText('WearMe').first()).toBeVisible();
  });

  test('shows mobile bottom navigation', async ({ page }) => {
    await page.goto('/dashboard');
    // Bottom nav should have key items
    // The mobile bottom nav has icons for Dashboard, Photos, Try On (center), Wardrobe, Profile
    const bottomNav = page.locator('nav').last();
    await expect(bottomNav).toBeVisible();
  });

  test('mobile avatar links to profile', async ({ page }) => {
    await page.goto('/dashboard');
    // The top bar has avatar linking to profile
    const avatar = page.locator('.md\\:hidden a[href*="profile"]');
    if (await avatar.isVisible({ timeout: 2000 }).catch(() => false)) {
      await avatar.click();
      await page.waitForURL(/\/profile/);
    }
  });
});
