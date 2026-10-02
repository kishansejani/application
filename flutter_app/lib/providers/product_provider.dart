import 'package:flutter/material.dart';
import '../constants/api_constants.dart';
import '../models/category.dart';
import '../models/offer.dart';
import '../models/product.dart';
import '../models/slider_item.dart';
import '../services/api_service.dart';

class ProductProvider extends ChangeNotifier {
  List<SliderItem> _sliders = [];
  List<Category> _categories = [];
  List<Product> _featuredProducts = [];
  List<Product> _newProducts = [];
  List<Offer> _offers = [];
  Map<String, dynamic>? _deliverySlotInfo;
  bool _isLoadingHome = false;

  List<SliderItem> get sliders => _sliders;
  List<Category> get categories => _categories;
  List<Product> get featuredProducts => _featuredProducts;
  List<Product> get newProducts => _newProducts;
  List<Offer> get offers => _offers;
  Map<String, dynamic>? get deliverySlotInfo => _deliverySlotInfo;
  bool get isLoadingHome => _isLoadingHome;

  Future<void> fetchHomeData() async {
    _isLoadingHome = true;
    notifyListeners();
    try {
      final res = await ApiService.get(ApiConstants.home);
      
      if (res['sliders'] != null) {
        _sliders = (res['sliders'] as List).map((i) => SliderItem.fromJson(i)).toList();
      }
      if (res['categories'] != null) {
        _categories = (res['categories'] as List).map((i) => Category.fromJson(i)).toList();
      }
      if (res['featured_products'] != null) {
        _featuredProducts = (res['featured_products'] as List).map((i) => Product.fromJson(i)).toList();
      }
      if (res['new_products'] != null) {
        _newProducts = (res['new_products'] as List).map((i) => Product.fromJson(i)).toList();
      }
      if (res['offers'] != null) {
        _offers = (res['offers'] as List).map((i) => Offer.fromJson(i)).toList();
      }
      if (res['delivery_slot'] != null) {
        _deliverySlotInfo = res['delivery_slot'];
      }

      _isLoadingHome = false;
      notifyListeners();
    } catch (e) {
      _isLoadingHome = false;
      notifyListeners();
    }
  }

  Future<List<Product>> fetchProducts({
    int? categoryId,
    int? subCategoryId,
    String? search,
    String? sort,
  }) async {
    String url = '${ApiConstants.products}?';
    if (categoryId != null) url += 'category_id=$categoryId&';
    if (subCategoryId != null) url += 'sub_category_id=$subCategoryId&';
    if (search != null && search.isNotEmpty) url += 'search=${Uri.encodeComponent(search)}&';
    if (sort != null) url += 'sort=$sort&';

    final res = await ApiService.get(url);
    if (res['data'] != null) {
      return (res['data'] as List).map((i) => Product.fromJson(i)).toList();
    }
    return [];
  }
}
