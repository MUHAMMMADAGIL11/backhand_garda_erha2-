# Dokumentasi API Register

## Endpoint Register

Endpoint untuk melakukan registrasi user baru ke dalam sistem.

### URL
```
POST /api/auth/register
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| username | string | Yes | Username unik (max 50 karakter) |
| password | string | Yes | Password minimal 6 karakter |
| password_confirmation | string | Yes | Konfirmasi password harus sama dengan password |
| nama_lengkap | string | Yes | Nama lengkap user (max 255 karakter) |
| role | enum | Yes | Role user: `AdminGudang`, `PetugasOperasional`, atau `KepalaDivisi` |
| divisi | string | No | Nama divisi (max 100 karakter, optional) |

### Contoh Request

#### Menggunakan cURL
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "johndoe",
    "password": "password123",
    "password_confirmation": "password123",
    "nama_lengkap": "John Doe",
    "role": "AdminGudang",
    "divisi": "Gudang Utama"
  }'
```

#### Menggunakan Postman
- Method: `POST`
- URL: `http://localhost:8000/api/auth/register`
- Headers:
  - `Content-Type: application/json`
  - `Accept: application/json`
- Body (raw JSON):
```json
{
  "username": "johndoe",
  "password": "password123",
  "password_confirmation": "password123",
  "nama_lengkap": "John Doe",
  "role": "AdminGudang",
  "divisi": "Gudang Utama"
}
```

#### Menggunakan JavaScript (Fetch API)
```javascript
fetch('http://localhost:8000/api/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    username: 'johndoe',
    password: 'password123',
    password_confirmation: 'password123',
    nama_lengkap: 'John Doe',
    role: 'AdminGudang',
    divisi: 'Gudang Utama'
  })
})
.then(response => response.json())
.then(data => {
  console.log('Success:', data);
  // Simpan token untuk request selanjutnya
  localStorage.setItem('token', data.data.access_token);
})
.catch((error) => {
  console.error('Error:', error);
});
```

### Response Success (201 Created)

```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id_user": 1,
      "username": "johndoe",
      "nama_lengkap": "John Doe",
      "role": "AdminGudang",
      "divisi": "Gudang Utama"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Response Error (422 Validation Error)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "username": [
      "The username has already been taken."
    ],
    "password": [
      "The password confirmation does not match."
    ],
    "role": [
      "The selected role is invalid."
    ]
  }
}
```

### Response Error (500 Server Error)

```json
{
  "success": false,
  "message": "Internal server error"
}
```

## Endpoint Login

Endpoint untuk melakukan login dan mendapatkan access token.

### URL
```
POST /api/auth/login
```

### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| username | string | Yes | Username yang sudah terdaftar |
| password | string | Yes | Password user |

### Contoh Request

```json
{
  "username": "johndoe",
  "password": "password123"
}
```

### Response Success (200 OK)

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id_user": 1,
      "username": "johndoe",
      "nama_lengkap": "John Doe",
      "role": "AdminGudang",
      "divisi": "Gudang Utama"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Response Error (401 Unauthorized)

```json
{
  "success": false,
  "message": "Username atau password salah"
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Akun Anda tidak aktif"
}
```

## Menggunakan Token untuk Request Terproteksi

Setelah mendapatkan `access_token` dari register atau login, gunakan token tersebut untuk mengakses endpoint yang dilindungi.

### Headers
```
Authorization: Bearer {access_token}
Accept: application/json
```

### Contoh Request Terproteksi

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

## Catatan Penting

1. **Password**: Minimal 6 karakter dan harus dikonfirmasi dengan `password_confirmation`
2. **Username**: Harus unik, tidak boleh duplikat
3. **Role**: Hanya bisa menggunakan salah satu dari:
   - `AdminGudang`
   - `PetugasOperasional`
   - `KepalaDivisi`
4. **Token**: Token JWT memiliki waktu kedaluwarsa (default: 60 menit)
5. **Divisi**: Field optional, bisa dikosongkan

## Troubleshooting

### Error: "Class 'Tymon\JWTAuth\Providers\LaravelServiceProvider' not found"
Pastikan JWT Auth sudah terinstall:
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### Error: "SQLSTATE[42S02]: Base table or view not found"
Pastikan migration sudah dijalankan:
```bash
php artisan migrate
```

### Error: "Route [login] not defined"
Pastikan route sudah terdaftar di `routes/api.php`

