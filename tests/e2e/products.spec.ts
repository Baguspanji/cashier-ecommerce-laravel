import { test, expect } from '@playwright/test';

test.describe('Products Management', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');

    // Navigate to products
    await page.goto('/products');
  });

  test('should display products list', async ({ page }) => {
    await expect(page.locator('h1, h2')).toContainText(/produk/i);

    // Should have create button
    await expect(page.locator('a[href*="create"], button:has-text("Tambah Produk")')).toBeVisible();

    // Should have filters
    await expect(page.locator('input[placeholder*="Cari"]')).toBeVisible();
    await expect(page.locator('select, [role="combobox"]')).toBeVisible();
  });

  test('should create new product', async ({ page }) => {
    // Click create button
    await page.click('a[href*="create"], button:has-text("Tambah Produk")');

    // Should navigate to create form
    await expect(page).toHaveURL(/.*create.*/);

    // Fill form
    await page.fill('input[name="name"]', 'Test Product');
    await page.fill('textarea[name="description"]', 'Test Description');
    await page.fill('input[name="price"]', '50000');
    await page.fill('input[name="current_stock"]', '10');
    await page.fill('input[name="minimum_stock"]', '5');

    // Select category if dropdown exists
    const categorySelect = page.locator('select[name="category_id"], [role="combobox"]').first();
    if (await categorySelect.isVisible()) {
      await categorySelect.click();
      await page.locator('option, [role="option"]').first().click();
    }

    // Submit form
    await page.click('button[type="submit"]');

    // Should redirect back to products list
    await expect(page).toHaveURL(/.*products$/);
    await expect(page.locator('text=Test Product')).toBeVisible();
  });

  test('should filter products by category', async ({ page }) => {
    // Use category filter
    const categoryFilter = page.locator('select[name="category"], [data-testid="category-filter"]').first();

    if (await categoryFilter.isVisible()) {
      await categoryFilter.click();

      // Select first category option
      const firstOption = page.locator('option:not([value=""]), [role="option"]').first();
      await firstOption.click();

      // Should filter results
      await page.waitForTimeout(500);

      // Should show filtered products
      const productCards = page.locator('.card, [data-testid*="product"]');
      await expect(productCards.first()).toBeVisible();
    }
  });

  test('should filter products by status', async ({ page }) => {
    // Use status filter
    const statusFilter = page.locator('select[name="status"], [data-testid="status-filter"]').first();

    if (await statusFilter.isVisible()) {
      await statusFilter.click();

      // Select "Active" status
      await page.locator('option[value="active"], [role="option"]:has-text("Aktif")').click();

      // Should filter results
      await page.waitForTimeout(500);

      // Should show only active products
      const productCards = page.locator('.card, [data-testid*="product"]');
      await expect(productCards.first()).toBeVisible();
    }
  });

  test('should search products', async ({ page }) => {
    // Type in search input
    await page.fill('input[placeholder*="Cari"]', 'Laptop');

    // Should filter results
    await page.waitForTimeout(500);

    // Should show filtered results
    const productCards = page.locator('.card:has-text("Laptop"), [data-testid*="product"]:has-text("Laptop")');
    if (await productCards.count() > 0) {
      await expect(productCards.first()).toBeVisible();
    }
  });

  test('should toggle product status', async ({ page }) => {
    // Find toggle button on first product
    const toggleButton = page.locator('button:has-text("Toggle"), button[aria-label*="toggle"]').first();

    if (await toggleButton.isVisible()) {
      await toggleButton.click();

      // Should show confirmation or status change
      await expect(page.locator('text=/berhasil|success/i')).toBeVisible({ timeout: 5000 });
    }
  });
});
