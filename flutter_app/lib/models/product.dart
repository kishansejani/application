import 'category.dart';

class Product {
  final int id;
  final int categoryId;
  final int? subCategoryId;
  final String name;
  final String nameEn;
  final String nameGu;
  final String? description;
  final double price;
  final double? strikePrice;
  final String unit;
  final int stock;
  final bool isInStock;
  final bool isFeatured;
  final String? mainImage;
  final List<String> galleryImages;
  final Category? category;
  final SubCategory? subCategory;

  Product({
    required this.id,
    required this.categoryId,
    this.subCategoryId,
    required this.name,
    required this.nameEn,
    required this.nameGu,
    this.description,
    required this.price,
    this.strikePrice,
    required this.unit,
    required this.stock,
    required this.isInStock,
    this.isFeatured = false,
    this.mainImage,
    this.galleryImages = const [],
    this.category,
    this.subCategory,
  });

  int get discountPercentage {
    if (strikePrice != null && strikePrice! > price) {
      return (((strikePrice! - price) / strikePrice!) * 100).round();
    }
    return 0;
  }

  factory Product.fromJson(Map<String, dynamic> json) {
    List<String> images = [];
    if (json['images'] is List) {
      for (var img in json['images']) {
        if (img is Map && img['image_path'] != null) {
          images.add(img['image_path'].toString());
        } else if (img is String) {
          images.add(img);
        }
      }
    }

    return Product(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      categoryId: json['category_id'] is int ? json['category_id'] : int.tryParse(json['category_id'].toString()) ?? 0,
      subCategoryId: json['sub_category_id'] != null ? (json['sub_category_id'] is int ? json['sub_category_id'] : int.tryParse(json['sub_category_id'].toString())) : null,
      name: json['name'] ?? json['name_en'] ?? '',
      nameEn: json['name_en'] ?? '',
      nameGu: json['name_gu'] ?? '',
      description: json['description'],
      price: json['price'] != null ? double.tryParse(json['price'].toString()) ?? 0.0 : 0.0,
      strikePrice: json['strike_price'] != null ? double.tryParse(json['strike_price'].toString()) : null,
      unit: json['unit'] ?? 'kg',
      stock: json['stock'] is int ? json['stock'] : int.tryParse(json['stock'].toString()) ?? 0,
      isInStock: json['is_in_stock'] == true || (json['stock'] != null && int.tryParse(json['stock'].toString())! > 0),
      isFeatured: json['is_featured'] == true || json['is_featured'] == 1,
      mainImage: json['main_image'],
      galleryImages: images,
      category: json['category'] != null ? Category.fromJson(json['category']) : null,
      subCategory: json['sub_category'] != null ? SubCategory.fromJson(json['sub_category']) : null,
    );
  }
}
