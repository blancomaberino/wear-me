import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test('displays greeting and page elements', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveTitle(/Dashboard/);
    // Time-based greeting: Good morning/afternoon/evening
    await expect(page.getByText(/Good (morning|afternoon|evening)/)).toBeVisible();
    await expect(page.getByText("Here's what's happening with your wardrobe")).toBeVisible();
  });

  test('shows stat cards', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.getByText('My Photos')).toBeVisible();
    await expect(page.getByText('Wardrobe Items')).toBeVisible();
    await expect(page.getByText('Saved Outfits')).toBeVisible();
  });

  test('shows quick action cards', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.getByText('Quick Actions')).toBeVisible();
    await expect(page.getByText('New Try-On')).toBeVisible();
    await expect(page.getByText('Upload Photo')).toBeVisible();
    await expect(page.getByText('Add Clothing')).toBeVisible();
    await expect(page.getByText('Get Suggestions')).toBeVisible();
  });

  test('shows measurements prompt for new user', async ({ page }) => {
    await page.goto('/dashboard');
    // New test user won't have measurements
    await expect(page.getByText('Complete your profile')).toBeVisible();
    await expect(page.getByText('Add body measurements for more accurate try-on results')).toBeVisible();
  });

  test('measurements prompt links to profile', async ({ page }) => {
    await page.goto('/dashboard');
    const profileLink = page.getByText('Complete your profile').locator('..');
    // Click the measurements prompt card (it's wrapped in a Link to profile.edit)
    await page.getByText('Complete your profile').click();
    await page.waitForURL(/\/profile/);
  });

  test('quick action "New Try-On" navigates correctly', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByText('New Try-On').click();
    await page.waitForURL(/\/tryon/);
  });

  test('quick action "Upload Photo" navigates correctly', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByText('Upload Photo').click();
    await page.waitForURL(/\/model-images/);
  });

  test('quick action "Add Clothing" navigates correctly', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByText('Add Clothing').click();
    await page.waitForURL(/\/wardrobe/);
  });

  test('shows empty state for recent try-ons', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.getByText('No try-ons yet')).toBeVisible();
    await expect(page.getByText('Create Your First Try-On')).toBeVisible();
  });
});
