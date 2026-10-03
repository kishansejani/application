/// Tolerant JSON readers: the Laravel API returns numbers as numbers or as
/// decimal strings ("120.00") and booleans as true/1/"1".
int asInt(dynamic v, [int fallback = 0]) {
  if (v == null) return fallback;
  if (v is int) return v;
  if (v is num) return v.toInt();
  final s = v.toString();
  return int.tryParse(s) ?? double.tryParse(s)?.toInt() ?? fallback;
}

int? asIntOrNull(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  final s = v.toString();
  return int.tryParse(s) ?? double.tryParse(s)?.toInt();
}

double asDouble(dynamic v, [double fallback = 0.0]) {
  if (v == null) return fallback;
  if (v is num) return v.toDouble();
  return double.tryParse(v.toString()) ?? fallback;
}

double? asDoubleOrNull(dynamic v) {
  if (v == null) return null;
  if (v is num) return v.toDouble();
  return double.tryParse(v.toString());
}

bool asBool(dynamic v, [bool fallback = false]) {
  if (v == null) return fallback;
  if (v is bool) return v;
  if (v is num) return v != 0;
  final s = v.toString().toLowerCase();
  return s == 'true' || s == '1' || s == 'yes';
}

String asString(dynamic v, [String fallback = '']) {
  if (v == null) return fallback;
  return v.toString();
}

String? asStringOrNull(dynamic v) {
  if (v == null) return null;
  final s = v.toString().trim();
  return s.isEmpty ? null : s;
}

List<dynamic> asList(dynamic v) => v is List ? v : const [];

Map<String, dynamic> asMap(dynamic v) {
  if (v is Map<String, dynamic>) return v;
  if (v is Map) return v.map((key, value) => MapEntry(key.toString(), value));
  return <String, dynamic>{};
}

/// Maps every Map element of a JSON list through [fromJson].
List<T> mapList<T>(dynamic v, T Function(Map<String, dynamic> json) fromJson) {
  return asList(v).whereType<Map>().map((e) => fromJson(asMap(e))).toList();
}
