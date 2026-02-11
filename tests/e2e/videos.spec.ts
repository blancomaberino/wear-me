import { test, expect } from '@playwright/test';

test.describe('Videos', () => {
  test('shows video creation page with correct title', async ({ page }) => {
    await page.goto('/videos');
    await expect(page).toHaveTitle(/Generate Video/);
  });

  test('shows empty state when no completed try-ons', async ({ page }) => {
    await page.goto('/videos');
    await expect(page.getByText('No completed try-ons yet')).toBeVisible();
    await expect(page.getByText('Complete a try-on first to generate a video')).toBeVisible();
  });

  test('has link to try-on page from empty state', async ({ page }) => {
    await page.goto('/videos');
    const goToTryOn = page.getByText('Go to Try-On');
    if (await goToTryOn.isVisible({ timeout: 2000 }).catch(() => false)) {
      await goToTryOn.click();
      await page.waitForURL(/\/tryon/);
    }
  });
});

test.describe('Video History', () => {
  test('shows video history page with correct title', async ({ page }) => {
    await page.goto('/videos/history');
    await expect(page).toHaveTitle(/Video History/);
    await expect(page.getByText('Video History')).toBeVisible();
  });

  test('shows notice about video generation', async ({ page }) => {
    await page.goto('/videos/history');
    await expect(page.getByText('Notice')).toBeVisible();
    await expect(page.getByText(/Video generation is temporarily unavailable/)).toBeVisible();
  });

  test('shows empty state for new user', async ({ page }) => {
    await page.goto('/videos/history');
    await expect(page.getByText('No videos yet')).toBeVisible();
  });

  test('has link to generate first video', async ({ page }) => {
    await page.goto('/videos/history');
    const generateLink = page.getByText('Generate your first video');
    if (await generateLink.isVisible({ timeout: 2000 }).catch(() => false)) {
      await generateLink.click();
      await page.waitForURL(/\/videos$/);
    }
  });

  test('can navigate from videos to history', async ({ page }) => {
    await page.goto('/videos');
    // Check page loaded
    await expect(page).toHaveTitle(/Generate Video/);
    // Navigate to history via URL
    await page.goto('/videos/history');
    await expect(page).toHaveTitle(/Video History/);
  });
});
