import 'json_utils.dart';

class Offer {
  final int id;
  final String title;
  final String code;

  /// `percentage` or `flat`.
  final String type;
  final double discountValue;
  final double minOrderAmount;
  final double? maxDiscountAmount;
  final String? description;
  final String? expiresAt;
  final String? bannerUrl;

  Offer({
    required this.id,
    required this.title,
    required this.code,
    required this.type,
    required this.discountValue,
    required this.minOrderAmount,
    this.maxDiscountAmount,
    this.description,
    this.expiresAt,
    this.bannerUrl,
  });

  bool get isPercentage => type == 'percentage' || type == 'percent';

  factory Offer.fromJson(Map<String, dynamic> json) {
    final maxDiscount = asDoubleOrNull(json['max_discount_amount']);
    return Offer(
      id: asInt(json['id']),
      title: asString(json['title']),
      code: asString(json['code']),
      type: asString(json['discount_type'] ?? json['type'], 'percentage'),
      discountValue: asDouble(json['discount_value']),
      minOrderAmount: asDouble(json['min_order_amount']),
      maxDiscountAmount: (maxDiscount != null && maxDiscount > 0) ? maxDiscount : null,
      description: asStringOrNull(json['description']),
      expiresAt: asStringOrNull(json['valid_to'] ?? json['expires_at']),
      bannerUrl: asStringOrNull(json['banner_url']),
    );
  }
}
