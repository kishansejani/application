# Fresh Express Grocery – Flutter App

A bilingual (**English / ગુજરાતી**) Material 3 grocery app for the Fresh Express
Laravel backend: OTP login, 2-hour express / next-day delivery, map address
picker, cart with coupons, checkout, order tracking, wishlist, offers, light /
dark theme and an in-app WebView for website pages.

**Requirements:** Flutter **3.24+** (Dart 3.5+). Tested API contract: `routes/api.php`
of the Laravel project.

---

## Quick start

```bat
cd flutter_app
setup_platforms.bat          :: creates android/ + ios/ (first time) and runs flutter pub get
flutter run
```

macOS / Linux:

```bash
cd flutter_app
flutter create --platforms=android,ios --org com.freshexpress .   # first time only
flutter pub get
flutter run
```

After `flutter create`, apply the AndroidManifest / Info.plist changes in
[`platform_setup/README.md`](platform_setup/README.md) (INTERNET + location
permissions, cleartext HTTP for local development, `url_launcher` queries,
minSdk 21).

Release APK: run `build_apk.bat` from the project root (it also runs
`flutter create` automatically when `android\` is missing).

### Backend base URL

Default: `http://10.0.2.2:8000/api` (Android emulator → your PC's localhost).

| Where the app runs | API base URL |
|---|---|
| Android emulator | `http://10.0.2.2:8000/api` (default) |
| iOS simulator | `http://localhost:8000/api` |
| Real phone (same Wi-Fi) | `http://<PC-LAN-IP>:8000/api`, e.g. `http://192.168.1.100:8000/api` |

Change it without editing code:

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api
flutter build apk --release --dart-define=API_BASE_URL=https://your-domain.com/api
```

or edit `defaultValue` in `lib/constants/api_constants.dart`.
`ApiConstants.webBaseUrl` (used by the WebView and for image paths) is derived
automatically by stripping `/api`.

For a real device start Laravel on all interfaces:
`php artisan serve --host=0.0.0.0 --port=8000`.
Image URLs that Laravel generates with `localhost` / `127.0.0.1` are rewritten
to the API host automatically (`lib/utils/image_url.dart`).

### Login / Sign up

- **Login** (`POST /auth/otp/send`): if the server answers `404 {needs_registration: true}` the app
  opens **Register** with the phone pre-filled and a notice.
- **Register** (`POST /auth/register {name, phone, email?, language}`): full name, mobile, optional
  e-mail, language (EN / ગુ, switches the app immediately), accept terms. A `422 {already_registered: true}`
  offers "Login with OTP" instead.
- **OTP** (`POST /auth/otp/verify`, resend via `POST /auth/otp/resend {phone, purpose}`): length from
  `otp_length` (default 4), resend cooldown from `resend_after` / `resend_in` (default 30 s; a 429 `retry_after` restarts it). A `demo_otp` in the
  response (demo mode only) is shown as a hint chip; the app never depends on it.

---

## Design system

`lib/theme/app_theme.dart`

- Material 3 `ColorScheme.fromSeed` (seed **#16A34A** green) – light + dark themes
- **Plus Jakarta Sans** via `google_fonts` (Gujarati glyphs fall back to the system Gujarati font)
- Tokens: `AppRadius` (12 / 16 / 20 / 28), `AppSpacing` (4-pt grid)
- Themed app bars, filled / outlined / text buttons, inputs, `NavigationBar`,
  `NavigationRail`, chips, bottom sheets, snackbars, list tiles, segmented buttons, badges
- Cards use the `AppCard` widget (rounded 16, hairline outline) instead of
  `ThemeData.cardTheme`, so the code compiles on every Flutter version
  (CardTheme → CardThemeData migration)
- `context.colors`, `context.textStyles`, `context.isDark`, `Color.fade(0.4)` helpers
- Theme mode **Light / Dark / System** – `ThemeProvider`, persisted in
  shared_preferences, switch in *Profile → Preferences* and the drawer

### Responsive layout (`lib/utils/responsive.dart`)

- `< 840dp`: bottom M3 `NavigationBar` (cart badge)
- `≥ 840dp`: `NavigationRail` (extended with labels at ≥ 1200dp)
- Product grids: 2 columns phone, 3–4 tablet, 5 desktop
- Content width capped at 1200dp (forms 640dp); product page, cart, checkout and
  order detail switch to two-pane layouts on wide screens

### Reusable widgets (`lib/widgets/`)

`app_card`, `app_network_image` (cached + shimmer + fallback), `shimmer_box`
(+ product / list / grid skeletons), `empty_state` (+ `LoginRequiredState`),
`error_state` (offline vs server error, retry), `section_header`, `price_text`
(+ `DiscountBadge`), `qty_stepper`, `primary_button`, `status_chip`,
`banner_carousel` (own PageView carousel – `carousel_slider` was removed because
it clashes with Flutter 3.22+), `offer_card` (coupon ticket with copy-code),
`order_timeline`, `product_card` (+ `CartControl`), `category_card`,
`delivery_slot_banner`, `drawer_menu`, `app_dropdown` (`AppDropdownField`, `AppSelectChip`,
`appMenuItem` – one themed look for every select / popup menu: rounded elevated menu, selected
entry tinted with a check), `auth_widgets` (`AuthModeSwitch`, `PhoneField`, `AuthNotice`).

Icons are `onSurfaceVariant` at rest and turn brand green on press / hover / focus
(`iconButtonTheme`, `iconTheme`, ink splash / highlight colours). Menus use
`popupMenuTheme`, `menuTheme` and `dropdownMenuTheme`.

### Language

Switching EN / ગુજરાતી (Profile, drawer, Register) updates `MaterialApp.locale` instantly, every
string comes from `l10n/en.dart` / `l10n/gu.dart` (network error texts too), API calls send
`Accept-Language` + `X-Locale: gu|en`, and WebView pages of our website get `?lang=gu|en`.

---

## Screens

| Area | Highlights |
|---|---|
| Splash | Animated logo, loads home feed + session in parallel |
| Login / Register | Login \| Register segmented switch, phone field (+91), sign-up form (name, mobile, e-mail, language dropdown, terms), needs-registration / already-registered handling |
| OTP | Pulsing shield header, staggered boxes, digit pop, shake + red on wrong code, animated check on success, circular resend countdown, haptics, paste button, auto-submit, honours *reduce motion* |
| Home | Greeting + delivery location, search bar, delivery-slot banner, auto-play banner carousel with indicators, category grid, offers strip, featured rail, fresh-arrivals grid, trust badges, shimmer skeletons, pull-to-refresh, error/offline state |
| Categories / Sub-categories | Tiles with image, sub-category preview; sub-category grid; "All in category" |
| Product list | Search with debounce + recent searches, sort/filter bottom sheet (featured / newest / price ↑ / price ↓, in-stock only), sub-category chips, infinite scroll |
| Product detail | Image PageView + dots + full-screen zoom viewer, discount badge, stock state, delivery promise, highlights, expandable description, related products, sticky add-to-cart / stepper bar, wishlist & copy-link |
| Cart | Swipe-to-delete with **Undo**, steppers, free-delivery progress, bill summary with savings, empty state |
| Checkout | Numbered sections: address selector (bottom sheet + add new), delivery slot (server decides 2-hour vs next-day), coupon (apply / remove / browse offers), payment tiles (COD / UPI / Card), delivery instructions, items summary, sticky Place-order |
| Order success | Animated check, order number copy, track / invoice / continue shopping |
| Orders | Status filter chips, order cards, infinite scroll, track / invoice / details |
| Order detail | Timeline, delivery info (call customer), items, bill, invoice + web tracking |
| Track order | Native vertical timeline, auto-refresh every 30 s, opens web tracking |
| Wishlist | Responsive grid, optimistic heart toggle |
| Addresses | Cards with default badge, edit / delete / set default, OSM map picker with GPS, zoom buttons |
| Offers | Coupon tickets, copy code, "Apply" in select mode (from checkout) |
| Profile | Header card, stats, account links, language (EN / ગુ), theme mode, About / Privacy / Terms (WebView), website, share / rate placeholders, logout confirmation |

---

## WebView

`lib/screens/webview/app_webview_screen.dart` – reusable in-app browser:

- App bar with page title + host subtitle (lock icon for HTTPS), close button
- `LinearProgressIndicator`, refresh action, overflow menu (**Reload**, **Open in browser**, **Copy link**)
- `PopScope` back handling – goes back inside the web history first
- Main-frame load errors show an offline / error state with **Retry**
- Non-http(s) links (`tel:`, `mailto:`, `whatsapp:`, `upi:`, `geo:`, `intent://` …)
  are opened with `url_launcher` (Android `intent://` links fall back to `browser_fallback_url`)
- Sends the UI language as `Accept-Language`

```dart
AppWebViewScreen.open(context, url: ApiConstants.pageUrl('about-us'), title: 'About Us');
```

Where it is used:

| Feature | Web route | Why WebView |
|---|---|---|
| About / Privacy / Terms (legal-information) | `/page/{slug}` | CMS pages managed in the admin panel |
| Invoice | `/order/invoice/{orderNumber}` | Printable invoice view |
| Web tracking | `/order/track?order_number=…` | Public tracking page |
| Visit our website | `/` | Full storefront |
| Banner with `link_type = custom` | `link_url` | External campaign pages |

`CustomerOrderController@invoice` and `@track` do **not** require a web
session (they look the order up by number), so they work for app users inside
the WebView. Order history and order details (which need the logged-in user)
are **native** screens using the Sanctum API (`GET /orders`, `GET /orders/{orderNumber}`).
The native *Track order* screen is the primary tracking UI; the web page is offered as an extra.

---

## Architecture

```text
lib/
├── constants/   api_constants.dart (base URL, endpoints, web URLs), app_colors.dart
├── l10n/        app_localizations.dart (context.tr('key', {'arg': 'v'})), en.dart, gu.dart
├── models/      product, category, cart_item, order, address, offer, slider_item, user, page_content, json_utils
├── providers/   auth, cart, wishlist, product, address, order, locale, theme  (provider / ChangeNotifier)
├── services/    api_service.dart (JSON, Bearer token, Accept-Language, timeouts, ApiException)
├── theme/       app_theme.dart
├── utils/       app_actions.dart, ui_helpers.dart, responsive.dart, formatters.dart, image_url.dart
├── widgets/     reusable UI
└── screens/     splash, auth, home, catalog, cart, checkout, orders, wishlist, addresses, offers, profile, webview
```

API contract notes (aligned with `app/Http/Controllers/Api/*`):

- All responses are `{status, message, data}`; `ApiService.dataOf()` unwraps `data`
- Search uses `GET /products?q=…&sort=featured|newest|price_asc|price_desc&page=n`
- Cart: `POST /cart/add`, `POST /cart/update {product_id, quantity}`, `DELETE /cart/{productId}`
- Checkout: `POST /checkout/coupon/apply {code}`, `POST /checkout/place-order {address_id, payment_method: cod|upi|card, coupon_code?, notes?}`
- Addresses: `type` Home/Work/Other, `recipient_name`, `recipient_phone`, `house_no`, `street_address`, `landmark?`, `city`, `pincode`, `latitude?`, `longitude?`, `is_default`
- Delivery slot is decided by the server (`/delivery-slot`): before 12 PM → 2-hour express, after → next morning

Adding a translation: add the key to **both** `l10n/en.dart` and `l10n/gu.dart`
and use `context.tr('key')`. Placeholders use `{name}` syntax.

## Postman

Import `testing_pro_api_collection.json` from the project root. (Some request
bodies in the collection are older than the controllers – the app follows the
controllers.)

### In-app web pages (no duplicate header)
The WebView sends a user agent containing `FreshExpressApp`. The Laravel storefront layout detects it (or `?in_app=1`)
and hides its own header, footer and bottom tab bar, so CMS pages, invoices and tracking look native inside the app.
