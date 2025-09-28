# Category API Documentation

This document provides comprehensive examples for using the Category API endpoints for product categorization and management.

## Authentication

All Category API endpoints require authentication using Bearer token:

```http
Authorization: Bearer {your-token-from-login}
```

## Endpoints Overview

### Category Management

#### List Categories

```http
GET /api/categories
```

**Query Parameters:**
- `search` (optional) - Search by category name or description
- `per_page` (optional) - Number of results per page (default: 15, max: 100)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic devices and accessories",
            "products_count": 15,
            "created_at": "2024-09-28T08:00:00.000000Z",
            "updated_at": "2024-09-28T08:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Clothing",
            "description": "Fashion and apparel items",
            "products_count": 8,
            "created_at": "2024-09-28T08:30:00.000000Z",
            "updated_at": "2024-09-28T08:30:00.000000Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/categories?page=1",
        "last": "http://localhost:8000/api/categories?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 15,
        "to": 2,
        "total": 2
    }
}
```

#### Create Category

```http
POST /api/categories
Content-Type: application/json
```

**Request Body:**

```json
{
    "name": "Home & Garden",
    "description": "Home improvement and gardening supplies"
}
```

**Response (201 Created):**

```json
{
    "message": "Kategori berhasil ditambahkan.",
    "data": {
        "id": 3,
        "name": "Home & Garden",
        "description": "Home improvement and gardening supplies",
        "products_count": 0,
        "created_at": "2024-09-28T09:30:00.000000Z",
        "updated_at": "2024-09-28T09:30:00.000000Z"
    }
}
```

#### Show Category

```http
GET /api/categories/{id}
```

**Response:**

```json
{
    "data": {
        "id": 1,
        "name": "Electronics",
        "description": "Electronic devices and accessories",
        "products_count": 15,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T08:00:00.000000Z"
    }
}
```

#### Update Category

```http
PUT /api/categories/{id}
Content-Type: application/json
```

**Request Body:**

```json
{
    "name": "Electronics & Gadgets",
    "description": "Electronic devices, gadgets, and tech accessories"
}
```

**Response:**

```json
{
    "message": "Kategori berhasil diperbarui.",
    "data": {
        "id": 1,
        "name": "Electronics & Gadgets",
        "description": "Electronic devices, gadgets, and tech accessories",
        "products_count": 15,
        "created_at": "2024-09-28T08:00:00.000000Z",
        "updated_at": "2024-09-28T10:15:00.000000Z"
    }
}
```

#### Delete Category

```http
DELETE /api/categories/{id}
```

**Response (204 No Content):**

```json
{
    "message": "Kategori berhasil dihapus."
}
```

## API Usage Examples

### cURL Examples

#### List Categories with Search

```bash
curl -X GET "http://localhost:8000/api/categories?search=electronics&per_page=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

#### Create New Category

```bash
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Books & Media",
    "description": "Books, magazines, and digital media"
  }'
```

#### Update Category

```bash
curl -X PUT "http://localhost:8000/api/categories/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Technology",
    "description": "Modern technology and electronic devices"
  }'
```

#### Delete Category

```bash
curl -X DELETE "http://localhost:8000/api/categories/3" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### JavaScript/Fetch Examples

#### List Categories

```javascript
const response = await fetch('/api/categories?per_page=20', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const categories = await response.json();
console.log(categories.data);
```

#### Create Category

```javascript
const categoryData = {
    name: 'Sports & Recreation',
    description: 'Sports equipment and recreational items'
};

const response = await fetch('/api/categories', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify(categoryData)
});

const result = await response.json();
```

#### Update Category

```javascript
const updatedData = {
    name: 'Health & Beauty',
    description: 'Health care and beauty products'
};

const response = await fetch('/api/categories/2', {
    method: 'PUT',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify(updatedData)
});

const result = await response.json();
```

#### Delete Category

```javascript
const response = await fetch('/api/categories/3', {
    method: 'DELETE',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

if (response.ok) {
    console.log('Category deleted successfully');
}
```

## Error Responses

### Validation Errors (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "name": ["The name has already been taken."]
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
    "message": "Kategori tidak ditemukan."
}
```

### Conflict when Deleting (409)

```json
{
    "message": "Kategori tidak dapat dihapus karena masih memiliki produk."
}
```

## Field Descriptions

### Category Fields
- `id` - Unique category identifier (auto-generated)
- `name` - Category name (required, string, max: 255, unique)
- `description` - Category description (optional, text)
- `products_count` - Number of products in this category (auto-calculated)
- `created_at` - Creation timestamp (auto-generated)
- `updated_at` - Last update timestamp (auto-generated)

## Advanced Usage

### Using Categories with Products

Once you have categories created, you can associate products with them:

```javascript
// Create a product with category
const productData = {
    name: 'Samsung Smart TV 55"',
    barcode: 'SAM-TV-55-001',
    description: '4K Ultra HD Smart TV',
    price: 8500000,
    price_purchase: 7000000,
    category_id: 1, // Electronics category
    current_stock: 3,
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
```

### Filter Products by Category

```javascript
// Get all products in Electronics category
const response = await fetch('/api/products?category_id=1', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const electronicsProducts = await response.json();
```

## Notes

- All endpoints require proper authentication with `auth:sanctum` middleware
- Categories are automatically ordered by name in listing
- Search functionality covers both category name and description
- The `products_count` field shows the number of products associated with each category
- Category names must be unique across the system
- Categories with associated products cannot be deleted (returns 409 Conflict)
- When deleting a category, ensure all associated products are moved to other categories first
- Categories support pagination for large datasets
- Use descriptive names and descriptions for better organization
