# API Authentication Examples

This document provides examples of how to use the API authentication endpoints.

## Login

### Request
```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password",
    "device_name": "Mobile App" // Optional
}
```

### Success Response (200)
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin"
    },
    "token": "1|abcdef123456789..."
}
```

### Error Response (422)
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

## Get Authenticated User

### Request
```http
GET /api/user
Authorization: Bearer {token}
```

### Response (200)
```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin"
    }
}
```

## Logout

### Request
```http
POST /api/logout
Authorization: Bearer {token}
```

### Response (200)
```json
{
    "message": "Logout successful"
}
```

## Revoke All Tokens

### Request
```http
POST /api/revoke-all-tokens
Authorization: Bearer {token}
```

### Response (200)
```json
{
    "message": "All tokens revoked successfully"
}
```

## Using the Token

Once you receive a token from the login endpoint, include it in all subsequent API requests:

```http
Authorization: Bearer {your-token-here}
```

### Example: Get Categories
```http
GET /api/categories
Authorization: Bearer 1|abcdef123456789...
```

### Example: Create Product
```http
POST /api/products
Authorization: Bearer 1|abcdef123456789...
Content-Type: application/json

{
    "name": "New Product",
    "price": "100000",
    "category_id": 1,
    "current_stock": 10,
    "minimum_stock": 5,
    "is_active": true
}
```

## Error Responses

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Validation Error (422)
```json
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password field is required."
        ]
    }
}
```
