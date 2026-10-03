import 'package:flutter/material.dart';

/// Brand palette. Material 3 colour schemes are generated from [seed] in
/// `theme/app_theme.dart`; these constants are for brand accents and semantic
/// colours that must look the same in light and dark mode.
class AppColors {
  AppColors._();

  static const Color seed = Color(0xFF16A34A);

  static const Color primary = Color(0xFF16A34A); // Green 600
  static const Color primaryDark = Color(0xFF15803D); // Green 700
  static const Color primaryDeep = Color(0xFF14532D); // Green 900
  static const Color primaryLight = Color(0xFFDCFCE7); // Green 100
  static const Color primaryOnDark = Color(0xFF4ADE80); // Green 400

  static const Color accent = Color(0xFFF59E0B); // Amber 500
  static const Color accentLight = Color(0xFFFEF3C7);

  // Light surfaces
  static const Color background = Color(0xFFF6F8F7);
  static const Color cardBg = Colors.white;
  static const Color textPrimary = Color(0xFF0F172A);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textMuted = Color(0xFF94A3B8);
  static const Color border = Color(0xFFE2E8F0);
  static const Color borderLight = Color(0xFFF1F5F9);

  // Dark surfaces
  static const Color backgroundDark = Color(0xFF0C110E);
  static const Color surfaceDark = Color(0xFF141A16);

  // Semantic
  static const Color success = Color(0xFF16A34A);
  static const Color error = Color(0xFFDC2626);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF2563EB);
  static const Color discount = Color(0xFFE11D48);

  // Order status colours
  static const Color statusPending = Color(0xFFD97706);
  static const Color statusConfirmed = Color(0xFF2563EB);
  static const Color statusProcessing = Color(0xFF7C3AED);
  static const Color statusOutForDelivery = Color(0xFF0891B2);
  static const Color statusDelivered = Color(0xFF16A34A);
  static const Color statusCancelled = Color(0xFFDC2626);

  static Color statusColor(String status) {
    switch (status) {
      case 'pending':
        return statusPending;
      case 'confirmed':
        return statusConfirmed;
      case 'processing':
        return statusProcessing;
      case 'out_for_delivery':
        return statusOutForDelivery;
      case 'delivered':
        return statusDelivered;
      case 'cancelled':
        return statusCancelled;
      default:
        return textSecondary;
    }
  }

  /// Soft tints used behind category tiles.
  static const List<Color> categoryTints = [
    Color(0xFFDCFCE7),
    Color(0xFFFEF3C7),
    Color(0xFFE0F2FE),
    Color(0xFFFCE7F3),
    Color(0xFFEDE9FE),
    Color(0xFFFFEDD5),
    Color(0xFFCCFBF1),
    Color(0xFFFEE2E2),
  ];

  static const List<Color> categoryTintsDark = [
    Color(0xFF173A24),
    Color(0xFF3A2F12),
    Color(0xFF14304A),
    Color(0xFF3F1A2E),
    Color(0xFF2B2248),
    Color(0xFF3F2814),
    Color(0xFF113B36),
    Color(0xFF3F1717),
  ];
}
