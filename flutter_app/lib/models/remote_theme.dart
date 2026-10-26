import 'package:flutter/material.dart';

/// Colour palette for one brightness, as configured in the admin panel.
class RemotePalette {
  final Color primary, primaryHover, primarySoft, accent, buttonBg, buttonText;
  final Color background, surface, border, header, text, textMuted, inputBg, inputBorder;

  const RemotePalette({
    required this.primary,
    required this.primaryHover,
    required this.primarySoft,
    required this.accent,
    required this.buttonBg,
    required this.buttonText,
    required this.background,
    required this.surface,
    required this.border,
    required this.header,
    required this.text,
    required this.textMuted,
    required this.inputBg,
    required this.inputBorder,
  });

  static Color? parseHex(dynamic v) {
    if (v == null) return null;
    var h = v.toString().trim().replaceFirst('#', '');
    if (h.length == 3) h = h.split('').map((c) => '$c$c').join();
    if (h.length == 6) h = 'FF$h';
    if (h.length != 8) return null;
    final n = int.tryParse(h, radix: 16);
    return n == null ? null : Color(n);
  }

  factory RemotePalette.fromJson(Map<String, dynamic>? j, RemotePalette fallback) {
    Color c(String k, Color d) => parseHex(j?[k]) ?? d;
    return RemotePalette(
      primary: c('primary', fallback.primary),
      primaryHover: c('primary_hover', fallback.primaryHover),
      primarySoft: c('primary_soft', fallback.primarySoft),
      accent: c('accent', fallback.accent),
      buttonBg: c('button_bg', fallback.buttonBg),
      buttonText: c('button_text', fallback.buttonText),
      background: c('background', fallback.background),
      surface: c('surface', fallback.surface),
      border: c('border', fallback.border),
      header: c('header', fallback.header),
      text: c('text', fallback.text),
      textMuted: c('text_muted', fallback.textMuted),
      inputBg: c('input_bg', fallback.inputBg),
      inputBorder: c('input_border', fallback.inputBorder),
    );
  }

  static const light = RemotePalette(
    primary: Color(0xFF059669),
    primaryHover: Color(0xFF047857),
    primarySoft: Color(0xFFECFDF5),
    accent: Color(0xFF10B981),
    buttonBg: Color(0xFF059669),
    buttonText: Colors.white,
    background: Color(0xFFF8FAFC),
    surface: Colors.white,
    border: Color(0xFFE2E8F0),
    header: Colors.white,
    text: Color(0xFF0F172A),
    textMuted: Color(0xFF64748B),
    inputBg: Colors.white,
    inputBorder: Color(0xFFCBD5E1),
  );

  static const dark = RemotePalette(
    primary: Color(0xFF34D399),
    primaryHover: Color(0xFF6EE7B7),
    primarySoft: Color(0xFF064E3B),
    accent: Color(0xFF34D399),
    buttonBg: Color(0xFF10B981),
    buttonText: Color(0xFF022C22),
    background: Color(0xFF0B1120),
    surface: Color(0xFF111827),
    border: Color(0xFF1F2937),
    header: Color(0xFF0F172A),
    text: Color(0xFFF1F5F9),
    textMuted: Color(0xFF94A3B8),
    inputBg: Color(0xFF1E293B),
    inputBorder: Color(0xFF334155),
  );

  Map<String, String> toJson() {
    // ignore: deprecated_member_use
    String h(Color c) => '#${(c.value & 0xFFFFFF).toRadixString(16).padLeft(6, '0')}';
    return {
      'primary': h(primary), 'primary_hover': h(primaryHover), 'primary_soft': h(primarySoft),
      'accent': h(accent), 'button_bg': h(buttonBg), 'button_text': h(buttonText),
      'background': h(background), 'surface': h(surface), 'border': h(border), 'header': h(header),
      'text': h(text), 'text_muted': h(textMuted), 'input_bg': h(inputBg), 'input_border': h(inputBorder),
    };
  }
}

/// Theme + design configuration served by `GET /api/theme`.
class RemoteTheme {
  final RemotePalette light;
  final RemotePalette dark;
  final String style; // aurora | soft | glass | bold | minimal
  final String radius; // sharp | soft | round | pill
  final String font; // jakarta | poppins | outfit | manrope | sora
  final bool aurora, glassHeader, floatTabbar;
  final bool spinAutoplay, spinHint;
  final int spinSpeed, spinSensitivity;
  final String themeMode;
  final String version;

  const RemoteTheme({
    this.light = RemotePalette.light,
    this.dark = RemotePalette.dark,
    this.style = 'aurora',
    this.radius = 'round',
    this.font = 'jakarta',
    this.aurora = true,
    this.glassHeader = true,
    this.floatTabbar = true,
    this.spinAutoplay = true,
    this.spinHint = true,
    this.spinSpeed = 50,
    this.spinSensitivity = 50,
    this.themeMode = 'system',
    this.version = 'default',
  });

  static const defaults = RemoteTheme();

  /// Corner radius multiplier applied to the whole app.
  double get radiusFactor {
    switch (radius) {
      case 'sharp':
        return 0.35;
      case 'soft':
        return 0.7;
      case 'pill':
        return 1.5;
      default:
        return 1.0;
    }
  }

  static bool _b(dynamic v, bool d) {
    if (v == null) return d;
    if (v is bool) return v;
    return v == 1 || v == '1' || v == 'true';
  }

  static int _i(dynamic v, int d) => v is num ? v.toInt() : int.tryParse('$v') ?? d;

  factory RemoteTheme.fromJson(Map<String, dynamic> j) {
    final design = j['design'] is Map ? Map<String, dynamic>.from(j['design']) : <String, dynamic>{};
    final fx = design['effects'] is Map ? Map<String, dynamic>.from(design['effects']) : <String, dynamic>{};
    final spin = design['spin'] is Map ? Map<String, dynamic>.from(design['spin']) : <String, dynamic>{};
    return RemoteTheme(
      light: RemotePalette.fromJson(j['light'] is Map ? Map<String, dynamic>.from(j['light']) : null, RemotePalette.light),
      dark: RemotePalette.fromJson(j['dark'] is Map ? Map<String, dynamic>.from(j['dark']) : null, RemotePalette.dark),
      style: (design['style'] ?? 'aurora').toString(),
      radius: (design['radius'] ?? 'round').toString(),
      font: (design['font'] ?? 'jakarta').toString(),
      aurora: _b(fx['aurora'], true),
      glassHeader: _b(fx['glass_header'], true),
      floatTabbar: _b(fx['float_tabbar'], true),
      spinAutoplay: _b(spin['autoplay'], true),
      spinHint: _b(spin['hint'], true),
      spinSpeed: _i(spin['speed'], 50),
      spinSensitivity: _i(spin['sensitivity'], 50),
      themeMode: (j['theme_mode'] ?? 'system').toString(),
      version: (j['version'] ?? 'default').toString(),
    );
  }

  /// Round-trippable JSON for the offline cache.
  Map<String, dynamic> toJson() => {
        'light': light.toJson(),
        'dark': dark.toJson(),
        'design': {
          'style': style,
          'radius': radius,
          'font': font,
          'effects': {'aurora': aurora, 'glass_header': glassHeader, 'float_tabbar': floatTabbar},
          'spin': {'autoplay': spinAutoplay, 'speed': spinSpeed, 'sensitivity': spinSensitivity, 'hint': spinHint},
        },
        'theme_mode': themeMode,
        'version': version,
      };
}
