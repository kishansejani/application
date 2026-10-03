import 'json_utils.dart';

class Category {
  final int id;
  final String name;
  final String nameEn;
  final String nameGu;
  final String slug;
  final String? image;
  final String? icon;
  final String? description;
  final List<SubCategory> subCategories;

  Category({
    required this.id,
    required this.name,
    this.nameEn = '',
    this.nameGu = '',
    this.slug = '',
    this.image,
    this.icon,
    this.description,
    this.subCategories = const [],
  });

  /// API already localises `name` via Accept-Language; raw relations only
  /// carry name_en / name_gu, so pick the right one for the UI language.
  String nameFor(String languageCode) {
    if (languageCode == 'gu' && nameGu.isNotEmpty) return nameGu;
    if (languageCode == 'en' && nameEn.isNotEmpty) return nameEn;
    return name.isNotEmpty ? name : (nameEn.isNotEmpty ? nameEn : nameGu);
  }

  factory Category.fromJson(Map<String, dynamic> json) {
    final id = asInt(json['id']);
    return Category(
      id: id,
      name: asString(json['name'] ?? json['name_en']),
      nameEn: asString(json['name_en']),
      nameGu: asString(json['name_gu']),
      slug: asString(json['slug']),
      image: asStringOrNull(json['image_url'] ?? json['image']),
      icon: asStringOrNull(json['icon']),
      description: asStringOrNull(json['description'] ?? json['description_en']),
      subCategories: mapList(
        json['subcategories'] ?? json['sub_categories'],
        (e) => SubCategory.fromJson(e, categoryId: id),
      ),
    );
  }
}

class SubCategory {
  final int id;
  final int categoryId;
  final String name;
  final String nameEn;
  final String nameGu;
  final String slug;
  final String? image;

  SubCategory({
    required this.id,
    required this.categoryId,
    required this.name,
    this.nameEn = '',
    this.nameGu = '',
    this.slug = '',
    this.image,
  });

  String nameFor(String languageCode) {
    if (languageCode == 'gu' && nameGu.isNotEmpty) return nameGu;
    if (languageCode == 'en' && nameEn.isNotEmpty) return nameEn;
    return name.isNotEmpty ? name : (nameEn.isNotEmpty ? nameEn : nameGu);
  }

  factory SubCategory.fromJson(Map<String, dynamic> json, {int? categoryId}) {
    return SubCategory(
      id: asInt(json['id']),
      categoryId: asInt(json['category_id'] ?? categoryId),
      name: asString(json['name'] ?? json['name_en']),
      nameEn: asString(json['name_en']),
      nameGu: asString(json['name_gu']),
      slug: asString(json['slug']),
      image: asStringOrNull(json['image_url'] ?? json['image']),
    );
  }
}
