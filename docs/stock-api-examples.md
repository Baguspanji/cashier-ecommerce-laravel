# Stock Management API Documentation

This document provides comprehensive examples for using the Stock Management API endpoints.

## Authentication

All Stock API endpoints require authentication using Bearer token:

```http
Authorization: Bearer {your-token-from-login}
```

## Endpoints Overview

### Stock Movements

#### List Stock Movements
```http
GET /api/stock
```

**Query Parameters:**
- `product_id` (optional) - Filter by specific product
- `type` (optional) - Filter by movement type (`in`, `out`, `adjustment`)
- `date_from` (optional) - Filter from date (YYYY-MM-DD)
- `date_to` (optional) - Filter to date (YYYY-MM-DD)
- `per_page` (optional) - Number of results per page (default: 15)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "product_id": 1,
            "type": "in",
            "quantity": 10,
            "reference_id": null,
            "reference_type": null,
            "notes": "Initial stock",
            "user_id": 1,
            "product": {
                "id": 1,
                "name": "Product Name",
                "barcode": "123456789",
                "current_stock": 20,
                "category": {
                    "id": 1,
                    "name": "Category Name"
                }
            },
            "user": {
                "id": 1,
                "name": "Admin User"
            },
            "created_at": "2025-09-28T10:00:00.000000Z",
            "updated_at": "2025-09-28T10:00:00.000000Z"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

#### Create Stock Movement
```http
POST /api/stock
Content-Type: application/json

{
    "product_id": 1,
    "type": "in",
    "quantity": 5,
    "notes": "Stock replenishment"
}
```

**Required Fields:**
- `product_id` - ID of the product
- `type` - Movement type: `in`, `out`, or `adjustment`
- `quantity` - Quantity moved (positive integer)

**Optional Fields:**
- `notes` - Additional notes (max 1000 characters)

**Response (201):**
```json
{
    "message": "Stok penambahan berhasil dicatat.",
    "data": {
        "id": 2,
        "product_id": 1,
        "type": "in",
        "quantity": 5,
        "notes": "Stock replenishment",
        "product": {...},
        "user": {...},
        "created_at": "2025-09-28T10:00:00.000000Z"
    }
}
```

### Stock Overview

#### Get Stock Overview
```http
GET /api/stock/overview
```

**Query Parameters:**
- `category_id` (optional) - Filter by category
- `stock_status` (optional) - Filter by stock status (`low`, `out`)
- `per_page` (optional) - Number of results per page (default: 20)

**Response:**
```json
{
    "products": {
        "data": [
            {
                "id": 1,
                "name": "Product Name",
                "barcode": "123456789",
                "current_stock": 15,
                "minimum_stock": 5,
                "is_active": true,
                "category": {
                    "id": 1,
                    "name": "Category Name"
                },
                "created_at": "2025-09-28T10:00:00.000000Z"
            }
        ],
        "links": {...},
        "meta": {...}
    },
    "summary": {
        "total_products": 100,
        "low_stock_count": 5,
        "out_of_stock_count": 2,
        "total_stock_value": 15000000
    }
}
```

### Stock Alerts

#### Get Stock Alerts
```http
GET /api/stock/alerts
```

**Response:**
```json
{
    "low_stock": [
        {
            "id": 1,
            "name": "Product Low Stock",
            "current_stock": 2,
            "minimum_stock": 5,
            "category": {
                "id": 1,
                "name": "Category Name"
            }
        }
    ],
    "out_of_stock": [
        {
            "id": 2,
            "name": "Product Out of Stock",
            "current_stock": 0,
            "minimum_stock": 3,
            "category": {
                "id": 1,
                "name": "Category Name"
            }
        }
    ],
    "counts": {
        "low_stock": 1,
        "out_of_stock": 1
    }
}
```

### Product Stock Movements

#### Get Product Stock Movements
```http
GET /api/stock/products/{product_id}/movements
```

**Parameters:**
- `product_id` (path) - ID of the product

**Query Parameters:**
- `per_page` (optional) - Number of results per page (default: 15)

**Response:**
```json
{
    "product": {
        "id": 1,
        "name": "Product Name",
        "current_stock": 15,
        "minimum_stock": 5,
        "category": {
            "id": 1,
            "name": "Category Name"
        }
    },
    "movements": {
        "data": [
            {
                "id": 1,
                "type": "in",
                "quantity": 10,
                "notes": "Initial stock",
                "user": {
                    "id": 1,
                    "name": "Admin User"
                },
                "created_at": "2025-09-28T10:00:00.000000Z"
            }
        ],
        "links": {...},
        "meta": {...}
    }
}
```

### Bulk Stock Adjustment

#### Perform Bulk Stock Adjustment
```http
POST /api/stock/bulk-adjustment
Content-Type: application/json

{
    "adjustments": [
        {
            "product_id": 1,
            "new_stock": 20
        },
        {
            "product_id": 2,
            "new_stock": 15
        }
    ],
    "notes": "Monthly stock adjustment"
}
```

**Required Fields:**
- `adjustments` - Array of adjustments
  - `product_id` - ID of the product
  - `new_stock` - New stock quantity

**Optional Fields:**
- `notes` - Adjustment notes (max 1000 characters)

**Response:**
```json
{
    "message": "Penyesuaian stok massal berhasil dilakukan.",
    "processed_adjustments": [
        {
            "product_id": 1,
            "product_name": "Product 1",
            "old_stock": 15,
            "new_stock": 20,
            "type": "in",
            "quantity": 5,
            "movement_id": 10
        },
        {
            "product_id": 2,
            "product_name": "Product 2",
            "old_stock": 18,
            "new_stock": 15,
            "type": "out",
            "quantity": 3,
            "movement_id": 11
        }
    ],
    "total_processed": 2
}
```

## Stock Movement Types

- **`in`** - Stock increase (receiving inventory, returns, etc.)
- **`out`** - Stock decrease (sales, damage, theft, etc.)
- **`adjustment`** - Stock adjustment (counting corrections, etc.)

## Error Responses

### Validation Error (422)
```json
{
    "message": "The product id field is required. (and 2 more errors)",
    "errors": {
        "product_id": [
            "The product id field is required."
        ],
        "type": [
            "The type field is required."
        ],
        "quantity": [
            "The quantity field is required."
        ]
    }
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
    "message": "Product not found."
}
```

## Usage Examples

### Track Product Receiving
```bash
curl -X POST "http://your-app.com/api/stock" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "type": "in",
    "quantity": 50,
    "notes": "Received from supplier ABC"
  }'
```

### Monitor Low Stock Products
```bash
curl -X GET "http://your-app.com/api/stock/alerts" \
  -H "Authorization: Bearer {token}"
```

### View Category Stock Overview
```bash
curl -X GET "http://your-app.com/api/stock/overview?category_id=1&stock_status=low" \
  -H "Authorization: Bearer {token}"
```

### Bulk Stock Adjustment
```bash
curl -X POST "http://your-app.com/api/stock/bulk-adjustment" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "adjustments": [
      {"product_id": 1, "new_stock": 25},
      {"product_id": 2, "new_stock": 10}
    ],
    "notes": "End of month stock count adjustment"
  }'
```
