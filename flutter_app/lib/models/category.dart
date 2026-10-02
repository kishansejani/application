class Category {
  final int id;
  final String name;
  final String nameEn;
  final String nameGu;
  final String slug;
  final String? image;
  final String? icon;
  final List<SubCategory> subCategories;

  Category({
    required this.id,
    required this.name,
    required this.nameEn,
    required this.nameGu,
    required this.slug,
    this.image,
    this.icon,
    this.subCategories = const [],
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    var subCatsJson = json['sub_categories'] as List? ?? [];
    List<SubCategory> subCatsList = subCatsJson.map((i) => SubCategory.fromJson(i)).toList();

    return Category(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? json['name_en'] ?? '',
      nameEn: json['name_en'] ?? '',
      nameGu: json['name_gu'] ?? '',
      slug: json['slug'] ?? '',
      image: json['image'],
      icon: json['icon'],
      subCategories: subCatsList,
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
    required this.nameEn,
    required this.nameGu,
    required this.slug,
    this.image,
  });

  factory SubCategory.fromJson(Map<String, dynamic> json) {
    return SubCategory(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      categoryId: json['category_id'] is int ? json['category_id'] : int.tryParse(json['category_id'].toString()) ?? 0,
      name: json['name'] ?? json['name_en'] ?? '',
      nameEn: json['name_en'] ?? '',
      nameGu: json['name_gu'] ?? '',
      slug: json['slug'] ?? '',
      image: json['image'],
    );
  }
}
