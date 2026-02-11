import { test, expect } from '@playwright/test';

test.describe('Try-On Wizard', () => {
  test('shows empty state when no photos or garments', async ({ page }) => {
    await page.goto('/tryon');
    await expect(page).toHaveTitle(/Try On/);
    // New user has no photos/garments, should show empty state
    await expect(page.getByText('Almost Ready!')).toBeVisible();
    await expect(page.getByText('You need at least one photo and one clothing item to start')).toBeVisible();
    await expect(page.getByRole('link', { name: /Upload Photo/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /Upload Clothing/ })).toBeVisible();
  });

  test('Upload Photo link navigates to model-images', async ({ page }) => {
    await page.goto('/tryon');
    // If empty state is shown
    const almostReady = page.getByText('Almost Ready!');
    if (await almostReady.isVisible()) {
      await page.getByRole('link', { name: /Upload Photo/ }).click();
      await page.waitForURL(/\/model-images/);
    }
  });

  test('Upload Clothing link navigates to wardrobe', async ({ page }) => {
    await page.goto('/tryon');
    const almostReady = page.getByText('Almost Ready!');
    if (await almostReady.isVisible()) {
      await page.getByRole('link', { name: /Upload Clothing/ }).click();
      await page.waitForURL(/\/wardrobe/);
    }
  });
});

test.describe('Try-On Wizard - Full Flow', () => {
  // These tests require photos and garments to exist
  // They should run after model-images and wardrobe tests have seeded data

  test.beforeEach(async ({ page }) => {
    await page.goto('/tryon');
    // Skip tests if wizard shows empty state
    const almostReady = page.getByText('Almost Ready!');
    if (await almostReady.isVisible({ timeout: 3000 }).catch(() => false)) {
      test.skip(true, 'No photos or garments uploaded yet');
    }
  });

  test('shows wizard with step indicators', async ({ page }) => {
    // Stepper should show all 4 steps
    await expect(page.getByText('Select Photo')).toBeVisible();
    await expect(page.getByText('Select Garments')).toBeVisible();
    await expect(page.getByText('Style')).toBeVisible();
    await expect(page.getByText('Review')).toBeVisible();
  });

  test('step 1: displays photo selection grid', async ({ page }) => {
    // Should show the photo grid on step 1
    await expect(page.getByText('Select Photo')).toBeVisible();
    // Next button should be enabled (primary photo auto-selected)
    await expect(page.getByRole('button', { name: 'Next' })).toBeEnabled();
  });

  test('step 1 to step 2: can navigate forward', async ({ page }) => {
    await page.getByRole('button', { name: 'Next' }).click();
    // Now on step 2 - garment selection
    // Should show category tabs
    await expect(page.getByText('Tops')).toBeVisible();
    await expect(page.getByText('Bottoms')).toBeVisible();
    await expect(page.getByText('Dresses')).toBeVisible();
  });

  test('step 2: Next disabled until garment selected', async ({ page }) => {
    await page.getByRole('button', { name: 'Next' }).click();
    // On step 2, Next should be disabled until a garment is selected
    await expect(page.getByRole('button', { name: 'Next' })).toBeDisabled();
    // Back button should be visible
    await expect(page.getByRole('button', { name: 'Back' })).toBeVisible();
  });

  test('can navigate back from step 2 to step 1', async ({ page }) => {
    await page.getByRole('button', { name: 'Next' }).click();
    await page.getByRole('button', { name: 'Back' }).click();
    // Should be back on step 1
    await expect(page.getByRole('button', { name: 'Next' })).toBeEnabled();
  });

  test('step 3: style preferences are optional', async ({ page }) => {
    // Navigate to step 3
    await page.getByRole('button', { name: 'Next' }).click();
    // Select first garment in step 2 (click first card in the grid)
    const garmentCard = page.locator('[class*="rounded-card"]').first();
    if (await garmentCard.isVisible({ timeout: 2000 }).catch(() => false)) {
      await garmentCard.click();
      await page.getByRole('button', { name: 'Next' }).click();
      // Step 3: style preferences
      // Style chips should be visible
      await expect(page.getByText('Casual')).toBeVisible();
      // Next button should be enabled (style is optional)
      await expect(page.getByRole('button', { name: 'Next' })).toBeEnabled();
    }
  });

  test('history link is accessible from wizard page', async ({ page }) => {
    await expect(page.getByText('History')).toBeVisible();
    await page.getByText('History').click();
    await page.waitForURL(/\/tryon\/history/);
  });
});
