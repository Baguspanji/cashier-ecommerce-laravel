# Product API Documentation

This document provides comprehensive examples for using the Product API endpoints for inventory and product management.

## Authentication

All Product API endpoints require authentication using Bearer token:

```http
Authorization: Bearer {your-token-from-login}
```

## Endpoints Overview

### Product Management

#### List Products
```http
GET /api/products
```

**Query Parameters:**
- `search` (optional) - Search by product name, description, or barcode
- `category_id` (optional) - Filter by specific category ID
- `status` (optional) - Filter by status: `active`, `inactive`
- `per_page` (optional) - Number of results per page (default: 15, max: 100)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Laptop Dell Inspiron 15",
            "barcode": "DEL123456789",
            "description": "High-performance laptop for business use",
            "price": "15000000.00",
            "price_purchase": "12000000.00",
            "image_path": null,
            "category_id": 1,
            "current_stock": 5,
            "minimum_stock": 2,
            "is_active": true,
            "created_at": "2024-09-28T08:00:00.000000Z",
            "updated_at": "2024-09-28T08:00:00.000000Z",
            "category": {
                "id": 1,
                "name": "Electronics",
                "description": "Electronic devices and accessories",
                "created_at": "2024-09-28T08:00:00.000000Z",
                "updated_at": "2024-09-28T08:00:00.000000Z"
            }
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/products?page=1",
        "last": "http://localhost:8000/api/products?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

#### Create Product
```http
POST /api/products
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Samsung Galaxy S24",
    "barcode": "SAM987654321",
    "description": "Latest smartphone with advanced camera",
    "price": 12000000,
    "price_purchase": 9500000,
    "category_id": 1,
    "current_stock": 10,
    "minimum_stock": 3,
    "is_active": true
}
```

**Response (201 Created):**
```json
{
    "message": "Produk berhasil ditambahkan.",
    "data": {
        "id": 2,
        "name": "Samsung Galaxy S24",
        "barcode": "SAM987654321",
        "description": "Latest smartphone with advanced camera",
        "price": "12000000.00",
        "price_purchase": "9500000.00",
        "image_path": null,
        "category_id": 1,
        "current_stock": 10,
        "minimum_stock": 3,
        "is_active": true,
        "created_at": "2024-09-28T09:30:00.000000Z",
        "updated_at": "2024-09-28T09:30:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories"
        }
    }
}
```

#### Show Product
```http
GET /api/products/{id}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "Laptop Dell Inspiron 15",
        "barcode": "DEL123456789",
        "description": "High-performance laptop for business use",
        "price": "15000000.00",
        "price_purchase": "12000000.00",
        "image_path": null,
        "category_id": 1,
        "current_stock": 5,
        "minimum_stock": 2,
        "is_active": true,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T08:00:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories"
        }
    }
}
```

#### Update Product
```http
PUT /api/products/{id}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Laptop Dell Inspiron 15 - Updated",
    "barcode": "DEL123456789",
    "description": "High-performance laptop for business use - Updated specs",
    "price": 16000000,
    "price_purchase": 12500000,
    "category_id": 1,
    "current_stock": 3,
    "minimum_stock": 2,
    "is_active": true
}
```

**Response:**
```json
{
    "message": "Produk berhasil diperbarui.",
    "data": {
        "id": 1,
        "name": "Laptop Dell Inspiron 15 - Updated",
        "barcode": "DEL123456789",
        "description": "High-performance laptop for business use - Updated specs",
        "price": "16000000.00",
        "price_purchase": "12500000.00",
        "image_path": null,
        "category_id": 1,
        "current_stock": 3,
        "minimum_stock": 2,
        "is_active": true,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T10:15:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories"
        }
    }
}
```

#### Delete Product
```http
DELETE /api/products/{id}
```

**Response (204 No Content):**
```json
{
    "message": "Produk berhasil dihapus."
}
```

### Special Product Endpoints

#### Search Product by Barcode
```http
GET /api/products/search-barcode/{barcode}
```

