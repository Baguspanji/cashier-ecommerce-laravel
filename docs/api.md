# API Documentation - Cashier E-commerce

This document describes the RESTful API endpoints for the Cashier E-commerce application, including the new sync API for PWA offline functionality.

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Generate API Token

```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "admin"
  }
}
```

### Using Token

```bash
Authorization: Bearer 1|abc123...
```

## Core API Endpoints

### Products

#### List Products

```bash
GET /api/products
Authorization: Bearer {token}

Query Parameters:
- page: int (default: 1)
- per_page: int (default: 15, max: 100)
- search: string
- category_id: int
- is_active: boolean

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "Product description",
      "price": "10.99",
      "current_stock": 100,
      "minimum_stock": 10,
      "barcode": "1234567890",
      "category_id": 1,
      "category": {
        "id": 1,
        "name": "Category Name"
      },
      "is_active": true,
      "created_at": "2025-08-19T12:00:00Z",
      "updated_at": "2025-08-19T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

#### Get Single Product

```bash
GET /api/products/{id}
Authorization: Bearer {token}

Response:
{
  "data": {
    "id": 1,
    "name": "Product Name",
    "description": "Product description",
    "price": "10.99",
    "current_stock": 100,
    "minimum_stock": 10,
    "barcode": "1234567890",
    "category_id": 1,
    "category": {
      "id": 1,
      "name": "Category Name"
    },
    "is_active": true,
    "created_at": "2025-08-19T12:00:00Z",
    "updated_at": "2025-08-19T12:00:00Z"
  }
}
```

#### Search by Barcode

```bash
GET /api/products/search-barcode/{barcode}
Authorization: Bearer {token}

Response:
{
  "data": {
    "id": 1,
    "name": "Product Name",
    "price": "10.99",
    "current_stock": 100,
    "barcode": "1234567890",
    "category": {
      "id": 1,
      "name": "Category Name"
    }
  }
}
```

### Categories

#### List Categories

```bash
GET /api/categories
Authorization: Bearer {token}

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Category Name",
      "description": "Category description",
      "products_count": 25,
      "created_at": "2025-08-19T12:00:00Z",
      "updated_at": "2025-08-19T12:00:00Z"
    }
  ]
}
```

### Transactions

#### Create Transaction

```bash
POST /api/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": "10.99"
    },
    {
      "product_id": 2,
      "quantity": 1,
      "unit_price": "5.50"
    }
  ],
  "payment_method": "cash",
  "payment_amount": "30.00",
  "notes": "Customer notes"
}

Response:
{
  "data": {
    "id": 1,
    "transaction_number": "TXN-20250819-001",
    "total_amount": "27.48",
    "payment_method": "cash",
    "payment_amount": "30.00",
    "change_amount": "2.52",
    "status": "completed",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Product Name",
        "quantity": 2,
        "unit_price": "10.99",
        "subtotal": "21.98"
      },
      {
        "id": 2,
        "product_id": 2,
        "product_name": "Another Product",
        "quantity": 1,
        "unit_price": "5.50",
        "subtotal": "5.50"
      }
    ],
    "created_at": "2025-08-19T12:00:00Z"
  }
}
```

## PWA Sync API (Phase 4 Implementation)

### Data Sync Endpoints

#### Get Products for Offline Cache

```bash
GET /api/sync/v1/products
Authorization: Bearer {token}

Query Parameters:
- last_updated: ISO 8601 timestamp (optional)
- include_inactive: boolean (default: false)

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "price": "10.99",
      "current_stock": 100,
      "minimum_stock": 10,
      "barcode": "1234567890",
      "category_id": 1,
      "is_active": true,
      "last_updated": "2025-08-19T12:00:00Z"
    }
  ],
  "meta": {
    "total": 100,
    "last_sync": "2025-08-19T13:00:00Z",
    "cache_expires_at": "2025-08-19T18:00:00Z"
  }
}
```

#### Get Categories for Offline Cache

```bash
GET /api/sync/v1/categories
Authorization: Bearer {token}

Query Parameters:
- last_updated: ISO 8601 timestamp (optional)

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Category Name",
      "description": "Category description",
      "last_updated": "2025-08-19T12:00:00Z"
    }
  ],
  "meta": {
    "total": 10,
    "last_sync": "2025-08-19T13:00:00Z"
  }
}
```

### Transaction Sync

#### Sync Offline Transactions

```bash
POST /api/sync/v1/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "sync_batch_id": "uuid-batch-123",
  "transactions": [
    {
      "offline_id": "offline_txn_123",
      "transaction_number": "OFF-20250819-001",
      "items": [
        {
          "product_id": 1,
          "product_name": "Product Name",
          "quantity": 2,
          "unit_price": "10.99",
          "subtotal": "21.98"
        }
      ],
      "total_amount": "21.98",
      "payment_method": "cash",
      "payment_amount": "25.00",
      "change_amount": "3.02",
      "notes": "Offline transaction",
      "created_at": "2025-08-19T12:00:00Z"
    }
  ]
}

Response:
{
  "sync_batch_id": "uuid-batch-123",
  "results": [
    {
      "offline_id": "offline_txn_123",
      "status": "success",
      "server_id": 123,
      "transaction_number": "TXN-20250819-123"
    }
  ],
  "conflicts": [
    {
      "offline_id": "offline_txn_124",
      "type": "stock_insufficient",
      "product_id": 2,
      "required_stock": 5,
      "available_stock": 3,
      "resolution_options": [
        "reduce_quantity",
        "cancel_item",
        "override_stock"
      ]
    }
  ],
  "summary": {
    "total_transactions": 1,
    "successful": 1,
    "failed": 0,
    "conflicts": 1
  }
}
```

#### Batch Sync (Large datasets)

```bash
POST /api/sync/v1/transactions/batch
Authorization: Bearer {token}
Content-Type: application/json

