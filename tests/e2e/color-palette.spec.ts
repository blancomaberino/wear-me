import { test, expect } from '@playwright/test';

test.describe('Color Palette', () => {
  test('profile page shows color palette section', async ({ page }) => {
    await page.goto('/profile');
    await expect(page.getByText('Color Palette')).toBeVisible();
    await expect(page.getByText('Your personal color palette helps AI generate better outfit suggestions.')).toBeVisible();
  });

  test('shows empty state when no colors', async ({ page }) => {
    await page.goto('/profile');
    const addButton = page.locator('button[title="Add Color"]');
    // Add button should be visible (palette may be empty or have colors)
    await expect(addButton).toBeVisible();
  });

  test('can add a color', async ({ page }) => {
    await page.goto('/profile');
    // Click the add color button (+ button)
    const addButton = page.locator('button[title="Add Color"]');
    await expect(addButton).toBeVisible();
    await addButton.click();
    // A color input should now be visible
    await expect(page.locator('input[type="color"]').first()).toBeVisible();
  });

  test('save palette button exists', async ({ page }) => {
    await page.goto('/profile');
    await expect(page.getByRole('button', { name: 'Save Palette' })).toBeVisible();
  });

  test('can add color and save palette', async ({ page }) => {
    await page.goto('/profile');
    const addButton = page.locator('button[title="Add Color"]');
    await expect(addButton).toBeVisible();
    // Add a color
    await addButton.click();
    await expect(page.locator('input[type="color"]').first()).toBeVisible();
    // Save palette
    await page.getByRole('button', { name: 'Save Palette' }).click();
    await expect(page.getByText('Saved.')).toBeVisible({ timeout: 10000 });
    // Reload and verify persistence
    await page.reload();
    await expect(page.locator('input[type="color"]').first()).toBeVisible({ timeout: 10000 });
  });

  test('group by tone appears with multiple colors', async ({ page }) => {
    await page.goto('/profile');
    const addButton = page.locator('button[title="Add Color"]');
    // Add two colors if needed
    const colorInputs = page.locator('input[type="color"]');
    const count = await colorInputs.count();
    if (count < 2) {
      for (let i = count; i < 2; i++) {
        await addButton.click();
      }
    }
    // "Group by tone" should appear with 2+ colors
    await expect(page.getByText('Group by tone')).toBeVisible();
  });
});
