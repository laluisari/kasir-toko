# 🔐 Sistem Autentikasi & Role-Based Access Control (RBAC)

## Ringkasan

Sistem Kasir telah dilengkapi dengan:
- ✅ **Login authentication** - Autentikasi berbasis email dan password
- ✅ **Role-based access control (RBAC)** - Kontrol akses berdasarkan role (Admin/Kasir)
- ✅ **Middleware protection** - Middleware untuk autentikasi dan otorisasi
- ✅ **Demo accounts** - Akun demo untuk testing

---

## 📋 Demo Credentials

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Admin | admin@kasir.test | password | Dashboard, Inventory, Produk, Kategori |
| Kasir 1 | kasir@kasir.test | password | Dashboard, Kasir (POS), History |
| Kasir 2 | kasir2@kasir.test | password | Dashboard, Kasir (POS), History |

---

## 🛠️ Komponen yang Dibuat

### 1. **Middleware**

#### `app/Http/Middleware/Authenticate.php`
- Memeriksa apakah user sudah login
- Redirect ke login jika belum authenticated
- Digunakan untuk melindungi semua rute yang membutuhkan login

```php
Route::middleware(['auth'])->group(function () {
    // Protected routes
});
```

#### `app/Http/Middleware/CheckRole.php`
- Memeriksa role user terhadap roles yang diizinkan
- Menerima parameter roles yang diizinkan
- Redirect ke dashboard jika tidak punya akses

```php
Route::middleware(['role:admin,kasir'])->group(function () {
    // Only accessible by admin or kasir
});
```

### 2. **AuthController** (`app/Http/Controllers/AuthController.php`)

**Methods:**

#### `showLogin()`
- Tampilkan halaman login
- Route: `GET /login` → `login`

#### `login(Request $request)`
- Handle login request
- Validasi: email (required, email), password (required, min:6)
- Remember me checkbox support
- Redirect berdasarkan role:
  - Admin → `/dashboard`
  - Kasir → `/admin/sales`
- Route: `POST /login` → `login.post`

#### `logout(Request $request)`
- Handle logout
- Invalidate session
- Regenerate token
- Redirect ke login
- Route: `POST /logout` → `logout`

### 3. **Login View** (`resources/views/auth/login.blade.php`)

**Features:**
- Modern gradient design
- Email & password input
- Remember me checkbox
- Error & validation messages
- Success/warning alerts
- Demo credentials info box
- Bootstrap 5 responsive

**Styling:**
- Gradient background: Purple to Pink
- Rounded card with shadow
- Input validation feedback
- Responsive mobile design

---

## 🔑 Route Protection

### Public Routes
```php
GET  /              → Welcome page
GET  /login         → Login form
POST /login         → Login handler
POST /logout        → Logout handler
```

### Protected Routes (Require Authentication)
```php
Route::middleware(['auth'])->group(function () {
    GET /dashboard          → Dashboard
    GET /home              → Users page
});
```

### Admin-Only Routes
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    POST   /categories           → Create category
    GET    /categories           → List categories
    PUT    /categories/{id}      → Update category
    DELETE /categories/{id}      → Delete category
    
    POST   /products            → Create product
    GET    /products            → List products
    PUT    /products/{id}       → Update product
    DELETE /products/{id}       → Delete product
});
```

### Kasir-Only Routes (Sales/POS)
```php
Route::middleware(['auth', 'role:kasir'])->group(function () {
    GET    /admin/sales                        → Kasir page (POS)
    GET    /admin/sales/search                 → Product search (AJAX)
    POST   /admin/sales/add-to-cart            → Add to cart (AJAX)
    PUT    /admin/sales/update-cart            → Update cart (AJAX)
    DELETE /admin/sales/remove-from-cart       → Remove from cart (AJAX)
    POST   /admin/sales/checkout               → Checkout (AJAX)
    GET    /admin/sales/{saleDocument}         → Show receipt
    GET    /admin/sales-history                → View history
});
```

---

## 🔐 Middleware Registration

Middleware didaftarkan di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

---

## 📊 Navbar Integration

Navbar telah diupdate untuk menampilkan:
- ✅ User name
- ✅ User role (badge: Admin/Kasir)
- ✅ Logout button dengan proper CSRF token

```blade
{{ auth()->user()->name }}
@if(auth()->user()->role === 'admin')
    <span class="badge bg-danger">Admin</span>
