# Transaction API Documentation

This document provides comprehensive examples for using the Transaction API endpoints for POS and sales management.

## Authentication

All Transaction API endpoints require authentication using Bearer token:

```http
Authorization: Bearer {your-token-from-login}
```

## Endpoints Overview

### Transaction Management

#### List Transactions
```http
GET /api/transactions
```

**Query Parameters:**
- `start_date` (optional) - Filter from date (YYYY-MM-DD)
- `end_date` (optional) - Filter to date (YYYY-MM-DD)
- `period` (optional) - Predefined period: `today`, `yesterday`, `this_week`, `last_week`, `this_month`, `last_month`, `this_year`, `custom`
- `payment_method` (optional) - Filter by payment method
- `user_id` (optional) - Filter by user who created transaction
- `status` (optional) - Filter by status: `completed`, `cancelled`
- `search` (optional) - Search by transaction number
- `per_page` (optional) - Number of results per page (default: 15, max: 100)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "transaction_number": "TRX202509280001",
            "user_id": 1,
            "customer_name": null,
            "total_amount": 35000,
            "payment_method": "cash",
            "payment_amount": 40000,
            "change_amount": 5000,
            "income": 11000,
            "status": "completed",
            "notes": "Test transaction",
            "offline_id": null,
            "sync_status": null,
            "last_sync_at": null,
            "created_at": "2025-09-28T10:00:00.000000Z",
            "updated_at": "2025-09-28T10:00:00.000000Z",
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "items": [
                {
                    "id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "price": 10000,
                    "subtotal": 20000,
                    "income": 6000,
                    "product": {
                        "id": 1,
                        "name": "Test Product 1",
                        "barcode": "123456789",
                        "price": 10000,
                        "price_purchase": 7000,
                        "current_stock": 98,
                        "minimum_stock": 10,
                        "is_active": true,
                        "category": {
                            "id": 1,
                            "name": "Test Category",
                            "is_active": true
                        }
                    }
                }
            ],
            "items_count": 2,
            "total_quantity": 3
        }
    ],
    "links": {...},
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

#### Create Transaction (POS Sale)
```http
POST /api/transactions
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ],
    "payment_method": "cash",
    "payment_amount": 40000,
    "notes": "Customer purchase"
}
```

**Required Fields:**
- `items` - Array of items to sell (minimum 1 item)
  - `product_id` - ID of the product
  - `quantity` - Quantity to sell (positive integer)
- `payment_method` - Payment method: `cash`, `debit_card`, `credit_card`, `bank_transfer`, `e_wallet`, `qris`
- `payment_amount` - Amount paid by customer (must be >= total amount)

**Optional Fields:**
- `notes` - Additional notes (max 1000 characters)

**Response (201):**
```json
{
    "message": "Transaction created successfully.",
    "data": {
        "id": 1,
        "transaction_number": "TRX202509280001",
        "user_id": 1,
        "total_amount": 35000,
        "payment_method": "cash",
        "payment_amount": 40000,
        "change_amount": 5000,
        "income": 11000,
        "status": "completed",
        "notes": "Customer purchase",
        "created_at": "2025-09-28T10:00:00.000000Z",
        "updated_at": "2025-09-28T10:00:00.000000Z",
        "user": {...},
        "items": [...]
    }
}
```

**Business Logic:**
- Automatically calculates totals and change
- Updates product stock levels
- Creates stock movement records
- Calculates profit/income per item
- Validates stock availability
- Ensures payment amount is sufficient

#### Get Transaction Details
```http
GET /api/transactions/{transaction_id}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "transaction_number": "TRX202509280001",
        "total_amount": 35000,
        "payment_method": "cash",
        "status": "completed",
        "user": {...},
        "items": [...]
    }
}
```

#### Cancel Transaction
```http
PATCH /api/transactions/{transaction_id}/cancel
```

**Response:**
```json
{
    "message": "Transaction cancelled successfully.",
    "data": {
        "id": 1,
        "status": "cancelled",
        ...
    }
}
```

**Business Logic:**
- Restores product stock levels
- Creates compensating stock movements
- Cannot cancel already cancelled transactions

### Reporting & Analytics

#### Get Daily Report
```http
GET /api/transactions/daily-report
```

**Query Parameters:**
- `date` (optional) - Specific date (YYYY-MM-DD), defaults to today

**Response:**
```json
{
    "data": {
        "date": "2025-09-28",
        "total_transactions": 15,
        "total_revenue": 450000,
        "total_income": 135000,
        "payment_methods": {
            "cash": {
                "count": 10,
                "total": 300000
            },
            "debit_card": {
                "count": 5,
                "total": 150000
            }
        },
        "top_products": [
            {
                "product": {
                    "id": 1,
                    "name": "Product Name"
                },
                "quantity_sold": 25,
                "total_revenue": 250000
            }
        ],
        "hourly_sales": [
            {
                "hour": "09",
                "transactions": 2,
                "revenue": 50000
            }
        ]
    },
    "transactions": [...]
}
```

