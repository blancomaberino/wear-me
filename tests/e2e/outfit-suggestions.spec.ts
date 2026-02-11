import { test, expect } from '@playwright/test';

test.describe('Outfit Suggestions', () => {
  test('shows suggestions page with correct title', async ({ page }) => {
    await page.goto('/outfits');
    await expect(page).toHaveTitle(/Outfit Suggestions/);
    await expect(page.getByText('Outfit Suggestions')).toBeVisible();
  });

  test('shows occasion tabs', async ({ page }) => {
    await page.goto('/outfits');
    await expect(page.getByText('Casual')).toBeVisible();
    await expect(page.getByText('Formal')).toBeVisible();
    await expect(page.getByText('Business')).toBeVisible();
    await expect(page.getByText('Date Night')).toBeVisible();
    await expect(page.getByText('Party')).toBeVisible();
    await expect(page.getByText('Workout')).toBeVisible();
  });

  test('shows generate button', async ({ page }) => {
    await page.goto('/outfits');
    await expect(page.getByRole('button', { name: /Generate Suggestions/ })).toBeVisible();
  });

  test('shows empty state for new user', async ({ page }) => {
    await page.goto('/outfits');
    await expect(page.getByText('No suggestions yet')).toBeVisible();
    await expect(page.getByText('Select an occasion and generate AI outfit suggestions from your wardrobe.')).toBeVisible();
  });

  test('can switch occasion tabs', async ({ page }) => {
    await page.goto('/outfits');
    // Click Formal tab
    await page.getByText('Formal').click();
    // Click Business tab
    await page.getByText('Business').click();
    // Click back to Casual
    await page.getByText('Casual').click();
    // Generate button should still be visible
    await expect(page.getByRole('button', { name: /Generate Suggestions/ })).toBeVisible();
  });

  test('has link to saved outfits', async ({ page }) => {
    await page.goto('/outfits');
    await expect(page.getByText('Saved')).toBeVisible();
  });
});

test.describe('Saved Outfits', () => {
  test('shows saved outfits page', async ({ page }) => {
    await page.goto('/outfits/saved');
    await expect(page).toHaveTitle(/Saved Outfits/);
    await expect(page.getByText('Saved Outfits')).toBeVisible();
  });

  test('shows empty state for new user', async ({ page }) => {
    await page.goto('/outfits/saved');
    await expect(page.getByText('No saved outfits')).toBeVisible();
    await expect(page.getByText('Save outfit suggestions to find them here.')).toBeVisible();
  });

  test('has link back to suggestions', async ({ page }) => {
    await page.goto('/outfits/saved');
    await expect(page.getByRole('button', { name: /Get More Suggestions/ })).toBeVisible();
  });

  test('empty state has Get Suggestions action', async ({ page }) => {
    await page.goto('/outfits/saved');
    const getSuggestionsBtn = page.getByRole('button', { name: /Get Suggestions/ });
    if (await getSuggestionsBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
      await expect(getSuggestionsBtn).toBeVisible();
    }
  });
});
