import { test, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const testImage = path.join(path.dirname(fileURLToPath(import.meta.url)), 'fixtures/test-image.png');

test.describe.serial('Garment Detail Sheet', () => {
  // Test 1: Upload two garments for subsequent tests
  test('setup: upload two garments', async ({ page }) => {
    await page.goto('/wardrobe');

    // Upload garment A - "Test Shirt A"
    await page.getByRole('button', { name: /Add Item/ }).click();
    await expect(page.getByText('Add to Wardrobe')).toBeVisible();
    await page.locator('[role="dialog"] input[type="file"]').setInputFiles(testImage);
    await page.getByRole('dialog').getByText('Top', { exact: true }).click();
    await page.getByRole('dialog').getByLabel('Name').fill('Test Shirt A');
    await page.getByRole('dialog').getByRole('button', { name: /Upload|Add|Save/ }).click();
    await expect(page.getByText('Add to Wardrobe')).not.toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Test Shirt A')).toBeVisible({ timeout: 10000 });

    // Upload garment B - "Test Pants B"
    await page.getByRole('button', { name: /Add Item/ }).click();
    await expect(page.getByText('Add to Wardrobe')).toBeVisible();
    await page.locator('[role="dialog"] input[type="file"]').setInputFiles(testImage);
    await page.getByRole('dialog').getByText('Bottom', { exact: true }).click();
    await page.getByRole('dialog').getByLabel('Name').fill('Test Pants B');
    await page.getByRole('dialog').getByRole('button', { name: /Upload|Add|Save/ }).click();
    await expect(page.getByText('Add to Wardrobe')).not.toBeVisible({ timeout: 15000 });
    await expect(page.getByText('Test Pants B')).toBeVisible({ timeout: 10000 });
  });

  // Test 2: Opens detail sheet with correct name
  test('opens detail sheet on garment click', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Shirt A')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Shirt A').click();
    // Dialog opens with garment name as title
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByText('Test Shirt A')).toBeVisible();
  });

  // Test 3: Shows garment info (image, category badge)
  test('shows garment info in detail sheet', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Shirt A')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Shirt A').click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // Image visible
    await expect(dialog.locator('img')).toBeVisible();
    // Category badge
    await expect(dialog.getByText('upper')).toBeVisible();
  });

  // Test 4: Can edit and save name
  test('can edit and save name', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Shirt A')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Shirt A').click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // Change name
    await dialog.getByLabel('Name').fill('Test Shirt A Renamed');
    await dialog.getByRole('button', { name: /Save Changes/ }).click();
    // Dialog closes, new name visible
    await expect(dialog).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Test Shirt A Renamed')).toBeVisible({ timeout: 10000 });
  });

  // Test 5: Can edit measurements
  test('can edit measurements', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Shirt A Renamed')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Shirt A Renamed').click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // Fill measurements
    await dialog.getByLabel('Chest (cm)').fill('100');
    await dialog.getByLabel('Length (cm)').fill('72');
    await dialog.getByRole('button', { name: /Save Changes/ }).click();
    await expect(dialog).not.toBeVisible({ timeout: 10000 });
    // Reopen and verify values persisted
    await page.getByText('Test Shirt A Renamed').click();
    const dialog2 = page.getByRole('dialog');
    await expect(dialog2).toBeVisible();
    await expect(dialog2.getByLabel('Chest (cm)')).toHaveValue('100');
    await expect(dialog2.getByLabel('Length (cm)')).toHaveValue('72');
  });

  // Test 6: REGRESSION - Form resets when switching garments
  test('form resets when switching garments', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Shirt A Renamed')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Test Pants B')).toBeVisible({ timeout: 10000 });

    // Open garment A, set size to "XL", save
    await page.getByText('Test Shirt A Renamed').click();
    let dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await dialog.getByLabel('Size').fill('XL');
    await dialog.getByRole('button', { name: /Save Changes/ }).click();
    await expect(dialog).not.toBeVisible({ timeout: 10000 });

    // Open garment B - size should be empty, NOT "XL"
    await page.getByText('Test Pants B').click();
    dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByLabel('Size')).toHaveValue('');
    // Close B
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible({ timeout: 5000 });

    // Re-open garment A - size should still be "XL" (persisted)
    await page.getByText('Test Shirt A Renamed').click();
    dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByLabel('Size')).toHaveValue('XL');
  });

  // Test 7: Delete cancel - garment still exists
  test('delete cancel keeps garment', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Pants B')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Pants B').click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // Click delete, then cancel
    await dialog.getByRole('button', { name: 'Delete' }).click();
    await expect(dialog.getByRole('button', { name: 'Confirm Delete' })).toBeVisible();
    await dialog.getByRole('button', { name: 'Cancel' }).click();
    // Confirm delete button should be gone
    await expect(dialog.getByRole('button', { name: 'Confirm Delete' })).not.toBeVisible();
    // Delete button should be back
    await expect(dialog.getByRole('button', { name: 'Delete' })).toBeVisible();
  });

  // Test 8: Delete with confirmation removes garment
  test('delete with confirmation removes garment', async ({ page }) => {
    await page.goto('/wardrobe');
    await expect(page.getByText('Test Pants B')).toBeVisible({ timeout: 10000 });
    await page.getByText('Test Pants B').click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // Click delete, then confirm
    await dialog.getByRole('button', { name: 'Delete' }).click();
    await dialog.getByRole('button', { name: 'Confirm Delete' }).click();
    // Dialog closes, garment removed
    await expect(dialog).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Test Pants B')).not.toBeVisible({ timeout: 10000 });
  });
});
