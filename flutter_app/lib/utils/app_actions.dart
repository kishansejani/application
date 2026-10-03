import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../constants/api_constants.dart';
import '../l10n/app_localizations.dart';
import '../models/product.dart';
import '../providers/address_provider.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../providers/locale_provider.dart';
import '../providers/order_provider.dart';
import '../providers/product_provider.dart';
import '../providers/wishlist_provider.dart';
import '../screens/webview/app_webview_screen.dart';
import 'ui_helpers.dart';

/// Shared user actions so every screen behaves the same way
/// (login gate, haptics, snackbars, error handling).
class AppActions {
  AppActions._();

  static Future<void> addToCart(BuildContext context, Product product, {int quantity = 1, bool showFeedback = false}) async {
    if (!product.isInStock) {
      showAppSnack(context, context.tr('out_of_stock'), type: SnackType.error);
      return;
    }
    if (!await ensureLoggedIn(context)) return;
    if (!context.mounted) return;
    HapticFeedback.lightImpact();
    try {
      await context.read<CartProvider>().addToCart(product, quantity: quantity);
      if (showFeedback && context.mounted) {
        showAppSnack(context, context.tr('added_to_cart'), type: SnackType.success);
      }
    } catch (e) {
      if (context.mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  static Future<void> setQuantity(BuildContext context, int productId, int quantity) async {
    HapticFeedback.selectionClick();
    try {
      await context.read<CartProvider>().setQuantity(productId, quantity);
    } catch (e) {
      if (context.mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  static Future<void> toggleWishlist(BuildContext context, Product product) async {
    if (!await ensureLoggedIn(context)) return;
    if (!context.mounted) return;
    HapticFeedback.lightImpact();
    try {
      final added = await context.read<WishlistProvider>().toggleWishlist(product);
      if (context.mounted) {
        showAppSnack(
          context,
          context.tr(added ? 'added_to_wishlist' : 'removed_from_wishlist'),
          type: added ? SnackType.success : SnackType.info,
        );
      }
    } catch (e) {
      if (context.mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  /// Switches UI language and reloads server-localised data.
  static Future<void> changeLanguage(BuildContext context, String languageCode) async {
    final localeProvider = context.read<LocaleProvider>();
    final products = context.read<ProductProvider>();
    final auth = context.read<AuthProvider>();
    final cart = context.read<CartProvider>();
    final wishlist = context.read<WishlistProvider>();
    final orders = context.read<OrderProvider>();
    final previous = localeProvider.locale.languageCode;
    // Updates MaterialApp.locale (whole UI) and ApiService's Accept-Language at once.
    await localeProvider.setLocale(Locale(languageCode));
    if (previous == languageCode) return;
    products.fetchHomeData();
    if (auth.isAuthenticated) {
      cart.fetchCart(silent: true);
      wishlist.fetchWishlist();
      orders.fetchOrders();
      // Remember the language on the account too (SMS / e-mails); best effort.
      final user = auth.user;
      if (user != null && user.preferredLanguage != languageCode) {
        auth
            .updateProfile(name: user.name, email: user.email, language: languageCode)
            .catchError((Object _) {});
      }
    }
  }

  /// Loads user specific data after a successful login.
  static void loadUserData(BuildContext context) {
    context.read<CartProvider>().fetchCart();
    context.read<WishlistProvider>().fetchWishlist();
    context.read<AddressProvider>().fetchAddresses();
    context.read<OrderProvider>().fetchOrders();
  }

  static Future<void> logout(BuildContext context) async {
    final confirmed = await confirmDialog(
      context,
      title: context.tr('logout'),
      message: context.tr('logout_confirm'),
      confirmLabel: context.tr('logout'),
      destructive: true,
      icon: Icons.logout_rounded,
    );
    if (!confirmed || !context.mounted) return;
    final cart = context.read<CartProvider>();
    final wishlist = context.read<WishlistProvider>();
    final addresses = context.read<AddressProvider>();
    final orders = context.read<OrderProvider>();
    await context.read<AuthProvider>().logout();
    cart.clearCart();
    wishlist.clear();
    addresses.clear();
    orders.clear();
    if (context.mounted) showAppSnack(context, context.tr('logged_out'), type: SnackType.success);
  }

  // ---------------------------------------------------------------------------
  // WebView pages
  // ---------------------------------------------------------------------------
  static Future<void> openCmsPage(BuildContext context, String slug, String title) {
    return AppWebViewScreen.open(context, url: ApiConstants.pageUrl(slug), title: title);
  }

  static Future<void> openWebsite(BuildContext context) {
    return AppWebViewScreen.open(context, url: ApiConstants.websiteUrl, title: context.tr('our_website'));
  }

  static Future<void> openInvoice(BuildContext context, String orderNumber) {
    return AppWebViewScreen.open(
      context,
      url: ApiConstants.invoiceUrl(orderNumber),
      title: context.tr('invoice'),
    );
  }

  static Future<void> openWebTracking(BuildContext context, String orderNumber) {
    return AppWebViewScreen.open(
      context,
      url: ApiConstants.trackUrl(orderNumber),
      title: context.tr('track_order'),
    );
  }

  /// Asks for an order number and opens the public web tracking page.
  static Future<void> promptTrackOrder(BuildContext context) async {
    final controller = TextEditingController();
    final orderNumber = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        icon: const Icon(Icons.local_shipping_rounded),
        title: Text(ctx.tr('track_order')),
        content: TextField(
          controller: controller,
          autofocus: true,
          textCapitalization: TextCapitalization.characters,
          decoration: InputDecoration(
            hintText: 'ORD-XXXXXXXX',
            labelText: ctx.tr('order_number'),
            prefixIcon: const Icon(Icons.tag_rounded),
          ),
          onSubmitted: (v) => Navigator.of(ctx).pop(v.trim()),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: Text(ctx.tr('cancel'))),
          FilledButton(
            style: FilledButton.styleFrom(minimumSize: const Size(88, 44)),
            onPressed: () => Navigator.of(ctx).pop(controller.text.trim()),
            child: Text(ctx.tr('track')),
          ),
        ],
      ),
    );
    // Controller is not disposed here: the dialog's exit animation still uses it.
    if (orderNumber == null || orderNumber.isEmpty || !context.mounted) return;
    await openWebTracking(context, orderNumber);
  }

  static Future<void> copyToClipboard(BuildContext context, String text, {String? message}) async {
    await Clipboard.setData(ClipboardData(text: text));
    HapticFeedback.selectionClick();
    if (context.mounted) {
      showAppSnack(context, message ?? context.tr('copied_to_clipboard'), type: SnackType.success);
    }
  }
}
