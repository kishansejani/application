import 'category.dart';
import 'json_utils.dart';

class Product {
  final int id;
  final int categoryId;
  final int? subCategoryId;
  final String name;
  final String nameEn;
  final String nameGu;
  final String slug;
  final String? shortDescription;
  final String? description;

  /// Selling (effective) price.
  final double price;

  /// MRP shown with a strike-through when higher than [price].
  final double? strikePrice;
  final String unit;
  final int stock;
  final bool isInStock;
  final bool isFeatured;
  final String? mainImage;
  final List<String> galleryImages;
  final String? categoryName;
  final String? subCategoryName;
  final Category? category;
  final SubCategory? subCategory;
  final List<Product> relatedProducts;

  Product({
    required this.id,
    this.categoryId = 0,
    this.subCategoryId,
    required this.name,
    this.nameEn = '',
    this.nameGu = '',
    this.slug = '',
    this.shortDescription,
    this.description,
    required this.price,
    this.strikePrice,
    this.unit = '',
    this.stock = 0,
    this.isInStock = true,
    this.isFeatured = false,
    this.mainImage,
    this.galleryImages = const [],
    this.categoryName,
    this.subCategoryName,
    this.category,
    this.subCategory,
    this.relatedProducts = const [],
  });

  int get discountPercentage {
    final mrp = strikePrice;
    if (mrp != null && mrp > price && mrp > 0) {
      return (((mrp - price) / mrp) * 100).round();
    }
    return 0;
  }

  bool get hasDiscount => discountPercentage > 0;

  double get savings => hasDiscount ? (strikePrice! - price) : 0;

  bool get isLowStock => isInStock && stock > 0 && stock <= 5;

  /// Main image followed by unique gallery images.
  List<String> get allImages {
    final list = <String>[];
    if (mainImage != null && mainImage!.isNotEmpty) list.add(mainImage!);
    for (final g in galleryImages) {
      if (g.isNotEmpty && !list.contains(g)) list.add(g);
    }
    return list;
  }

  String nameFor(String languageCode) {
    if (languageCode == 'gu' && nameGu.isNotEmpty) return nameGu;
    if (languageCode == 'en' && nameEn.isNotEmpty) return nameEn;
    return name.isNotEmpty ? name : (nameEn.isNotEmpty ? nameEn : nameGu);
  }

  /// Parses every product shape the API returns: home feed, `/products`
  /// (raw model), `/products/{id}` detail, related products and wishlist rows.
  factory Product.fromJson(Map<String, dynamic> json) {
    final isWishlistRow = json.containsKey('wishlist_id');

    final mrp = asDoubleOrNull(json['mrp'] ?? json['price']) ?? 0.0;
    final discountPrice = asDoubleOrNull(json['discount_price']);
    final effectiveRaw = asDoubleOrNull(json['effective_price']);
    double effective;
    if (effectiveRaw != null && effectiveRaw > 0) {
      effective = effectiveRaw;
    } else if (discountPrice != null && discountPrice > 0 && discountPrice < mrp) {
      effective = discountPrice;
    } else {
      effective = mrp;
    }
    double? strike = mrp > effective ? mrp : null;
    final legacyStrike = asDoubleOrNull(json['strike_price']);
    if (strike == null && legacyStrike != null && legacyStrike > effective) {
      strike = legacyStrike;
    }

    final gallery = <String>[];
    for (final g in asList(json['gallery'])) {
      final s = asStringOrNull(g);
      if (s != null) gallery.add(s);
    }
    for (final img in asList(json['images'])) {
      if (img is Map) {
        final s = asStringOrNull(img['image_url'] ?? img['image_path']);
        if (s != null) gallery.add(s);
      } else {
        final s = asStringOrNull(img);
        if (s != null) gallery.add(s);
      }
    }

    final stock = asInt(json['stock_quantity'] ?? json['stock'] ?? json['max_stock']);
    final inStock = json.containsKey('is_in_stock') ? asBool(json['is_in_stock']) : stock > 0;

    final category = json['category'] is Map ? Category.fromJson(asMap(json['category'])) : null;
    final subCategory = json['sub_category'] is Map ? SubCategory.fromJson(asMap(json['sub_category'])) : null;

    return Product(
      id: asInt(isWishlistRow ? json['product_id'] : (json['id'] ?? json['product_id'])),
      categoryId: asInt(json['category_id']),
      subCategoryId: asIntOrNull(json['sub_category_id']),
      name: asString(json['name'] ?? json['name_en']),
      nameEn: asString(json['name_en']),
      nameGu: asString(json['name_gu']),
      slug: asString(json['slug']),
      shortDescription: asStringOrNull(json['short_description'] ?? json['short_description_en']),
      description: asStringOrNull(json['description'] ?? json['description_en']),
      price: effective,
      strikePrice: strike,
      unit: asString(json['unit']),
      stock: stock,
      isInStock: inStock,
      isFeatured: asBool(json['is_featured']),
      mainImage: asStringOrNull(json['thumbnail_url'] ?? json['thumbnail'] ?? json['main_image'] ?? json['image']),
      galleryImages: gallery,
      categoryName: asStringOrNull(json['category_name']) ?? category?.name,
      subCategoryName: asStringOrNull(json['subcategory_name']) ?? subCategory?.name,
      category: category,
      subCategory: subCategory,
      relatedProducts: mapList(json['related_products'], (e) => Product.fromJson(e)),
    );
  }
}

/// One page of `/products` results.
class ProductPage {
  final List<Product> items;
  final int currentPage;
  final int lastPage;
  final int total;

  ProductPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  bool get hasMore => currentPage < lastPage;
}
