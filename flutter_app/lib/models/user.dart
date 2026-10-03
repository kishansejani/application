import 'json_utils.dart';

class User {
  final int id;
  final String name;
  final String? email;
  final String phone;
  final String role;
  final String? preferredLanguage;
  final int ordersCount;
  final int addressesCount;

  User({
    required this.id,
    required this.name,
    this.email,
    required this.phone,
    this.role = 'customer',
    this.preferredLanguage,
    this.ordersCount = 0,
    this.addressesCount = 0,
  });

  String get initials {
    final parts = name.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty).toList();
    if (parts.isEmpty) return '?';
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return (parts.first.substring(0, 1) + parts.last.substring(0, 1)).toUpperCase();
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: asInt(json['id']),
      name: asString(json['name']),
      email: asStringOrNull(json['email']),
      phone: asString(json['phone']),
      role: asString(json['role'], 'customer'),
      preferredLanguage: asStringOrNull(json['language'] ?? json['preferred_language']),
      ordersCount: asInt(json['orders_count']),
      addressesCount: asInt(json['saved_addresses_count']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'role': role,
      'language': preferredLanguage,
    };
  }
}
