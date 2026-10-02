import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/cart_item.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class CartProvider extends ChangeNotifier {
  List<CartItem> _items = [];
  bool _isLoading = false;
  String? _couponCode;
  double _discountAmount = 0.0;
  double _deliveryCharge = 0.0;

  List<CartItem> get items => _items;
  bool get isLoading => _isLoading;
  String? get couponCode => _couponCode;
  double get discountAmount => _discountAmount;
  double get deliveryCharge => _deliveryCharge;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);

  double get grandTotal {
    double total = subtotal - _discountAmount + _deliveryCharge;
    return total > 0 ? total : 0.0;
  }

  bool isProductInCart(int productId) {
    return _items.any((item) => item.productId == productId);
  }

  int getProductQuantity(int productId) {
    final item = _items.firstWhere(
      (item) => item.productId == productId,
      orElse: () => CartItem(id: 0, productId: 0, quantity: 0, unitPrice: 0),
    );
    return item.quantity;
  }

  Future<void> fetchCart() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.cart, requireAuth: true);
      if (res['items'] != null) {
        var list = res['items'] as List;
        _items = list.map((i) => CartItem.fromJson(i)).toList();
      }
      _deliveryCharge = res['delivery_charge'] != null ? double.tryParse(res['delivery_charge'].toString()) ?? 0.0 : (subtotal >= 499 ? 0.0 : 40.0);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> addToCart(Product product, {int quantity = 1}) async {
    try {
      final res = await ApiService.post(
        ApiConstants.cartAdd,
        {'product_id': product.id, 'quantity': quantity},
        requireAuth: true,
      );
      if (res['items'] != null) {
        var list = res['items'] as List;
        _items = list.map((i) => CartItem.fromJson(i)).toList();
      } else {
        await fetchCart();
      }
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateQuantity(int cartItemId, int quantity) async {
    try {
      if (quantity <= 0) {
        await removeItem(cartItemId);
        return;
      }
      final res = await ApiService.put(
        '${ApiConstants.cart}/$cartItemId',
        {'quantity': quantity},
        requireAuth: true,
      );
      if (res['items'] != null) {
        var list = res['items'] as List;
        _items = list.map((i) => CartItem.fromJson(i)).toList();
      } else {
        await fetchCart();
      }
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> removeItem(int cartItemId) async {
    try {
      await ApiService.delete('${ApiConstants.cart}/$cartItemId', requireAuth: true);
      _items.removeWhere((item) => item.id == cartItemId);
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<bool> applyCoupon(String code) async {
    try {
      final res = await ApiService.post(
        ApiConstants.validateCoupon,
        {'code': code, 'subtotal': subtotal},
        requireAuth: true,
      );

      if (res['valid'] == true) {
        _couponCode = code;
        _discountAmount = double.tryParse(res['discount'].toString()) ?? 0.0;
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      rethrow;
    }
  }

  void removeCoupon() {
    _couponCode = null;
    _discountAmount = 0.0;
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    _couponCode = null;
    _discountAmount = 0.0;
    notifyListeners();
  }
}