**Example:**
```http
GET /api/products/search-barcode/DEL123456789
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "Laptop Dell Inspiron 15",
        "barcode": "DEL123456789",
        "description": "High-performance laptop for business use",
        "price": "15000000.00",
        "price_purchase": "12000000.00",
        "image_path": null,
        "category_id": 1,
        "current_stock": 5,
        "minimum_stock": 2,
        "is_active": true,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T08:00:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories"
        }
    }
}
```

**Response when not found (404):**
```json
{
    "message": "Produk dengan barcode tersebut tidak ditemukan."
}
```

#### Toggle Product Status
```http
PATCH /api/products/{id}/toggle-status
```

**Response:**
```json
{
    "message": "Status produk berhasil diubah.",
    "data": {
        "id": 1,
        "name": "Laptop Dell Inspiron 15",
        "barcode": "DEL123456789",
        "description": "High-performance laptop for business use",
        "price": "15000000.00",
        "price_purchase": "12000000.00",
        "image_path": null,
        "category_id": 1,
        "current_stock": 5,
        "minimum_stock": 2,
        "is_active": false,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T10:45:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories"
        }
    }
}
```

## API Usage Examples

### cURL Examples

#### List Products with Search
```bash
curl -X GET "http://localhost:8000/api/products?search=laptop&per_page=5" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Create New Product
```bash
curl -X POST "http://localhost:8000/api/products" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "iPhone 15 Pro",
    "barcode": "APP123456789",
    "description": "Latest iPhone with titanium build",
    "price": 18000000,
    "price_purchase": 15000000,
    "category_id": 1,
    "current_stock": 8,
    "minimum_stock": 2,
    "is_active": true
  }'
```

#### Search by Barcode
```bash
curl -X GET "http://localhost:8000/api/products/search-barcode/DEL123456789" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### JavaScript/Fetch Examples

#### List Products
```javascript
const response = await fetch('/api/products?status=active&per_page=10', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const products = await response.json();
console.log(products.data);
```

#### Create Product
```javascript
const productData = {
    name: 'MacBook Pro M3',
    barcode: 'MAC123456789',
    description: 'Professional laptop with M3 chip',
    price: 25000000,
    price_purchase: 20000000,
    category_id: 1,
    current_stock: 5,
    minimum_stock: 1,
    is_active: true
};

const response = await fetch('/api/products', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify(productData)
});

const result = await response.json();
```

## Error Responses

### Validation Errors (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "price": ["The price must be a number."],
        "category_id": ["The selected category id is invalid."]
    }
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "message": "This action is unauthorized."
}
```

### Not Found (404)
```json
{
    "message": "Produk tidak ditemukan."
}
```

## Field Descriptions

### Product Fields
- `id` - Unique product identifier (auto-generated)
- `name` - Product name (required, string, max: 255)
- `barcode` - Product barcode (optional, string, unique)
- `description` - Product description (optional, text)
- `price` - Selling price (required, decimal with 2 decimal places)
- `price_purchase` - Purchase/cost price (required, decimal with 2 decimal places)
- `image_path` - Path to product image (optional, string)
- `category_id` - Associated category ID (required, must exist in categories table)
- `current_stock` - Current stock quantity (required, integer, min: 0)
- `minimum_stock` - Minimum stock alert threshold (required, integer, min: 0)
- `is_active` - Product status (required, boolean, default: true)
- `created_at` - Creation timestamp (auto-generated)
- `updated_at` - Last update timestamp (auto-generated)

### Category Relationship
- `category` - Associated category object with `id`, `name`, and `description`

## Notes

- All endpoints require proper authentication with `auth:sanctum` middleware
- Products are automatically ordered by name in listing
- Search functionality covers product name, description, and barcode
- Stock management is handled through separate Stock API endpoints
- Image upload functionality may require additional multipart/form-data handling
- Barcode must be unique across all products
- Prices are stored as decimal values with 2 decimal places
- Stock quantities cannot be negative
