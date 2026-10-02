class SliderItem {
  final int id;
  final String title;
  final String? subtitle;
  final String image;
  final String? linkUrl;

  SliderItem({
    required this.id,
    required this.title,
    this.subtitle,
    required this.image,
    this.linkUrl,
  });

  factory SliderItem.fromJson(Map<String, dynamic> json) {
    return SliderItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? '',
      subtitle: json['subtitle'],
      image: json['image'] ?? '',
      linkUrl: json['link_url'],
    );
  }
}

class Offer {
  final int id;
  final String title;
  final String code;
  final String type; // percentage, fixed
  final double discountValue;
  final double minOrderAmount;
  final String? description;
  final String? expiresAt;

  Offer({
    required this.id,
    required this.title,
    required this.code,
    required this.type,
    required this.discountValue,
    required this.minOrderAmount,
    this.description,
    this.expiresAt,
  });

  factory Offer.fromJson(Map<String, dynamic> json) {
    return Offer(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? '',
      code: json['code'] ?? '',
      type: json['type'] ?? 'percentage',
      discountValue: json['discount_value'] != null ? double.tryParse(json['discount_value'].toString()) ?? 0.0 : 0.0,
      minOrderAmount: json['min_order_amount'] != null ? double.tryParse(json['min_order_amount'].toString()) ?? 0.0 : 0.0,
      description: json['description'],
      expiresAt: json['expires_at'],
    );
  }
}

class PageContent {
  final int id;
  final String title;
  final String slug;
  final String content;

  PageContent({
    required this.id,
    required this.title,
    required this.slug,
    required this.content,
  });

  factory PageContent.fromJson(Map<String, dynamic> json) {
    return PageContent(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? '',
      slug: json['slug'] ?? '',
      content: json['content'] ?? '',
    );
  }
}
