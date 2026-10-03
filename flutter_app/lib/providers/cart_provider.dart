import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/cart_item.dart';
import '../models/json_utils.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class CartProvider extends ChangeNotifier {
  static const double _defaultDeliveryCharge = 40;

  List<CartItem> _items = [];
  bool _isLoading = false;
  bool _hasLoaded = false;
  Object? _error;
  String? _couponCode;
  double _discountAmount = 0.0;
  double _deliveryCharge = 0.0;
  double _freeDeliveryThreshold = 499;
  String? _deliverySlotText;
  String? _deliveryType;
  final Set<int> _busyProducts = {};

  List<CartItem> get items => _items;
  bool get isLoading => _isLoading;
  bool get hasLoaded => _hasLoaded;
  Object? get error => _error;
  String? get couponCode => _couponCode;
  double get discountAmount => _discountAmount;
  double get deliveryCharge => _items.isEmpty ? 0 : _deliveryCharge;
  double get freeDeliveryThreshold => _freeDeliveryThreshold;
  String? get deliverySlotText => _deliverySlotText;
  String? get deliveryType => _deliveryType;
  bool get isEmpty => _items.isEmpty;

  bool isBusy(int productId) => _busyProducts.contains(productId);

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);

  double get totalSavings => _items.fold(0.0, (sum, item) => sum + item.savings) + _discountAmount;

  double get amountForFreeDelivery {
    final remaining = _freeDeliveryThreshold - subtotal;
    return remaining > 0 ? remaining : 0;
  }

  double get freeDeliveryProgress {
    if (_freeDeliveryThreshold <= 0) return 1;
    final p = subtotal / _freeDeliveryThreshold;
    return p > 1 ? 1 : p;
  }

  double get grandTotal {
    final total = subtotal - _discountAmount + deliveryCharge;
    return total > 0 ? total : 0.0;
  }

  bool isProductInCart(int productId) => _items.any((item) => item.productId == productId);

  CartItem? itemFor(int productId) {
    for (final item in _items) {
      if (item.productId == productId) return item;
    }
    return null;
  }

  int getProductQuantity(int productId) => itemFor(productId)?.quantity ?? 0;

  double _localDeliveryCharge() {
    if (_items.isEmpty) return 0;
    return subtotal >= _freeDeliveryThreshold ? 0 : _defaultDeliveryCharge;
  }

  Future<void> fetchCart({bool silent = false}) async {
    if (!silent) {
      _isLoading = true;
      notifyListeners();
    }
    try {
      final res = await ApiService.get(ApiConstants.cart, requireAuth: true);
      final data = ApiService.dataOf(res);
      if (data is Map) {
        _items = mapList(data['items'], (e) => CartItem.fromJson(e));
        _freeDeliveryThreshold = asDouble(data['free_delivery_threshold'], _freeDeliveryThreshold);
        _deliveryCharge = asDouble(data['delivery_charge'], _localDeliveryCharge());
        _deliverySlotText = asStringOrNull(data['delivery_slot']);
        _deliveryType = asStringOrNull(data['delivery_type']);
      }
      _error = null;
      _hasLoaded = true;
    } catch (e) {
      _error = e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    if (_couponCode != null) {
      await _revalidateCoupon();
    }
  }

  Future<void> addToCart(Product product, {int quantity = 1}) async {
    _busyProducts.add(product.id);
    notifyListeners();
    try {
      await ApiService.post(
        ApiConstants.cartAdd,
        {'product_id': product.id, 'quantity': quantity},
        requireAuth: true,
      );
      await fetchCart(silent: true);
    } finally {
      _busyProducts.remove(product.id);
      notifyListeners();
    }
  }

  /// Sets the absolute quantity of a product (0 removes it).
  Future<void> setQuantity(int productId, int quantity) async {
    if (quantity <= 0) {
      await removeItem(productId);
      return;
    }
    final item = itemFor(productId);
    if (item == null) return;
    final previous = item.quantity;
    item.quantity = quantity;
    _deliveryCharge = _localDeliveryCharge();
    _busyProducts.add(productId);
    notifyListeners();
    try {
      await ApiService.post(
        ApiConstants.cartUpdate,
        {'product_id': productId, 'quantity': quantity},
        requireAuth: true,
      );
      await fetchCart(silent: true);
    } catch (e) {
      item.quantity = previous;
      _deliveryCharge = _localDeliveryCharge();
      rethrow;
    } finally {
      _busyProducts.remove(productId);
      notifyListeners();
    }
  }

  Future<void> increment(Product product) async {
    final qty = getProductQuantity(product.id);
    if (qty == 0) {
      await addToCart(product);
    } else {
      await setQuantity(product.id, qty + 1);
    }
  }

  Future<void> decrement(int productId) async {
    final qty = getProductQuantity(productId);
    if (qty <= 0) return;
    await setQuantity(productId, qty - 1);
  }

  /// Removes a product from the cart. Returns the removed item (for "Undo").
  Future<CartItem?> removeItem(int productId) async {
    final index = _items.indexWhere((i) => i.productId == productId);
    if (index < 0) return null;
    final removed = _items.removeAt(index);
    _deliveryCharge = _localDeliveryCharge();
    notifyListeners();
    try {
      await ApiService.delete(ApiConstants.cartRemove(productId), requireAuth: true);
      await fetchCart(silent: true);
      return removed;
    } catch (e) {
      _items.insert(index <= _items.length ? index : _items.length, removed);
      _deliveryCharge = _localDeliveryCharge();
      notifyListeners();
      rethrow;
    }
  }

  Future<bool> applyCoupon(String code) async {
    final res = await ApiService.post(
      ApiConstants.validateCoupon,
      {'code': code.trim().toUpperCase(), 'subtotal': subtotal},
      requireAuth: true,
    );
    final ok = res is Map && (res['status'] == true || res['valid'] == true);
    if (!ok) return false;
    final data = asMap(ApiService.dataOf(res));
    _couponCode = asString(data['code'], code.trim().toUpperCase());
    _discountAmount = asDouble(data['discount'] ?? (res['discount']));
    _deliveryCharge = asDouble(data['delivery_charge'], _deliveryCharge);
    notifyListeners();
    return true;
  }

  Future<void> _revalidateCoupon() async {
    final code = _couponCode;
    if (code == null) return;
    if (_items.isEmpty) {
      removeCoupon();
      return;
    }
    try {
      final ok = await applyCoupon(code);
      if (!ok) removeCoupon();
    } on ApiException catch (e) {
      if (!e.isNetworkError) removeCoupon();
    } catch (_) {}
  }

  void removeCoupon() {
    _couponCode = null;
    _discountAmount = 0.0;
    notifyListeners();
  }

  /// Local reset (after an order is placed or on logout).
  void clearCart() {
    _items = [];
    _couponCode = null;
    _discountAmount = 0.0;
    _deliveryCharge = 0.0;
    _hasLoaded = false;
    _error = null;
    notifyListeners();
  }
}
