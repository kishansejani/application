import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/json_utils.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class WishlistProvider extends ChangeNotifier {
  List<Product> _items = [];
  bool _isLoading = false;
  bool _hasLoaded = false;
  Object? _error;
  final Set<int> _busy = {};

  List<Product> get items => _items;
  bool get isLoading => _isLoading;
  bool get hasLoaded => _hasLoaded;
  Object? get error => _error;
  int get count => _items.length;

  bool isFavorite(int productId) => _items.any((p) => p.id == productId);
  bool isBusy(int productId) => _busy.contains(productId);

  Future<void> fetchWishlist() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.wishlist, requireAuth: true);
      final data = ApiService.dataOf(res);
      _items = mapList(data, (e) => Product.fromJson(e['product'] is Map ? asMap(e['product']) : e));
      _error = null;
      _hasLoaded = true;
    } catch (e) {
      _error = e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Optimistic toggle. Returns the new state (true = in wishlist).
  Future<bool> toggleWishlist(Product product) async {
    if (_busy.contains(product.id)) return isFavorite(product.id);
    final wasFavorite = isFavorite(product.id);
    _busy.add(product.id);
    if (wasFavorite) {
      _items.removeWhere((p) => p.id == product.id);
    } else {
      _items.insert(0, product);
    }
    notifyListeners();

    try {
      final res = await ApiService.post(
        ApiConstants.wishlistToggle,
        {'product_id': product.id},
        requireAuth: true,
      );
      final nowFavorite = res is Map && res.containsKey('is_in_wishlist')
          ? asBool(res['is_in_wishlist'])
          : (res is Map ? asBool(res['attached'], !wasFavorite) : !wasFavorite);
      _items.removeWhere((p) => p.id == product.id);
      if (nowFavorite) _items.insert(0, product);
      return nowFavorite;
    } catch (e) {
      _items.removeWhere((p) => p.id == product.id);
      if (wasFavorite) _items.insert(0, product);
      rethrow;
    } finally {
      _busy.remove(product.id);
      notifyListeners();
    }
  }

  void clear() {
    _items = [];
    _hasLoaded = false;
    _error = null;
    notifyListeners();
  }
}
