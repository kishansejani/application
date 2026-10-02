class ApiConstants {
  // Use 10.0.2.2 for Android Emulator, localhost for iOS simulator/web, or your LAN IP for physical device
  static const String baseUrl = 'http://10.0.2.2:8000/api';
  static const String imageBaseUrl = 'http://10.0.2.2:8000/storage';

  // Auth endpoints
  static const String sendOtp = '$baseUrl/auth/otp/send';
  static const String verifyOtp = '$baseUrl/auth/otp/verify';
  static const String userProfile = '$baseUrl/auth/profile';
  static const String updateProfile = '$baseUrl/auth/profile';
  static const String logout = '$baseUrl/auth/logout';

  // Home & Catalog
  static const String home = '$baseUrl/home';
  static const String deliverySlot = '$baseUrl/delivery-slot';
  static const String sliders = '$baseUrl/sliders';
  static const String categories = '$baseUrl/categories';
  static const String products = '$baseUrl/products';
  static const String offers = '$baseUrl/offers';
  static const String validateCoupon = '$baseUrl/checkout/coupon/apply';
  static const String pages = '$baseUrl/pages';

  // Cart & Wishlist
  static const String cart = '$baseUrl/cart';
  static const String cartAdd = '$baseUrl/cart/add';
  static const String cartUpdate = '$baseUrl/cart/update';
  static const String wishlist = '$baseUrl/wishlist';
  static const String wishlistToggle = '$baseUrl/wishlist/toggle';

  // Addresses & Orders
  static const String addresses = '$baseUrl/addresses';
  static const String orders = '$baseUrl/orders';
  static const String placeOrder = '$baseUrl/checkout/place-order';
}
