import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
  test('should login successfully', async ({ page }) => {
    await page.goto('/login');

    // Fill login form
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');

    // Submit form
    await page.click('button[type="submit"]');

    // Should redirect to dashboard
    await expect(page).toHaveURL('/dashboard');
    await expect(page.locator('h1')).toContainText('Dashboard');
  });

  test('should show validation errors on invalid login', async ({ page }) => {
    await page.goto('/login');

    // Fill invalid credentials
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');

    // Submit form
    await page.click('button[type="submit"]');

    // Should show error message
    await expect(page.locator('.error, [role="alert"]')).toBeVisible();
  });
});

test.describe('Navigation', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
  });

  test('should navigate to all main sections', async ({ page }) => {
    // Test navigation to Products
    await page.click('a[href*="products"]');
    await expect(page).toHaveURL(/.*products.*/);
    await expect(page.locator('h1, h2')).toContainText(/produk/i);

    // Test navigation to Categories
    await page.click('a[href*="categories"]');
    await expect(page).toHaveURL(/.*categories.*/);
    await expect(page.locator('h1, h2')).toContainText(/kategori/i);

    // Test navigation to POS
    await page.click('a[href*="pos"]');
    await expect(page).toHaveURL(/.*pos.*/);
    await expect(page.locator('h1, h2')).toContainText(/pos|point of sale/i);

    // Test navigation to Transactions
    await page.click('a[href*="transactions"]');
    await expect(page).toHaveURL(/.*transactions.*/);
    await expect(page.locator('h1, h2')).toContainText(/transaksi/i);

    // Test navigation to Stock
    await page.click('a[href*="stock"]');
    await expect(page).toHaveURL(/.*stock.*/);
    await expect(page.locator('h1, h2')).toContainText(/stok/i);
  });
});
