# Fresh Express v2.0 – UI/UX redesign notes

## Admin panel
- **New layout** (`resources/views/admin/layouts/admin.blade.php`, `public/assets/admin/admin.css`, `public/assets/admin/admin.js`)
  - Sidebar: grouped menu (Overview, Sales, Catalog, Storefront, Administration), live badges (pending orders, low/out-of-stock),
    menu filter, collapsible groups (remembered), icon-only mini mode that expands on hover, mobile off-canvas drawer, user card with sign-out.
  - Top bar: breadcrumbs, command palette (**Ctrl + K**), light/dark/system theme, **full-screen toggle** (Ctrl + Shift + F),
    language switch, notifications (pending orders + stock alerts), profile menu.
  - Toast notifications, styled confirm dialogs, double-submit protection, page progress bar, back-to-top, keyboard shortcuts (**?**),
    branded error pages (403/404/419/500/503).
  - Icons: Phosphor Icons. Font Awesome is still loaded only because category icons are stored as FA classes in the database.
- **Data tables everywhere** (orders, products, stock, categories, sub categories, sliders, offers, pages, users, roles) with a toolbar:
  **Excel**, **PDF** (dialog: **Portrait / Landscape**, paper size A4/A3/Letter/Legal, title), **Print**, CSV, Copy, show/hide columns,
  expand-table full screen. Exports respect the current search/filter. Excel keeps Gujarati text and real numbers;
  PDF uses Latin text only (pdfmake fonts can't shape Gujarati) – use Excel or Print for Gujarati.
- **Invoices**: printable A4 GST invoice with a Download PDF button and portrait/landscape toggle.
- **Dashboard**: KPIs, week-over-week revenue, sales trend, order pipeline, recent orders, low-stock alerts, best sellers, quick actions.
- Every module page and form redesigned (cards, sticky save bar, live previews, inline validation messages, dark mode, responsive).

## Fixes made along the way
- **Super Admin could not open the admin panel (403)** – `AdminMiddleware` only allowed role `admin`. Now any active admin/staff role works; deactivated users are signed out.
- **Role permissions are now enforced** on routes (`permission:` middleware) and the sidebar only shows what the user may access.
  Store Admin keeps catalog/orders/offers/pages; Users, Roles and Settings are Super-Admin only (as described in the project guide).
  Custom roles with permissions can now sign in.
- Dashboard customer count (customers are stored with role `customer`), Users page customer stat.
- Settings: new **Store / panel name** field; footer and sidebar-text fields no longer reset on save.
- Forms: un-ticking "Active" on create now really saves inactive; sliders keep their link target; page meta descriptions no longer wiped.
- Storefront: icons, `primary` colours and dark mode were broken; profile update (sent `locale` instead of `language`), address delete,
  offers page fields, CMS page title/content, cart buttons, coupon removal, and HTML content on CMS pages.

## Storefront
- New header/search/category bar, mobile bottom tab bar + off-canvas menu, cart drawer with free-delivery meter, product cards with qty stepper,
  recently viewed, sticky mobile add-to-cart, order status timeline, light/dark/system theme, new admin login and password-reset pages.
- Pages opened from the mobile app (user agent contains `FreshExpressApp`, or `?in_app=1`) hide the site header/footer/tab bar.

## Flutter app (`flutter_app/`)
- Full Material 3 redesign (light/dark), responsive (NavigationRail on tablets), reusable widgets, WebView screen
  (`lib/screens/webview/app_webview_screen.dart`) for CMS pages, invoices, order tracking and the website.
- Many API contract mismatches fixed (see `flutter_app/README.md`). Requires **Flutter 3.24+**.
- First run: `flutter_app\setup_platforms.bat`, then apply `flutter_app/platform_setup/README.md`, then `flutter analyze`.
  The app could not be compiled in the build environment (no Flutter SDK there), so run `flutter analyze` and fix anything it reports.
- `lib/widgets/custom_button.dart` and `lib/screens/pages/static_page_screen.dart` are now empty re-export stubs – safe to delete.

## After pulling these files
```
php artisan view:clear
php artisan route:clear
php artisan storage:link   # if uploaded images return 403/404
```
No database migrations are required.

---

# v2.1 update

## What changed
- **Themed dropdowns everywhere** – `public/assets/shared/fx-select.(js|css)` upgrades every `<select>` (admin + storefront) into a styled, searchable (8+ options), keyboard-friendly dropdown. The native select stays in the form, so validation, `onchange`, jQuery `.val()` all keep working. Opt out with `<select data-native>`.
- **Premium KPI cards** (admin): gradient icon tiles, tinted glow, animated accent bar, clear selected state.
- **Premium icons**: one neutral colour at rest, accent colour on hover with smooth micro-animations (sidebar, top bar, row actions, table toolbar, buttons, card titles; storefront header/menus/footer).
- **Gujarati works everywhere** – root cause: the locale middleware let the browser's `Accept-Language: en-US` header override the session, so switching did nothing. Fixed in `app/Http/Middleware/SetLocale.php`. Admin panel is now translated via `lang/gu.json` (~900 strings); storefront strings completed in `lang/gu/messages.php`.
- **Storefront header/nav**: "Products" removed, categories that don't fit go into **More**, mega-menu for All Categories, Offers & Deals, Track Order, My Orders, About, Contact & Help, account dropdown with Register.
- **Real OTP SMS** – `app/Services/OtpService.php` + `app/Services/Sms/*` (drivers: log, Fast2SMS, 2Factor, MSG91, Twilio). Codes are random, hashed, expire in 5 min, max 5 attempts, 30 s resend cooldown, hourly cap. Demo code 1234 only works while `SMS_DRIVER=log`.
- **Registration / Sign up** – `/register` (web) and `POST /api/auth/register` (app); login with an unknown number sends you to sign-up with the number filled in.
- **Forgot password by e-mail** – branded e-mail with code + one-click reset link (`app/Mail/PasswordResetCodeMail.php`), or SMS when a phone number is entered. `php artisan mail:test you@example.com` checks your mail settings.
- **Animated OTP screens** (web + Flutter): pop-in boxes, shake on wrong code, success tick, countdown ring, paste/auto-submit.
- Flutter: Sign-up screen, animated OTP, themed dropdowns (`lib/widgets/app_dropdown.dart`), instant language switching (API + WebView follow the app language).

## Required steps on your machine
```
php artisan migrate          # creates the otp_codes table
php artisan config:clear
php artisan view:clear
```
### To receive real SMS
Set in `.env` (see `.env.example` for every key), e.g. Fast2SMS:
```
SMS_DRIVER=fast2sms
FAST2SMS_API_KEY=your_key
FAST2SMS_ROUTE=otp
```
### To send real e-mails (Gmail example)
Enable 2-Step Verification on the Google account and create an **App Password**, then:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_FROM_NAME="Fresh Express"
```
then `php artisan config:clear && php artisan mail:test you@gmail.com`.
