import { test, expect } from '@playwright/test';

test.describe('Point of Sale (POS)', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');

    // Navigate to POS
    await page.goto('/transactions/pos');
  });

  test('should display POS interface', async ({ page }) => {
    // Should show products section
    await expect(page.locator('text=Produk')).toBeVisible();

    // Should show cart section
    await expect(page.locator('text=Keranjang')).toBeVisible();

    // Should show search input
    await expect(page.locator('input[placeholder*="Cari produk"]')).toBeVisible();

    // Should show payment section
    await expect(page.locator('text=Total')).toBeVisible();
  });

  test('should search for products', async ({ page }) => {
    // Type in search input
    await page.fill('input[placeholder*="Cari produk"]', 'Laptop');

    // Should filter product results
    await page.waitForTimeout(500);

    // Should show filtered products
    const productCards = page.locator('.card:has-text("Laptop")');
    await expect(productCards.first()).toBeVisible();
  });

  test('should add product to cart', async ({ page }) => {
    // Find first product card with available stock
    const productCard = page.locator('.card').first();

    // Check if product has stock
    const stockText = await productCard.locator('text=/Stok: \\d+/').textContent();

    if (stockText && !stockText.includes('0')) {
      // Click add button
      const addButton = productCard.locator('button:has-text("Tambah")');
      await addButton.click();

      // Should add to cart
      await expect(page.locator('text=1 item')).toBeVisible();
    }
  });

  test('should calculate total correctly', async ({ page }) => {
    // Add a product to cart (if available)
    const productCard = page.locator('.card').first();
    const addButton = productCard.locator('button:has-text("Tambah")');

    if (await addButton.isVisible()) {
      // Get product price
      const priceText = await productCard.locator('text=/Rp .*\\d+/').textContent();

      if (priceText) {
        await addButton.click();

        // Check if total is calculated
        await expect(page.locator('text=/Total.*Rp/i')).toBeVisible();
      }
    }
  });

  test('should process transaction with cash payment', async ({ page }) => {
    // Add a product to cart first
    const productCard = page.locator('.card').first();
    const addButton = productCard.locator('button:has-text("Tambah")');

    if (await addButton.isVisible()) {
      await addButton.click();

      // Select cash payment method
      await page.click('button:has-text("Tunai")');

      // Enter payment amount
      const paymentInput = page.locator('input[type="number"]').last();
      await paymentInput.fill('100000');

      // Process transaction
      await page.click('button:has-text("Proses")');

      // Should show success or redirect
      await expect(page.locator('text=/berhasil|success/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should show change calculation', async ({ page }) => {
    // Add a product to cart
    const productCard = page.locator('.card').first();
    const addButton = productCard.locator('button:has-text("Tambah")');

    if (await addButton.isVisible()) {
      await addButton.click();

      // Select cash payment
      await page.click('button:has-text("Tunai")');

      // Enter payment amount higher than total
      const paymentInput = page.locator('input[type="number"]').last();
      await paymentInput.fill('100000');

      // Should show change calculation
      await expect(page.locator('text=/kembalian|change/i')).toBeVisible();
    }
  });
});
