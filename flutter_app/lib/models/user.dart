class User {
  final int id;
  final String name;
  final String? email;
  final String phone;
  final String role;
  final String? preferredLanguage;

  User({
    required this.id,
    required this.name,
    this.email,
    required this.phone,
    required this.role,
    this.preferredLanguage,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      email: json['email'],
      phone: json['phone'] ?? '',
      role: json['role'] ?? 'customer',
      preferredLanguage: json['preferred_language'] ?? 'gu',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'role': role,
      'preferred_language': preferredLanguage,
    };
  }
}
