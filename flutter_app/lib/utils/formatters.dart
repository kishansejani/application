/// Formatting helpers.
class Fmt {
  Fmt._();

  /// ₹1,234 or ₹1,234.50 (Indian digit grouping).
  static String price(num value) {
    final v = value.toDouble();
    final negative = v < 0;
    final abs = v.abs();
    final hasPaise = (abs - abs.truncateToDouble()).abs() >= 0.005;
    final fixed = abs.toStringAsFixed(hasPaise ? 2 : 0);
    final parts = fixed.split('.');
    final grouped = _groupIndian(parts[0]);
    final result = parts.length > 1 ? '$grouped.${parts[1]}' : grouped;
    return '${negative ? '-' : ''}₹$result';
  }

  static String _groupIndian(String digits) {
    if (digits.length <= 3) return digits;
    final last3 = digits.substring(digits.length - 3);
    var rest = digits.substring(0, digits.length - 3);
    final groups = <String>[];
    while (rest.length > 2) {
      groups.insert(0, rest.substring(rest.length - 2));
      rest = rest.substring(0, rest.length - 2);
    }
    if (rest.isNotEmpty) groups.insert(0, rest);
    return '${groups.join(',')},$last3';
  }

  static String phone(String phone) {
    final p = phone.replaceAll(RegExp(r'\D'), '');
    if (p.length == 10) return '+91 ${p.substring(0, 5)} ${p.substring(5)}';
    return phone;
  }
}