@else
    <span class="badge bg-success">Kasir</span>
@endif
```

---

## 🌱 Database Seeder

### UserSeeder (`database/seeders/UserSeeder.php`)

Membuat 3 akun demo:

1. **Admin**
   - Email: admin@kasir.test
   - Password: password (hashed)
   - Role: admin

2. **Kasir 1**
   - Email: kasir@kasir.test
   - Password: password (hashed)
   - Role: kasir

3. **Kasir 2**
   - Email: kasir2@kasir.test
   - Password: password (hashed)
   - Role: kasir

**Run seeder:**
```bash
php artisan db:seed --class=UserSeeder
```

---

## 🧪 Testing

### Test Admin Login
1. Go to `http://localhost:8000/login`
2. Email: `admin@kasir.test`
3. Password: `password`
4. Click login → redirect ke dashboard
5. Can access: Dashboard, Categories, Products
6. Cannot access: Kasir/POS page (redirected with error)

### Test Kasir Login
1. Go to `http://localhost:8000/login`
2. Email: `kasir@kasir.test`
3. Password: `password`
4. Click login → redirect ke kasir page
5. Can access: Dashboard, Kasir/POS, History
6. Cannot access: Categories, Products (redirected with error)

### Test Unauthorized Access
1. Login as Kasir
2. Try to access `/admin/categories`
3. Should redirect to dashboard with error message

### Test Logout
1. Login with any account
2. Click "Log Out" in navbar
3. Session invalidated
4. Redirect to login page

---

## 🔒 Security Features

1. **CSRF Protection**
   - Login form memiliki `@csrf` token
   - Logout form menggunakan POST dengan `@csrf`

2. **Password Hashing**
   - Password di-hash menggunakan `Hash::make()`
   - Model User menggunakan password casting

3. **Session Management**
   - Session di-regenerate setelah login
   - Session di-invalidate setelah logout
   - Remember token support

4. **Validation**
   - Email validation
   - Password minimum length (6 chars)
   - Database unique validation untuk update

---

## 📝 Alur Login

```
1. User membuka /login
   ↓
2. Lihat login form
   ↓
3. Input email & password
   ↓
4. Submit form → POST /login
   ↓
5. AuthController::login()
   ↓
6. Validasi input
   ↓
7. Auth::attempt($credentials)
   ↓
8. ✓ Berhasil:
   - Session regenerate
   - Redirect ke dashboard/kasir sesuai role
   ↓
8. ✗ Gagal:
   - Redirect ke login dengan error message
```

---

## 📝 Alur Logout

```
1. User klik "Log Out" di navbar
   ↓
2. Submit form → POST /logout
   ↓
3. AuthController::logout()
   ↓
4. Auth::logout()
   ↓
5. Session invalidate
   ↓
6. Session regenerate token
   ↓
7. Redirect ke login dengan success message
```

---

## ✅ Checklist Implementasi

- ✅ Middleware untuk autentikasi (Authenticate.php)
- ✅ Middleware untuk role checking (CheckRole.php)
- ✅ AuthController dengan login/logout
- ✅ Login view dengan design modern
- ✅ Route protection dengan middleware
- ✅ Role-based route grouping
- ✅ Database seeder untuk demo accounts
- ✅ Navbar integration dengan logout
- ✅ Session management
- ✅ CSRF protection
- ✅ Error handling & validation

---

## 🚀 Next Steps (Optional)

Fitur tambahan yang bisa ditambahkan:
- [ ] Profile page untuk edit user data
- [ ] Change password feature
- [ ] Login activity log
- [ ] Session timeout
- [ ] Two-factor authentication (2FA)
- [ ] User management admin panel
- [ ] Role assignment interface
- [ ] Login attempt tracking

---

**Status:** ✅ Selesai - Siap untuk testing!

Gunakan demo credentials di atas untuk login dan testing sistem.
