import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class WishlistProvider extends ChangeNotifier {
  List<Product> _items = [];
  bool _isLoading = false;

  List<Product> get items => _items;
  bool get isLoading => _isLoading;

  bool isFavorite(int productId) {
    return _items.any((p) => p.id == productId);
  }

  Future<void> fetchWishlist() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.wishlist, requireAuth: true);
      if (res['data'] != null) {
        var list = res['data'] as List;
        _items = list.map((i) => Product.fromJson(i['product'] ?? i)).toList();
      }
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> toggleWishlist(Product product) async {
    try {
      final res = await ApiService.post(
        ApiConstants.wishlistToggle,
        {'product_id': product.id},
        requireAuth: true,
      );

      if (res['attached'] == true) {
        if (!_items.any((p) => p.id == product.id)) {
          _items.add(product);
        }
      } else {
        _items.removeWhere((p) => p.id == product.id);
      }
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }
}
