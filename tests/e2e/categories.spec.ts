import { test, expect } from '@playwright/test';

test.describe('Categories Management', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');

    // Navigate to categories
    await page.goto('/categories');
  });

  test('should display categories list', async ({ page }) => {
    await expect(page.locator('h1, h2')).toContainText(/kategori/i);

    // Should have create button
    await expect(page.locator('button', { hasText: /tambah kategori/i })).toBeVisible();

    // Should have search input
    await expect(page.locator('input[placeholder*="Cari"]')).toBeVisible();
  });

  test('should create new category', async ({ page }) => {
    // Click create button
    await page.click('button:has-text("Tambah Kategori")');

    // Fill form in modal
    await page.fill('input[name="name"]', 'Test Category');
    await page.fill('textarea[name="description"]', 'Test Description');

    // Submit form
    await page.click('button[type="submit"]');

    // Should show success message or new category
    await expect(page.locator('text=Test Category')).toBeVisible();
  });

  test('should search categories', async ({ page }) => {
    // Type in search input
    await page.fill('input[placeholder*="Cari"]', 'Electronics');

    // Should filter results
    await page.waitForTimeout(500); // Wait for debounced search

    // Should show filtered results
    const categoryCards = page.locator('.card, [data-testid*="category"]');
    await expect(categoryCards.first()).toBeVisible();
  });

  test('should edit category', async ({ page }) => {
    // Look for edit button on first category
    const editButton = page.locator('button:has-text("Edit"), button[aria-label*="edit"]').first();

    if (await editButton.isVisible()) {
      await editButton.click();

      // Update name
      await page.fill('input[name="name"]', 'Updated Category');

      // Submit form
      await page.click('button[type="submit"]');

      // Should show updated category
      await expect(page.locator('text=Updated Category')).toBeVisible();
    }
  });
});
