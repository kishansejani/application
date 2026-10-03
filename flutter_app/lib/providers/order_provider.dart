import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/json_utils.dart';
import '../models/order.dart';
import '../services/api_service.dart';

class OrderProvider extends ChangeNotifier {
  List<Order> _orders = [];
  bool _isLoading = false;
  bool _isLoadingMore = false;
  bool _hasLoaded = false;
  Object? _error;
  int _page = 1;
  int _lastPage = 1;

  List<Order> get orders => _orders;
  bool get isLoading => _isLoading;
  bool get isLoadingMore => _isLoadingMore;
  bool get hasLoaded => _hasLoaded;
  Object? get error => _error;
  bool get hasMore => _page < _lastPage;

  Future<void> fetchOrders() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.get('${ApiConstants.orders}?page=1', requireAuth: true);
      _orders = mapList(ApiService.dataOf(res), (e) => Order.fromJson(e));
      _page = res is Map ? asInt(res['current_page'], 1) : 1;
      _lastPage = res is Map ? asInt(res['last_page'], 1) : 1;
      _error = null;
      _hasLoaded = true;
    } catch (e) {
      _error = e;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMore() async {
    if (_isLoadingMore || _isLoading || !hasMore) return;
    _isLoadingMore = true;
    notifyListeners();
    try {
      final res = await ApiService.get('${ApiConstants.orders}?page=${_page + 1}', requireAuth: true);
      _orders = [..._orders, ...mapList(ApiService.dataOf(res), (e) => Order.fromJson(e))];
      _page = res is Map ? asInt(res['current_page'], _page + 1) : _page + 1;
      _lastPage = res is Map ? asInt(res['last_page'], _lastPage) : _lastPage;
    } catch (_) {
      // Keep the list; the user can scroll again to retry.
    } finally {
      _isLoadingMore = false;
      notifyListeners();
    }
  }

  Future<Order> fetchOrder(String orderNumber) async {
    final res = await ApiService.get(ApiConstants.orderDetail(orderNumber), requireAuth: true);
    return Order.fromJson(asMap(ApiService.dataOf(res)));
  }

  /// Places the order from the server-side cart. Returns the API `data` map:
  /// order_id, order_number, invoice_number, total_amount, delivery_slot,
  /// estimated_delivery_at, delivery_type.
  Future<Map<String, dynamic>> placeOrder({
    required int addressId,
    required String paymentMethod,
    String? couponCode,
    String? notes,
  }) async {
    final res = await ApiService.post(
      ApiConstants.placeOrder,
      {
        'address_id': addressId,
        'payment_method': paymentMethod,
        if (couponCode != null && couponCode.isNotEmpty) 'coupon_code': couponCode,
        if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim(),
      },
      requireAuth: true,
    );
    final data = asMap(ApiService.dataOf(res));
    // Refresh the order list in the background.
    fetchOrders();
    return data;
  }

  void clear() {
    _orders = [];
    _hasLoaded = false;
    _error = null;
    _page = 1;
    _lastPage = 1;
    notifyListeners();
  }
}
