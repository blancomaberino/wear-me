import { test, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const testImage = path.join(path.dirname(fileURLToPath(import.meta.url)), 'fixtures/test-image.png');

test.describe('Model Images (My Photos)', () => {
  test('shows photos page with upload dropzone', async ({ page }) => {
    await page.goto('/model-images');
    await expect(page).toHaveTitle(/My Photos/);
    await expect(page.getByText('Upload a photo of yourself')).toBeVisible();
    await expect(page.getByText('JPG, PNG, or WebP up to 10MB')).toBeVisible();
  });

  test('shows empty state when no photos', async ({ page }) => {
    await page.goto('/model-images');
    await expect(page.getByText('No photos yet')).toBeVisible();
  });

  test('can upload a photo', async ({ page }) => {
    await page.goto('/model-images');
    // The dropzone has an input[type="file"] hidden inside it
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(testImage);
    // Wait for upload to complete and page to refresh
    await expect(page.getByText('Uploading...')).toBeVisible();
    // After upload, the photo should appear in the gallery
    await expect(page.getByText('No photos yet')).not.toBeVisible({ timeout: 15000 });
    // Should show "1 photos uploaded" in the description
    await expect(page.getByText(/\d+ photos? uploaded/)).toBeVisible();
  });

  test('uploaded photo shows primary badge', async ({ page }) => {
    await page.goto('/model-images');
    // First photo is automatically primary
    await expect(page.getByText('Primary')).toBeVisible({ timeout: 10000 });
  });

  test('can delete a photo via dialog', async ({ page }) => {
    await page.goto('/model-images');
    // First upload a photo if none exists
    const photoCount = await page.locator('img[loading="lazy"]').count();
    if (photoCount === 0) {
      const fileInput = page.locator('input[type="file"]');
      await fileInput.setInputFiles(testImage);
      await expect(page.locator('img[loading="lazy"]').first()).toBeVisible({ timeout: 15000 });
    }
    // Hover over the first image to reveal actions
    await page.locator('img[loading="lazy"]').first().hover();
    // Click delete button (Trash2 icon button with danger variant)
    const deleteBtn = page.locator('.group').first().getByRole('button').last();
    await deleteBtn.click({ force: true });
    // Confirm delete in dialog
    await expect(page.getByText('Delete Photo')).toBeVisible();
    await expect(page.getByText('Are you sure you want to delete this photo?')).toBeVisible();
    await page.getByRole('button', { name: 'Delete' }).last().click();
    // Should show empty state again or one less photo
    await page.waitForTimeout(1000);
  });
});
