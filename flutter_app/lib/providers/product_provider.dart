import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';
import '../models/category.dart';
import '../models/json_utils.dart';
import '../models/offer.dart';
import '../models/product.dart';
import '../models/slider_item.dart';
import '../services/api_service.dart';

class ProductProvider extends ChangeNotifier {
  static const String _recentSearchKey = 'recent_searches';

  List<SliderItem> _sliders = [];
  List<Category> _categories = [];
  List<Product> _featuredProducts = [];
  List<Product> _newProducts = [];
  List<Offer> _offers = [];
  Map<String, dynamic>? _deliverySlotInfo;
  bool _isLoadingHome = false;
  bool _homeLoaded = false;
  Object? _homeError;
  bool _isLoadingCategories = false;
  Object? _categoriesError;
  List<String> _recentSearches = [];

  ProductProvider() {
    _loadRecentSearches();
  }

  List<SliderItem> get sliders => _sliders;
  List<Category> get categories => _categories;
  List<Product> get featuredProducts => _featuredProducts;
  List<Product> get newProducts => _newProducts;
  List<Offer> get offers => _offers;

  /// `{type: two_hours|next_day, slot: "...", promise_text: "..."}`
  Map<String, dynamic>? get deliverySlotInfo => _deliverySlotInfo;
  bool get isLoadingHome => _isLoadingHome;
  bool get homeLoaded => _homeLoaded;
  Object? get homeError => _homeError;
  bool get isLoadingCategories => _isLoadingCategories;
  Object? get categoriesError => _categoriesError;
  List<String> get recentSearches => _recentSearches;

  Category? categoryById(int id) {
    for (final c in _categories) {
      if (c.id == id) return c;
    }
    return null;
  }

  Future<void> fetchHomeData() async {
    _isLoadingHome = true;
    notifyListeners();
    try {
      final results = await Future.wait<dynamic>([
        ApiService.get(ApiConstants.home),
        // "Fresh arrivals" rail - the home endpoint has no new products list.
        fetchProductPage(sort: 'newest').then<dynamic>((p) => p).catchError((Object _) => null),
      ]);
      final data = asMap(ApiService.dataOf(results[0]));

      _sliders = mapList(data['sliders'], (e) => SliderItem.fromJson(e));
      _categories = mapList(data['categories'], (e) => Category.fromJson(e));
      _featuredProducts = mapList(data['featured_products'], (e) => Product.fromJson(e));
      _offers = mapList(data['offers'], (e) => Offer.fromJson(e));
      final banner = data['delivery_banner'] ?? data['delivery_slot'];
      if (banner is Map) _deliverySlotInfo = asMap(banner);

      final newPage = results[1];
      if (newPage is ProductPage) {
        _newProducts = newPage.items;
      } else {
        _newProducts = mapList(data['new_products'], (e) => Product.fromJson(e));
      }

      _homeError = null;
      _homeLoaded = true;
    } catch (e) {
      _homeError = e;
    } finally {
      _isLoadingHome = false;
      notifyListeners();
    }
  }

  Future<void> fetchCategories() async {
    _isLoadingCategories = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.categories);
      _categories = mapList(ApiService.dataOf(res), (e) => Category.fromJson(e));
      _categoriesError = null;
    } catch (e) {
      _categoriesError = e;
    } finally {
      _isLoadingCategories = false;
      notifyListeners();
    }
  }

  /// `GET /products` - supports category / sub-category filters, `q` search,
  /// `sort` (featured | newest | price_asc | price_desc) and pagination.
  Future<ProductPage> fetchProductPage({
    int? categoryId,
    int? subCategoryId,
    String? search,
    String? sort,
    int page = 1,
  }) async {
    final params = <String, String>{'page': '$page'};
    if (categoryId != null) params['category_id'] = '$categoryId';
    if (subCategoryId != null) params['sub_category_id'] = '$subCategoryId';
    if (search != null && search.trim().isNotEmpty) params['q'] = search.trim();
    if (sort != null && sort.isNotEmpty) params['sort'] = sort;

    final uri = Uri.parse(ApiConstants.products).replace(queryParameters: params);
    final res = await ApiService.get(uri.toString());
    final items = mapList(ApiService.dataOf(res), (e) => Product.fromJson(e));
    return ProductPage(
      items: items,
      currentPage: res is Map ? asInt(res['current_page'], page) : page,
      lastPage: res is Map ? asInt(res['last_page'], page) : page,
      total: res is Map ? asInt(res['total'], items.length) : items.length,
    );
  }

  Future<List<Product>> fetchProducts({
    int? categoryId,
    int? subCategoryId,
    String? search,
    String? sort,
  }) async {
    final page = await fetchProductPage(
      categoryId: categoryId,
      subCategoryId: subCategoryId,
      search: search,
      sort: sort,
    );
    return page.items;
  }

  /// Full product detail with gallery, description and related products.
  Future<Product> fetchProductDetail(int id) async {
    final res = await ApiService.get(ApiConstants.productDetail(id));
    return Product.fromJson(asMap(ApiService.dataOf(res)));
  }

  Future<List<Offer>> fetchOffers() async {
    final res = await ApiService.get(ApiConstants.offers);
    final list = mapList(ApiService.dataOf(res), (e) => Offer.fromJson(e));
    _offers = list;
    notifyListeners();
    return list;
  }

  /// `GET /delivery-slot` -> {type, slot_text, cutoff_time, rule_description}
  Future<Map<String, dynamic>> fetchDeliverySlot() async {
    final res = await ApiService.get(ApiConstants.deliverySlot);
    return asMap(ApiService.dataOf(res));
  }

  // ---------------------------------------------------------------------------
  // Recent searches (local only)
  // ---------------------------------------------------------------------------
  Future<void> _loadRecentSearches() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _recentSearches = prefs.getStringList(_recentSearchKey) ?? [];
      notifyListeners();
    } catch (_) {}
  }

  Future<void> addRecentSearch(String query) async {
    final q = query.trim();
    if (q.length < 2) return;
    _recentSearches = [q, ..._recentSearches.where((s) => s.toLowerCase() != q.toLowerCase())].take(8).toList();
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(_recentSearchKey, _recentSearches);
  }

  Future<void> clearRecentSearches() async {
    _recentSearches = [];
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_recentSearchKey);
  }
}
