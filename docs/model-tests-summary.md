# Model Tests Summary

This document provides an overview of the comprehensive Pest test suite created for all models in the Laravel cashier e-commerce application.

## Tests Created

### 1. CategoryTest.php
- **6 tests** covering:
  - Category creation with valid data
  - Products relationship testing
  - Optional description field
  - Factory validation
  - Required name field validation
  - HasMany relationship type verification

### 2. ProductTest.php  
- **12 tests** covering:
  - Product creation with all required fields
  - Category relationship (BelongsTo)
  - Stock management methods (`isLowStock()`, `isOutOfStock()`)
  - TransactionItems relationship (HasMany)
  - StockMovements relationship (HasMany)
  - Factory validation
  - Data type casting (price to decimal, is_active to boolean)
  - Relationship type verification
  - Edge cases for stock levels

### 3. TransactionTest.php
- **10 tests** covering:
  - Transaction creation with valid data
  - User relationship (BelongsTo)
  - TransactionItems relationship (HasMany)
  - Unique transaction number generation
  - Sequential numbering for same day
  - Factory validation
  - Decimal casting for monetary amounts
  - Relationship type verification
  - Transaction number format validation
  - Incrementing counter logic

### 4. TransactionItemTest.php
- **8 tests** covering:
  - Transaction item creation
  - Transaction relationship (BelongsTo)
  - Product relationship (BelongsTo)
  - Factory validation
  - Decimal casting for prices
  - Relationship type verification
  - Historical product name storage
  - Quantity validation

### 5. StockMovementTest.php
- **11 tests** covering:
  - Stock movement creation
  - Product relationship (BelongsTo)
  - User relationship (BelongsTo)
  - Stock update logic for 'in' movements
  - Stock update logic for 'out' movements
  - Negative stock prevention
  - Factory validation
  - Relationship type verification
  - Adjustment type handling
  - Reference details storage
  - Authenticated user assignment

### 6. UserTest.php
- **11 tests** covering:
  - User creation with valid data
  - Transactions relationship (HasMany)
  - StockMovements relationship (HasMany)
  - Factory validation
  - Password hashing
  - Email uniqueness constraint
  - Relationship type verification
  - Hidden attributes protection
  - DateTime casting for email_verified_at
  - Trait usage verification

## Test Configuration

- **Framework**: Pest PHP
- **Database**: RefreshDatabase trait for clean state
- **Total Tests**: 58 tests with 149 assertions
- **Coverage**: All model relationships, methods, casting, validation, and factory patterns

## Key Testing Patterns

1. **Relationship Testing**: Verifies both data integrity and relationship type
2. **Factory Validation**: Ensures factories generate valid data
3. **Casting Verification**: Confirms proper data type conversion
4. **Business Logic**: Tests custom methods like stock checks and transaction numbering
5. **Edge Cases**: Covers boundary conditions and error scenarios
6. **Database Constraints**: Tests unique constraints and required fields

## Running Tests

```bash
# Run all model tests
php artisan test tests/Unit/Models/

# Run specific model test
php artisan test tests/Unit/Models/ProductTest.php
```

All tests pass successfully and provide comprehensive coverage of the application's model layer.
