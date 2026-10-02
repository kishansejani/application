# 🛒 Fresh Express Grocery - Flutter Mobile Application

A state-of-the-art Flutter grocery mobile application with **Bilingual (Gujarati & English)** localization, **OTP Authentication**, **2-Hour Express Delivery calculation**, **Interactive Leaflet Map Address Picker (GPS & Manual)**, **Live Cart Management**, and **REST API integration** connected with Laravel Backend.

---

## 📁 Clean Folder & Architecture Structure

This codebase is 100% modular and decoupled, ready for any Flutter developer to pick up:

```text
flutter_app/
├── lib/
│   ├── constants/
│   │   ├── api_constants.dart      # Base URLs, dynamic API endpoints
│   │   └── app_colors.dart         # Curated theme colors (Primary, Accent, Background)
│   ├── l10n/
│   │   ├── app_localizations.dart  # Localization Delegate & context.tr('key')
│   │   ├── en.dart                 # English translation dictionary
│   │   └── gu.dart                 # Gujarati translation dictionary
│   ├── models/
│   │   ├── user.dart               # User model & auth attributes
│   │   ├── category.dart           # Category & SubCategory models
│   │   ├── product.dart            # Product & Gallery images model
│   │   ├── cart_item.dart          # Cart item model
│   │   ├── order.dart              # Order & OrderItem models
│   │   ├── address.dart            # GPS + Manual Address model
│   │   ├── slider_item.dart        # Home slider banner model
│   │   ├── offer.dart              # Coupon & discount model
│   │   └── page_content.dart       # Static page model (About Us, Privacy Policy, Legal Info)
│   ├── services/
│   │   ├── api_service.dart        # Generic HTTP Client (GET, POST, PUT, DELETE with Bearer token & Accept-Language header)
│   │   └── storage_service.dart    # SharedPreferences local storage
│   ├── providers/
│   │   ├── auth_provider.dart      # Phone OTP Auth & user session state
│   │   ├── cart_provider.dart      # Live cart calculation, coupon application, and subtotal
│   │   ├── wishlist_provider.dart  # Wishlist toggle & items state
│   │   ├── locale_provider.dart    # Language switcher state (English / ગુજરાતી)
│   │   └── product_provider.dart   # Home feed, catalog filters & delivery slot state
│   ├── widgets/
│   │   ├── product_card.dart       # Reusable product card with quantity stepper & discount tag
│   │   ├── category_card.dart      # Category avatar & title card
│   │   ├── delivery_slot_banner.dart# Express 2-Hours vs Next-Day dynamic status banner
│   │   ├── custom_button.dart      # Standardized rounded button with loading indicator
│   │   └── drawer_menu.dart        # Side drawer navigation & language switcher
│   ├── screens/
│   │   ├── splash_screen.dart
│   │   ├── auth/
│   │   │   ├── phone_login_screen.dart
│   │   │   └── otp_verify_screen.dart
│   │   ├── main_navigation_screen.dart
│   │   ├── home/
│   │   │   └── home_screen.dart
│   │   ├── catalog/
│   │   │   ├── categories_screen.dart
│   │   │   ├── subcategories_screen.dart
│   │   │   ├── product_list_screen.dart
│   │   │   └── product_detail_screen.dart
│   │   ├── cart/
│   │   │   └── cart_screen.dart
│   │   ├── checkout/
│   │   │   ├── checkout_screen.dart
│   │   │   └── order_success_screen.dart
│   │   ├── orders/
│   │   │   ├── orders_screen.dart
│   │   │   ├── order_detail_screen.dart
│   │   │   └── order_track_screen.dart
│   │   ├── wishlist/
│   │   │   └── wishlist_screen.dart
│   │   ├── addresses/
│   │   │   ├── addresses_screen.dart
│   │   │   └── add_address_map_screen.dart
│   │   ├── offers/
│   │   │   └── offers_screen.dart
│   │   ├── profile/
│   │   │   └── profile_screen.dart
│   │   └── pages/
│   │       └── static_page_screen.dart
│   └── main.dart                   # Application entry point with MultiProvider setup
└── pubspec.yaml                    # Flutter dependencies
```

---

## ⚡ How to Run the Flutter App

### 1. Install Dependencies
```bash
flutter pub get
```

### 2. Configure Backend API URL
Open `lib/constants/api_constants.dart`:
- **Android Emulator:** `http://10.0.2.2:8000/api`
- **iOS Simulator / Web:** `http://localhost:8000/api`
- **Physical Device:** `http://<YOUR_LOCAL_IP>:8000/api` (e.g. `http://192.168.1.100:8000/api`)

### 3. Run the App
```bash
flutter run
```

---

## 🔑 Demo Login Credentials

- **Customer Mobile:** `9876543210` or `9988776655`
- **Demo OTP:** `1234`
- **Admin Email (Web Admin):** `admin@grocery.com` (Password: `admin123`)
- **Super Admin Email (Web Admin):** `superadmin@grocery.com` (Password: `admin123`)

---

## 🌐 Postman Collection
Import `testing_pro_api_collection.json` located in the root project folder into Postman for complete endpoint documentation and testing.
