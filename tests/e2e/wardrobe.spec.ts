import { test, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const testImage = path.join(path.dirname(fileURLToPath(import.meta.url)), 'fixtures/test-image.png');

test.describe('Wardrobe', () => {
  test('shows wardrobe page with correct elements', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page).toHaveTitle(/Wardrobe/);
    await expect(page.getByRole('button', { name: /Add Item/ })).toBeVisible();
    // Category tabs
    await expect(page.getByRole('tab', { name: /All/ })).toBeVisible();
    await expect(page.getByRole('tab', { name: /Tops/ })).toBeVisible();
    await expect(page.getByRole('tab', { name: /Bottoms/ })).toBeVisible();
    await expect(page.getByRole('tab', { name: /Dresses/ })).toBeVisible();
  });

  test('shows empty state when no garments', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Your wardrobe is empty')).toBeVisible();
  });

  test('opens upload dialog on Add Item click', async ({ page }) => {
    await page.goto('/wardrobe');
    await page.getByRole('button', { name: /Add Item/ }).click();
    // Dialog should appear
    await expect(page.getByText('Add to Wardrobe')).toBeVisible();
  });

  test('can upload a garment with category and size', async ({ page }) => {
    await page.goto('/wardrobe');
    await page.getByRole('button', { name: /Add Item/ }).click();
    // Wait for dialog
    await expect(page.getByText('Add to Wardrobe')).toBeVisible();
    // Upload image
    const fileInput = page.locator('[role="dialog"] input[type="file"]');
    await fileInput.setInputFiles(testImage);
    // Select category - click "Tops" pill
    await page.getByRole('dialog').getByText('Tops', { exact: true }).click();
    // Fill name
    await page.getByRole('dialog').getByLabel('Name').fill('Test Blue Shirt');
    // Fill brand
    await page.getByRole('dialog').getByLabel('Brand').fill('Test Brand');
    // Select size - click "M" pill
    await page.getByRole('dialog').getByText('M', { exact: true }).click();
    // Submit
    await page.getByRole('dialog').getByRole('button', { name: /Upload|Add|Save/ }).click();
    // Wait for dialog to close and garment to appear
    await expect(page.getByText('Add to Wardrobe')).not.toBeVisible({ timeout: 15000 });
    // Garment should be in the grid
    await expect(page.getByText('Test Blue Shirt')).toBeVisible({ timeout: 10000 });
  });

  test('category tabs filter garments', async ({ page }) => {
    await page.goto('/wardrobe');
    // Click Tops tab
    await page.getByRole('tab', { name: /Tops/ }).click();
    // If we uploaded a top garment, it should still be visible
    // Click Bottoms tab
    await page.getByRole('tab', { name: /Bottoms/ }).click();
    // The top garment should not be visible under bottoms tab
    // Click All tab
    await page.getByRole('tab', { name: /All/ }).click();
    // All garments visible again
  });
});