#### Get Sales Analytics
```http
GET /api/transactions/analytics
```

**Query Parameters:**
- `period` (optional) - Time period: `7days`, `30days`, `90days`, `1year` (default: `30days`)
- `group_by` (optional) - Grouping: `day`, `week`, `month` (default: `day`)

**Response:**
```json
{
    "data": {
        "period": "30days",
        "group_by": "day",
        "total_revenue": 1250000,
        "total_income": 375000,
        "total_transactions": 125,
        "average_transaction_value": 10000,
        "sales_trend": [
            {
                "period": "2025-09-01",
                "transactions": 8,
                "revenue": 80000,
                "income": 24000
            }
        ],
        "payment_methods_breakdown": {
            "cash": {
                "count": 75,
                "total": 750000,
                "percentage": 60.0
            },
            "debit_card": {
                "count": 50,
                "total": 500000,
                "percentage": 40.0
            }
        },
        "top_products": [
            {
                "product": {...},
                "quantity_sold": 150,
                "total_revenue": 300000
            }
        ],
        "top_categories": [
            {
                "category_id": 1,
                "category_name": "Electronics",
                "quantity_sold": 200,
                "total_revenue": 500000
            }
        ]
    }
}
```

## Payment Methods

The API supports the following payment methods:
- **`cash`** - Cash payment
- **`debit_card`** - Debit card payment
- **`credit_card`** - Credit card payment
- **`bank_transfer`** - Bank transfer
- **`e_wallet`** - Electronic wallet (GoPay, OVO, Dana, etc.)
- **`qris`** - QR Code Indonesian Standard

## Transaction Statuses

- **`completed`** - Transaction successfully completed
- **`cancelled`** - Transaction cancelled (stock restored)

## Error Responses

### Insufficient Stock (422)
```json
{
    "message": "Transaction failed: Insufficient stock for product: Product Name. Available: 5, Required: 10"
}
```

### Insufficient Payment (422)
```json
{
    "message": "Transaction failed: Payment amount is insufficient"
}
```

### Validation Error (422)
```json
{
    "message": "The items field is required. (and 2 more errors)",
    "errors": {
        "items": ["The items field is required."],
        "payment_method": ["The payment method field is required."],
        "payment_amount": ["The payment amount field is required."]
    }
}
```

### Item Validation Error (422)
```json
{
    "message": "The items.0.quantity field is required.",
    "errors": {
        "items.0.quantity": ["The items.0.quantity field is required."]
    }
}
```

### Already Cancelled (422)
```json
{
    "message": "Transaction is already cancelled."
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
    "message": "No query results for model [App\\Models\\Transaction] 99999"
}
```

## Usage Examples

### Simple Cash Sale
```bash
curl -X POST "http://your-app.com/api/transactions" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ],
    "payment_method": "cash",
    "payment_amount": 75000,
    "notes": "Walk-in customer"
  }'
```

### Get Today's Sales Report
```bash
curl -X GET "http://your-app.com/api/transactions/daily-report" \
  -H "Authorization: Bearer {token}"
```

### Filter Transactions by Payment Method
```bash
curl -X GET "http://your-app.com/api/transactions?payment_method=cash&period=this_week" \
  -H "Authorization: Bearer {token}"
```

### Get Weekly Analytics
```bash
curl -X GET "http://your-app.com/api/transactions/analytics?period=7days&group_by=day" \
  -H "Authorization: Bearer {token}"
```

### Cancel a Transaction
```bash
curl -X PATCH "http://your-app.com/api/transactions/123/cancel" \
  -H "Authorization: Bearer {token}"
```

### Search Transactions by Number
```bash
curl -X GET "http://your-app.com/api/transactions?search=TRX20250928" \
  -H "Authorization: Bearer {token}"
```

## Integration Tips

### POS Integration
1. **Product Selection**: Use the Product API to get available products with stock levels
2. **Real-time Stock Check**: Always verify stock before adding items to transaction
3. **Payment Processing**: Calculate change automatically, validate payment amount
4. **Receipt Generation**: Use transaction details from the response for receipt printing
5. **Offline Support**: Store transactions locally and sync when connection is available

### Reporting Integration
1. **Dashboard Widgets**: Use daily report endpoint for dashboard summaries
2. **Analytics Charts**: Use analytics endpoint with different periods for trend visualization
3. **Export Functions**: Query transactions with filters for CSV/Excel export
4. **Real-time Updates**: Poll endpoints periodically or implement WebSocket for live updates

### Error Handling
Always handle common error scenarios:
- Insufficient stock validation
- Payment amount validation  
- Network connectivity issues
- Authentication token expiration
- Product availability changes

### Performance Considerations
- Use pagination for large transaction lists
- Filter by date ranges to limit data size
- Cache analytics data for frequently accessed reports
- Use appropriate per_page values (default 15, max 100)