{
  "sync_batch_id": "uuid-batch-456",
  "transactions": [
    // ... up to 100 transactions
  ]
}

Response:
{
  "job_id": "sync-job-789",
  "status": "queued",
  "estimated_completion": "2025-08-19T13:05:00Z",
  "webhook_url": "/api/sync/v1/jobs/sync-job-789/status"
}
```

#### Check Batch Sync Status

```bash
GET /api/sync/v1/jobs/{job_id}/status
Authorization: Bearer {token}

Response:
{
  "job_id": "sync-job-789",
  "status": "processing", // queued, processing, completed, failed
  "progress": {
    "total": 100,
    "processed": 75,
    "percentage": 75
  },
  "results": {
    "successful": 70,
    "failed": 5,
    "conflicts": 0
  },
  "estimated_completion": "2025-08-19T13:03:00Z"
}
```

### Conflict Resolution

#### Resolve Sync Conflicts

```bash
POST /api/sync/v1/resolve-conflicts
Authorization: Bearer {token}
Content-Type: application/json

{
  "conflicts": [
    {
      "offline_id": "offline_txn_124",
      "conflict_id": "conflict_456",
      "resolution": "reduce_quantity",
      "resolution_data": {
        "new_quantity": 3
      }
    }
  ]
}

Response:
{
  "results": [
    {
      "offline_id": "offline_txn_124",
      "status": "resolved",
      "server_id": 124,
      "transaction_number": "TXN-20250819-124"
    }
  ]
}
```

### Sync Status & Health

#### Get Sync Status

```bash
GET /api/sync/v1/status
Authorization: Bearer {token}

Response:
{
  "user_id": 1,
  "pending_transactions": 5,
  "last_sync_at": "2025-08-19T12:00:00Z",
  "conflicts_pending": 2,
  "sync_queue_health": "healthy", // healthy, degraded, critical
  "server_time": "2025-08-19T13:00:00Z",
  "cache_status": {
    "products_last_updated": "2025-08-19T10:00:00Z",
    "categories_last_updated": "2025-08-19T10:00:00Z",
    "products_count": 100,
    "categories_count": 10
  }
}
```

#### Heartbeat

```bash
POST /api/sync/v1/heartbeat
Authorization: Bearer {token}
Content-Type: application/json

{
  "client_time": "2025-08-19T13:00:00Z",
  "app_version": "3.0.0",
  "sync_capabilities": ["background_sync", "conflict_resolution"]
}

Response:
{
  "server_time": "2025-08-19T13:00:05Z",
  "status": "healthy",
  "maintenance_mode": false,
  "urgent_updates": false,
  "recommended_sync_interval": 300, // seconds
  "max_offline_duration": 86400 // seconds (24 hours)
}
```

## Error Handling

### Standard Error Response

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "items.0.quantity": [
        "The quantity must be at least 1."
      ]
    }
  }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `UNAUTHORIZED` | 401 | Invalid or missing authentication token |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `CONFLICT` | 409 | Data conflict (e.g., insufficient stock) |
| `RATE_LIMITED` | 429 | Too many requests |
| `SERVER_ERROR` | 500 | Internal server error |
| `SYNC_CONFLICT` | 409 | Data synchronization conflict |
| `OFFLINE_MODE_REQUIRED` | 503 | Feature requires offline mode |

### Sync-Specific Errors

```json
{
  "error": {
    "code": "SYNC_CONFLICT",
    "message": "Data synchronization conflict detected",
    "conflicts": [
      {
        "type": "stock_mismatch",
        "product_id": 1,
        "expected_stock": 10,
        "actual_stock": 5,
        "resolution_required": true
      }
    ]
  }
}
```

## Rate Limiting

- **General API**: 60 requests per minute per user
- **Sync API**: 30 requests per minute per user
- **Batch Sync**: 5 requests per minute per user

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1692451200
```

## Webhook Support (Future)

### Sync Status Webhooks

```bash
POST {webhook_url}
Content-Type: application/json

{
  "event": "sync.completed",
  "job_id": "sync-job-789",
  "user_id": 1,
  "status": "completed",
  "results": {
    "successful": 95,
    "failed": 5,
    "conflicts": 0
  },
  "timestamp": "2025-08-19T13:05:00Z"
}
```

## SDK Example (JavaScript)

```javascript
// API Client example for PWA
class CashierAPI {
  constructor(baseURL, token) {
    this.baseURL = baseURL;
    this.token = token;
  }

  async syncProducts(lastUpdated = null) {
    const params = lastUpdated ? `?last_updated=${lastUpdated}` : '';
    const response = await fetch(`${this.baseURL}/api/sync/v1/products${params}`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }

  async syncOfflineTransactions(transactions) {
    const response = await fetch(`${this.baseURL}/api/sync/v1/transactions`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        sync_batch_id: crypto.randomUUID(),
        transactions
      })
    });
    return response.json();
  }

  async getSyncStatus() {
    const response = await fetch(`${this.baseURL}/api/sync/v1/status`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }
}
```

---

This API documentation will be expanded as the PWA sync functionality is implemented in Phase 4.
