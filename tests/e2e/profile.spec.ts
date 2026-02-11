import { test, expect } from '@playwright/test';

test.describe('Profile', () => {
  test('loads profile page with all sections', async ({ page }) => {
    await page.goto('/profile');
    await expect(page).toHaveTitle(/Profile/);
    // Check all sections exist
    await expect(page.getByText('Profile Information')).toBeVisible();
    await expect(page.getByText('Body Measurements')).toBeVisible();
    await expect(page.getByText('Update Password')).toBeVisible();
    await expect(page.getByText('Delete Account')).toBeVisible();
  });

  test('can update profile information', async ({ page }) => {
    await page.goto('/profile');
    const nameInput = page.locator('form').filter({ hasText: 'Profile Information' }).getByLabel('Name');
    await nameInput.fill('Updated Test User');
    await page.locator('form').filter({ hasText: 'Profile Information' }).getByRole('button', { name: 'Save' }).click();
    // Should show success indicator
    await expect(page.getByText('Saved.')).toBeVisible({ timeout: 10000 });
  });

  test('body measurements section has all fields', async ({ page }) => {
    await page.goto('/profile');
    // Check measurement fields exist
    await expect(page.getByLabel('Height')).toBeVisible();
    await expect(page.getByLabel('Weight')).toBeVisible();
    await expect(page.getByLabel('Chest')).toBeVisible();
    await expect(page.getByLabel('Waist')).toBeVisible();
    await expect(page.getByLabel('Hips')).toBeVisible();
    await expect(page.getByLabel('Inseam')).toBeVisible();
    await expect(page.getByLabel('Shoe Size (EU)')).toBeVisible();
  });

  test('can toggle metric/imperial units', async ({ page }) => {
    await page.goto('/profile');
    // Default is metric - check for "cm" suffix
    await expect(page.getByText('Metric')).toBeVisible();
    await expect(page.getByText('Imperial')).toBeVisible();
  });

  test('can save body measurements', async ({ page }) => {
    await page.goto('/profile');
    await page.getByLabel('Height').fill('175');
    await page.getByLabel('Weight').fill('70');
    await page.getByLabel('Chest').fill('96');
    await page.getByLabel('Waist').fill('82');
    await page.getByLabel('Hips').fill('96');
    await page.getByLabel('Inseam').fill('80');
    await page.getByLabel('Shoe Size (EU)').fill('42');
    await page.getByRole('button', { name: 'Save Measurements' }).click();
    await expect(page.getByText('Saved.')).toBeVisible({ timeout: 10000 });
  });

  test('shows "Why do we ask" expandable section', async ({ page }) => {
    await page.goto('/profile');
    const whyButton = page.getByText('Why do we ask for measurements?');
    await expect(whyButton).toBeVisible();
    await whyButton.click();
    await expect(page.getByText('Your measurements help our AI generate more accurate')).toBeVisible();
  });

  test('can update password', async ({ page }) => {
    await page.goto('/profile');
    const passwordSection = page.locator('form').filter({ hasText: 'Update Password' });
    await passwordSection.getByLabel('Current Password').fill('password123');
    await passwordSection.getByLabel('New Password').fill('newpassword456');
    await passwordSection.getByLabel('Confirm Password').fill('newpassword456');
    await passwordSection.getByRole('button', { name: 'Save' }).click();
    // Should show success
    await expect(page.getByText('Saved.')).toBeVisible({ timeout: 10000 });
    // Revert password for other tests
    await passwordSection.getByLabel('Current Password').fill('newpassword456');
    await passwordSection.getByLabel('New Password').fill('password123');
    await passwordSection.getByLabel('Confirm Password').fill('password123');
    await passwordSection.getByRole('button', { name: 'Save' }).click();
    await expect(page.getByText('Saved.')).toBeVisible({ timeout: 10000 });
  });
});
