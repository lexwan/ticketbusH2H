# API Documentation - Bridge Sistem Tiket Bus H2H

## Base URL
```
http://your-domain.com/api/v1
```

## Standar Response API

### Success Response
```json
{
  "status": true,
  "message": "success",
  "data": {...}
}
```

### Error Response
```json
{
  "status": false,
  "message": "error message",
  "errors": {...}
}
```

## Authentication

Semua endpoint yang memerlukan autentikasi harus menyertakan Bearer Token di header:

```
Authorization: Bearer {your_access_token}
```

## Rate Limiting

- Default: 60 requests per minute
- Jika limit terlampaui, akan mendapat response 429 Too Many Requests

## Keamanan API

### 1. Bearer Token (Laravel Passport)
Gunakan OAuth2 token untuk autentikasi:
```bash
POST /api/v1/login
```

### 2. Role & Permission Middleware
Endpoint tertentu memerlukan role khusus:
- `admin`: Full access
- `mitra`: Mitra access

### 3. Signature Verification (Callback)
Untuk endpoint callback, kirim signature di header:
```
X-Signature: {hmac_sha256_signature}
```

Cara generate signature:
```php
$signature = hash_hmac('sha256', $payload, $secret);
```

## Endpoints

### Authentication

#### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

#### Get Current User
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

### Protected Routes

Semua route di bawah ini memerlukan Bearer Token.

#### Admin Only Routes
Memerlukan role `admin`:
- POST /api/v1/mitra/register
- GET /api/v1/mitra
- GET /api/v1/mitra/{id}
- PUT /api/v1/mitra/{id}/fee

### Callback Routes

Memerlukan signature verification di header `X-Signature`:

```http
POST /api/v1/callbacks/provider/payment
X-Signature: {signature}
Content-Type: application/json

{
  "transaction_id": "TRX123",
  "status": "success"
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

## Testing

### Using cURL

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get user with token
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Postman

1. Import collection dari `/docs/postman_collection.json`
2. Set environment variable `base_url` dan `token`
3. Test semua endpoint
