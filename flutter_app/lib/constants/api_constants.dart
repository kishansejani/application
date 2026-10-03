/// Central place for every backend URL used by the app.
///
/// Base URL:
///  * Android emulator  -> http://10.0.2.2:8000/api   (default)
///  * iOS simulator     -> http://localhost:8000/api
///  * Physical device   -> http://<YOUR_PC_LAN_IP>:8000/api (e.g. http://192.168.1.100:8000/api)
///
/// You can also override it without editing code:
///   flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api
class ApiConstants {
  ApiConstants._();

  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Website root (API base without the trailing `/api`). Used for WebView pages
  /// and for resolving relative image paths.
  static final String webBaseUrl = baseUrl.replaceFirst(RegExp(r'/api/?$'), '');

  static final String imageBaseUrl = '$webBaseUrl/storage';

  // Auth endpoints
  static const String sendOtp = '$baseUrl/auth/otp/send';
  static const String verifyOtp = '$baseUrl/auth/otp/verify';
  static const String resendOtp = '$baseUrl/auth/otp/resend';
  static const String register = '$baseUrl/auth/register';
  static const String userProfile = '$baseUrl/auth/profile';
  static const String updateProfile = '$baseUrl/auth/profile';
  static const String logout = '$baseUrl/auth/logout';

  // Home & Catalog
  static const String home = '$baseUrl/home';
  static const String settings = '$baseUrl/settings';
  static const String deliverySlot = '$baseUrl/delivery-slot';
  static const String categories = '$baseUrl/categories';
  static const String products = '$baseUrl/products';
  static const String offers = '$baseUrl/offers';
  static const String pages = '$baseUrl/pages';

  static String productDetail(int id) => '$products/$id';
  static String categoryDetail(String slug) => '$categories/$slug';

  // Cart & Wishlist
  static const String cart = '$baseUrl/cart';
  static const String cartAdd = '$baseUrl/cart/add';
  static const String cartUpdate = '$baseUrl/cart/update';
  static String cartRemove(int productId) => '$cart/$productId';
  static const String wishlist = '$baseUrl/wishlist';
  static const String wishlistToggle = '$baseUrl/wishlist/toggle';

  // Addresses, Checkout & Orders
  static const String addresses = '$baseUrl/addresses';
  static String address(int id) => '$addresses/$id';
  static String addressDefault(int id) => '$addresses/$id/default';
  static const String validateCoupon = '$baseUrl/checkout/coupon/apply';
  static const String placeOrder = '$baseUrl/checkout/place-order';
  static const String orders = '$baseUrl/orders';
  static String orderDetail(String orderNumber) => '$orders/${Uri.encodeComponent(orderNumber)}';

  // ---------------------------------------------------------------------------
  // Website (WebView) URLs - these web routes do not require a web session.
  // ---------------------------------------------------------------------------
  static String get websiteUrl => webBaseUrl;

  /// Appends `lang=gu|en` (and keeps any existing query) so the website renders
  /// in the same language as the app. Existing `lang` values are replaced.
  static String withLang(String url, String languageCode) {
    final uri = Uri.tryParse(url);
    if (uri == null || !(uri.scheme == 'http' || uri.scheme == 'https')) return url;
    final params = Map<String, String>.from(uri.queryParameters);
    params['lang'] = languageCode == 'gu' ? 'gu' : 'en';
    return uri.replace(queryParameters: params).toString();
  }

  static String pageUrl(String slug) => '$webBaseUrl/page/$slug';
  static String invoiceUrl(String orderNumber) =>
      '$webBaseUrl/order/invoice/${Uri.encodeComponent(orderNumber)}';
  static String trackUrl(String orderNumber) =>
      '$webBaseUrl/order/track?order_number=${Uri.encodeQueryComponent(orderNumber)}';
  static String productWebUrl(String slug) => '$webBaseUrl/product/$slug';

  // CMS page slugs (seeded by the backend)
  static const String slugAbout = 'about-us';
  static const String slugPrivacy = 'privacy-policy';
  static const String slugLegal = 'legal-information';
}
