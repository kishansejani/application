import 'json_utils.dart';

class Address {
  final int id;
  final int userId;

  /// Backend accepts `Home`, `Work` or `Other`.
  final String type;
  final String recipientName;
  final String recipientPhone;
  final String houseNo;
  final String streetAddress;
  final String? landmark;
  final String city;
  final String state;
  final String pincode;
  final double? latitude;
  final double? longitude;
  final String? formattedAddress;
  final bool isDefault;

  Address({
    required this.id,
    this.userId = 0,
    required this.type,
    required this.recipientName,
    required this.recipientPhone,
    required this.houseNo,
    required this.streetAddress,
    this.landmark,
    required this.city,
    this.state = 'Gujarat',
    required this.pincode,
    this.latitude,
    this.longitude,
    this.formattedAddress,
    this.isDefault = false,
  });

  // Backwards compatible getters.
  String get name => recipientName;
  String get phone => recipientPhone;

  String get fullAddress {
    if (formattedAddress != null && formattedAddress!.isNotEmpty) return formattedAddress!;
    final parts = <String>[
      houseNo,
      streetAddress,
      if (landmark != null && landmark!.isNotEmpty) 'Near $landmark',
      city,
      state,
      pincode,
    ].where((p) => p.trim().isNotEmpty).toList();
    return parts.join(', ');
  }

  static String normalizeType(String type) {
    final t = type.toLowerCase();
    if (t == 'work' || t == 'office') return 'Work';
    if (t == 'other') return 'Other';
    return 'Home';
  }

  factory Address.fromJson(Map<String, dynamic> json) {
    return Address(
      id: asInt(json['id']),
      userId: asInt(json['user_id']),
      type: normalizeType(asString(json['type'], 'Home')),
      recipientName: asString(json['recipient_name'] ?? json['name']),
      recipientPhone: asString(json['recipient_phone'] ?? json['phone']),
      houseNo: asString(json['house_no'] ?? json['address_line_1']),
      streetAddress: asString(json['street_address'] ?? json['address_line_2']),
      landmark: asStringOrNull(json['landmark']),
      city: asString(json['city']),
      state: asString(json['state'], 'Gujarat'),
      pincode: asString(json['pincode']),
      latitude: asDoubleOrNull(json['latitude']),
      longitude: asDoubleOrNull(json['longitude']),
      formattedAddress: asStringOrNull(json['formatted_address']),
      isDefault: asBool(json['is_default']),
    );
  }

  /// Body for `POST /addresses` and `PUT /addresses/{id}`.
  Map<String, dynamic> toJson() {
    return {
      'type': normalizeType(type),
      'recipient_name': recipientName,
      'recipient_phone': recipientPhone,
      'house_no': houseNo,
      'street_address': streetAddress,
      'landmark': landmark,
      'city': city,
      'pincode': pincode,
      'latitude': latitude,
      'longitude': longitude,
      'is_default': isDefault,
    };
  }
}
