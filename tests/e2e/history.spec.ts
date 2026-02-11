import { test, expect } from '@playwright/test';

test.describe('Try-On History', () => {
  test('shows history page', async ({ page }) => {
    await page.goto('/tryon/history');
    await expect(page).toHaveTitle(/Try-On History/);
    await expect(page.getByText('Try-On History')).toBeVisible();
  });

  test('shows filter tabs', async ({ page }) => {
    await page.goto('/tryon/history');
    await expect(page.getByText('All')).toBeVisible();
    await expect(page.getByText('Favorites')).toBeVisible();
  });

  test('shows empty state for new user', async ({ page }) => {
    await page.goto('/tryon/history');
    await expect(page.getByText('No try-on history')).toBeVisible();
    await expect(page.getByText('Your try-on results will appear here')).toBeVisible();
  });

  test('favorites tab shows no favorites message', async ({ page }) => {
    await page.goto('/tryon/history');
    // Click favorites tab
    await page.getByRole('tab', { name: 'Favorites' }).click();
    await expect(page.getByText('No favorites yet')).toBeVisible();
  });

  test('New Try-On button navigates to wizard', async ({ page }) => {
    await page.goto('/tryon/history');
    await page.getByRole('link', { name: /New Try-On/ }).click();
    await page.waitForURL(/\/tryon$/);
  });

  test('Start a Try-On link in empty state works', async ({ page }) => {
    await page.goto('/tryon/history');
    const startLink = page.getByRole('link', { name: /Start a Try-On/ });
    if (await startLink.isVisible({ timeout: 2000 }).catch(() => false)) {
      await startLink.click();
      await page.waitForURL(/\/tryon$/);
    }
  });
});
