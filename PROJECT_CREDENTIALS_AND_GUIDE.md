# 🛒 Fresh Express Grocery - Project Master Credentials & Setup Guide

> **Bilingual (English & Gujarati) E-Commerce Platform**  
> Built with **Laravel 12 (Web Storefront, Admin Panel, REST API)** and a standalone **Flutter Mobile Application**.

---

## 📌 1. Essential Project URLs

| Interface / Page | URL | Description |
| :--- | :--- | :--- |
| 🌐 **Customer Storefront** | [http://127.0.0.1:8000](http://127.0.0.1:8000) or [http://testing_pro.test](http://testing_pro.test) | Full e-commerce store with catalog, cart drawer, GPS map address picker, wishlist, bilingual language switcher. |
| 🛡️ **Admin Portal Login** | [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login) | Admin portal authentication with remember me and forgot password. |
| 📊 **Admin Dashboard** | [http://127.0.0.1:8000/admin/dashboard](http://127.0.0.1:8000/admin/dashboard) | Live sales analytics, inventory alerts, low-stock counters, order statuses. |
| 🎨 **Theme Settings (Screenshot Customizer)** | [http://127.0.0.1:8000/admin/settings](http://127.0.0.1:8000/admin/settings) | Dynamic theme colors, sidebar colors, Decent Infoways branding, Light/Dark/System mode switcher. |
| 👥 **Roles & Permissions** | [http://127.0.0.1:8000/admin/roles](http://127.0.0.1:8000/admin/roles) | Role management (Super Admin, Admin, Customer) & Permission matrix. |
| 👤 **Users Management** | [http://127.0.0.1:8000/admin/users](http://127.0.0.1:8000/admin/users) | Manage administrators, staff members, assign roles, toggle active status. |
| 📦 **Products & Gallery** | [http://127.0.0.1:8000/admin/products](http://127.0.0.1:8000/admin/products) | Drag & drop multi-image uploads, prices, stock, Gujarati/English translations. |
| 🏭 **Manage Stock** | [http://127.0.0.1:8000/admin/stock](http://127.0.0.1:8000/admin/stock) | Real-time stock +/- stepper adjustments and bulk updates. |
| 🚚 **Manage Orders** | [http://127.0.0.1:8000/admin/orders](http://127.0.0.1:8000/admin/orders) | Order workflow, delivery slot indicator, customer GPS map pin link. |
| 📄 **Tax Invoices** | [http://127.0.0.1:8000/admin/invoices/1](http://127.0.0.1:8000/admin/invoices/1) | Clean printable & downloadable GST tax invoice with barcode. |
| 🎁 **Offers & Promo Codes** | [http://127.0.0.1:8000/admin/offers](http://127.0.0.1:8000/admin/offers) | Percentage & flat discount promo coupons with min order threshold. |
| 📑 **Static Pages** | [http://127.0.0.1:8000/admin/pages](http://127.0.0.1:8000/admin/pages) | Dynamic CMS for About Us, Legal Information, Privacy Policy. |
| 🔑 **Forgot Password** | [http://127.0.0.1:8000/forgot-password](http://127.0.0.1:8000/forgot-password) | Mobile & Email OTP password reset flow (Demo OTP: `1234`). |

---

## 🔑 2. Login Credentials

### 👑 Super Administrator (Full Control)
- **Login URL:** [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login)
- **Email:** `superadmin@grocery.com`
- **Phone:** `9876500000`
- **Password:** `admin123`
- **Role:** `super_admin` (Full access to all modules, roles, permissions, and theme settings)

### 🛡️ Store Administrator
- **Login URL:** [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login)
- **Email:** `admin@grocery.com`
- **Phone:** `9876543210`
- **Password:** `admin123`
- **Role:** `admin` (Access to Catalog, Stock, Orders, Invoices, Sliders, Offers, Pages)

### 🛒 Customer / User Login (Storefront & Mobile App)
- **Login URL:** [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)
- **Mobile Number:** `9876543210` or `9988776655`
- **OTP:** `1234` *(Fixed demo OTP for instant testing)*
- **Email:** `customer@gmail.com` (Password: `customer123`)

---

## 🗄️ 3. MySQL Database Configuration

- **Database Name:** `testing_pro`
- **Host:** `127.0.0.1` (or `localhost`)
- **Port:** `3306`
- **Username:** `root`
- **Password:** `Decent@2018$$`
- **Charset / Collation:** `utf8mb4 / utf8mb4_unicode_ci`

```env
# In .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testing_pro
DB_USERNAME=root
DB_PASSWORD="Decent@2018$$"
```

---

## ⏱️ 4. Delivery Slot Business Logic (12:00 PM Rule)

- **Order placed BEFORE 12:00 PM Noon:**
  - `delivery_slot` = `2_hours`
  - Order is delivered within **2 Hours** via express fleet.
- **Order placed AFTER 12:00 PM Noon:**
  - `delivery_slot` = `next_day`
  - Order is prepared for delivery **Next Morning (09:00 AM - 12:00 PM)**.
- Displayed with live banner on Home page, Cart, Checkout, Order Tracking, and Flutter Mobile App.

---

## 🎨 5. Theme Customization & 3-Way Mode Switcher

### Settings Card (from Screenshot)
1. **Theme Primary Color:** Choose brand color across buttons, active badges, highlights (Default: `#000000` or `#16a34a`).
2. **Theme Hover Color:** Choose hover state color for buttons and clickable links (Default: `#a1a1a1`).
3. **Sidebar Background Color:** Custom navigation sidebar background (Default: `#000000`).
4. **Sidebar Active Item Color:** Color for opened menu toggles and active links (Default: `#add8e6`).
5. **Footer Branding:** Prefix `© 2026, made with ❤️ by` + Creator `Decent Infoways` + URL `https://decentinfoways.com`.

### 3-Option Theme Modes:
- ☀️ **Light:** Crisp clean light UI.
- 🌙 **Dark:** Deep dark mode.
- 💻 **System (Match PC):** Automatically detects Windows/Mac/Linux OS theme via `prefers-color-scheme` and synchronizes dynamically.

---

## 📱 6. Flutter Mobile Application (`flutter_app/`)

The mobile app code is located inside the dedicated folder:
📂 `c:\Users\devde\Herd\testing_pro\flutter_app\`

### 📦 How to Test & Run the Flutter App:

#### Step 1: Install Flutter SDK (if not already installed)
1. Download Flutter SDK from [flutter.dev](https://flutter.dev) (or unzip Flutter into `C:\flutter`).
2. Add `C:\flutter\bin` to your Windows System Environment Variable `PATH`.

#### Step 2: Open Terminal in Flutter folder
```powershell
cd c:\Users\devde\Herd\testing_pro\flutter_app
flutter pub get
```

#### Step 3: Run the App on Web / Chrome or Emulator
```powershell
# Run in Chrome browser instantly:
flutter run -d chrome

# Or run in connected Android Phone / Emulator:
flutter run
```

#### Step 4: Build Release APK for Mobile Installation:
```powershell
flutter build apk --release
```
The compiled APK will be generated at:
`flutter_app/build/app/outputs/flutter-apk/app-release.apk`

---

## 📬 7. REST API & Postman Collection

The file **`testing_pro_api_collection.json`** is located in the root directory.

### Quick API Summary:
- `POST /api/auth/otp/send` -> Send Mobile OTP
- `POST /api/auth/otp/verify` -> Verify OTP & Receive Bearer Token
- `POST /api/auth/forgot-password/send-otp` -> Forgot password OTP
- `POST /api/auth/forgot-password/reset` -> Reset password with OTP
- `GET /api/home` -> Aggregated Home Feed (Sliders, Categories, Products, Delivery Slot)
- `GET /api/settings` -> Global Theme & Branding settings
- `GET /api/categories` & `GET /api/products` -> Catalog browsing with filters
- `GET /api/cart` & `POST /api/cart/add` -> Cart management
- `POST /api/checkout/place-order` -> Auto 2-Hour vs Next-Day Slot Order Placement
- `GET /api/orders` & `GET /api/orders/{orderNumber}` -> Order history & Tracking
- `GET /api/addresses` & `POST /api/addresses` -> GPS Lat/Lng Map & Manual Addresses

---

## 🚀 8. Quick Commands

```powershell
# Navigate to project
cd c:\Users\devde\Herd\testing_pro

# Start Web Server
php artisan serve

# Re-migrate & seed MySQL database (if needed)
php artisan migrate:fresh --seed
```
