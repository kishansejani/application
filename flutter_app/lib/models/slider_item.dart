import 'json_utils.dart';

class SliderItem {
  final int id;
  final String title;
  final String? subtitle;
  final String? badge;
  final String image;

  /// none | category | subcategory | product | offer | custom
  final String linkType;
  final int? targetId;
  final String? linkUrl;

  SliderItem({
    required this.id,
    required this.title,
    this.subtitle,
    this.badge,
    required this.image,
    this.linkType = 'none',
    this.targetId,
    this.linkUrl,
  });

  factory SliderItem.fromJson(Map<String, dynamic> json) {
    return SliderItem(
      id: asInt(json['id']),
      title: asString(json['title']),
      subtitle: asStringOrNull(json['subtitle']),
      badge: asStringOrNull(json['badge']),
      image: asString(json['image_url'] ?? json['image']),
      linkType: asString(json['link_type'], 'none'),
      targetId: asIntOrNull(json['target_id']),
      linkUrl: asStringOrNull(json['link_url']),
    );
  }
}
