import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';
import '../models/remote_theme.dart';

/// Corner radius tokens.
class AppRadius {
  AppRadius._();
  static const double xs = 8;
  static const double sm = 12;
  static const double md = 16;
  static const double lg = 20;
  static const double xl = 28;
  static const double pill = 999;
}

/// Spacing tokens (4pt grid).
class AppSpacing {
  AppSpacing._();
  static const double xxs = 2;
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double xxl = 32;
  static const double xxxl = 48;
}

/// Opacity helper that works on every Flutter version without deprecation
/// warnings (`withOpacity` is deprecated on newer SDKs, `withValues` is not
/// available on older ones).
extension ColorFade on Color {
  Color fade(double opacity) => withAlpha((opacity.clamp(0.0, 1.0) * 255).round());
}

/// Short-hands for theme lookups.
extension AppThemeContext on BuildContext {
  ThemeData get theme => Theme.of(this);
  ColorScheme get colors => Theme.of(this).colorScheme;
  TextTheme get textStyles => Theme.of(this).textTheme;
  bool get isDark => Theme.of(this).brightness == Brightness.dark;
}

/// Admin-managed design language, available via `context.design`.
class AppDesign extends ThemeExtension<AppDesign> {
  final String style; // aurora | soft | glass | bold | minimal
  final double radiusFactor;
  final bool aurora, glassHeader, floatTabbar;
  final bool spinAutoplay, spinHint;
  final int spinSpeed, spinSensitivity;

  const AppDesign({
    this.style = 'aurora',
    this.radiusFactor = 1,
    this.aurora = true,
    this.glassHeader = true,
    this.floatTabbar = true,
    this.spinAutoplay = true,
    this.spinHint = true,
    this.spinSpeed = 50,
    this.spinSensitivity = 50,
  });

  double r(double base) => base * radiusFactor;
  double get card => r(AppRadius.lg);
  double get button => r(AppRadius.md);
  bool get isGlass => style == 'glass';
  bool get isBold => style == 'bold';
  bool get isMinimal => style == 'minimal';
  bool get isSoft => style == 'soft';

  @override
  AppDesign copyWith() => this;

  @override
  AppDesign lerp(ThemeExtension<AppDesign>? other, double t) => this;
}

extension AppDesignContext on BuildContext {
  AppDesign get design => Theme.of(this).extension<AppDesign>() ?? const AppDesign();
}

class AppTheme {
  AppTheme._();

  static final Map<String, ThemeData> _cache = {};

  static ThemeData light([RemoteTheme? t]) => _get(t ?? RemoteTheme.defaults, Brightness.light);
  static ThemeData dark([RemoteTheme? t]) => _get(t ?? RemoteTheme.defaults, Brightness.dark);

  static ThemeData _get(RemoteTheme t, Brightness b) =>
      _cache['${t.version}|${t.style}|${t.radius}|${t.font}|${b.name}'] ??= _build(t, b);

  static TextTheme _fontTheme(String font, TextTheme base) {
    switch (font) {
      case 'poppins':
        return GoogleFonts.poppinsTextTheme(base);
      case 'outfit':
        return GoogleFonts.outfitTextTheme(base);
      case 'manrope':
        return GoogleFonts.manropeTextTheme(base);
      case 'sora':
        return GoogleFonts.soraTextTheme(base);
      default:
        return GoogleFonts.plusJakartaSansTextTheme(base);
    }
  }

  static ThemeData _build(RemoteTheme t, Brightness brightness) {
    final isDark = brightness == Brightness.dark;
    final pal = isDark ? t.dark : t.light;
    final f = t.radiusFactor;
    double rr(double v) => v * f;

    var scheme = ColorScheme.fromSeed(seedColor: pal.primary, brightness: brightness);
    scheme = scheme.copyWith(
      primary: pal.buttonBg,
      onPrimary: pal.buttonText,
      primaryContainer: pal.primarySoft,
      onPrimaryContainer: isDark ? pal.primary : pal.primaryHover,
      surface: pal.surface,
      onSurface: pal.text,
      onSurfaceVariant: pal.textMuted,
      outline: pal.inputBorder,
      outlineVariant: pal.border,
      tertiary: pal.accent,
      surfaceContainerHigh: isDark ? pal.inputBg : null,
    );

    final accentInk = isDark ? pal.primary : pal.primaryHover;
    final scaffoldBg = pal.background;
    final baseText = ThemeData(brightness: brightness, useMaterial3: true).textTheme;
    final textTheme = _fontTheme(t.font, baseText).apply(
      bodyColor: pal.text,
      displayColor: pal.text,
    );

    final isBold = t.style == 'bold';
    final inputBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(rr(AppRadius.sm)),
      borderSide: BorderSide(color: pal.inputBorder, width: isBold ? 1.4 : 1),
    );

    final buttonShape = RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.md)));

    // Icons: neutral at rest, brand colour while pressed / hovered / focused.
    final iconRest = scheme.onSurfaceVariant;
    final iconActive = accentInk;
    Color? iconForeground(Set<WidgetState> states) {
      if (states.contains(WidgetState.disabled)) return null;
      if (states.contains(WidgetState.pressed) ||
          states.contains(WidgetState.hovered) ||
          states.contains(WidgetState.focused)) {
        return iconActive;
      }
      return null;
    }

    Color? iconOverlay(Set<WidgetState> states) {
      if (states.contains(WidgetState.pressed)) return iconActive.fade(0.14);
      if (states.contains(WidgetState.focused)) return iconActive.fade(0.12);
      if (states.contains(WidgetState.hovered)) return iconActive.fade(0.08);
      return null;
    }

    final menuColor = isDark ? pal.inputBg : pal.surface;
    final menuShape = RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(rr(AppRadius.md)),
      side: BorderSide(color: scheme.outlineVariant.fade(0.6)),
    );
    final menuStyle = MenuStyle(
      backgroundColor: WidgetStatePropertyAll(menuColor),
      surfaceTintColor: const WidgetStatePropertyAll(Colors.transparent),
      shadowColor: WidgetStatePropertyAll(Colors.black.fade(isDark ? 0.5 : 0.18)),
      elevation: const WidgetStatePropertyAll(6.0),
      shape: WidgetStatePropertyAll(menuShape),
      padding: const WidgetStatePropertyAll(EdgeInsets.symmetric(vertical: AppSpacing.xs + 2)),
    );
    final buttonText = textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700, fontSize: 15);

    final design = AppDesign(
      style: t.style,
      radiusFactor: f,
      aurora: t.aurora,
      glassHeader: t.glassHeader,
      floatTabbar: t.floatTabbar,
      spinAutoplay: t.spinAutoplay,
      spinHint: t.spinHint,
      spinSpeed: t.spinSpeed,
      spinSensitivity: t.spinSensitivity,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: scheme,
      extensions: [design],
      scaffoldBackgroundColor: scaffoldBg,
      canvasColor: scaffoldBg,
      cardColor: scheme.surface,
      textTheme: textTheme,
      visualDensity: VisualDensity.standard,
      iconTheme: IconThemeData(color: iconRest, size: 24),
      splashColor: iconActive.fade(0.12),
      highlightColor: iconActive.fade(0.06),
      hoverColor: iconActive.fade(0.05),
      focusColor: iconActive.fade(0.10),
      appBarTheme: AppBarTheme(
        elevation: 0,
        scrolledUnderElevation: 0.6,
        centerTitle: false,
        backgroundColor: t.glassHeader ? scaffoldBg.fade(0.92) : pal.header,
        surfaceTintColor: Colors.transparent,
        foregroundColor: scheme.onSurface,
        iconTheme: IconThemeData(color: scheme.onSurface),
        titleTextStyle: textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800, fontSize: 20),
        systemOverlayStyle: isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(64, 52),
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl),
          shape: buttonShape,
          textStyle: buttonText,
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          minimumSize: const Size(64, 52),
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl),
          shape: buttonShape,
          elevation: 0,
          backgroundColor: scheme.primary,
          foregroundColor: scheme.onPrimary,
          textStyle: buttonText,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(64, 52),
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl),
          shape: buttonShape,
          side: BorderSide(color: scheme.outlineVariant),
          textStyle: buttonText,
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
          textStyle: textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700),
        ),
      ),
      iconButtonTheme: IconButtonThemeData(
        style: ButtonStyle(
          shape: WidgetStatePropertyAll(
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
          ),
          foregroundColor: WidgetStateProperty.resolveWith(iconForeground),
          overlayColor: WidgetStateProperty.resolveWith(iconOverlay),
        ),
      ),
      popupMenuTheme: PopupMenuThemeData(
        color: menuColor,
        surfaceTintColor: Colors.transparent,
        shadowColor: Colors.black.fade(isDark ? 0.5 : 0.18),
        elevation: 6,
        shape: menuShape,
        position: PopupMenuPosition.under,
        iconColor: iconRest,
        labelTextStyle: WidgetStatePropertyAll(
          textTheme.bodyLarge?.copyWith(fontWeight: FontWeight.w600, color: scheme.onSurface),
        ),
      ),
      menuTheme: MenuThemeData(style: menuStyle),
      dropdownMenuTheme: DropdownMenuThemeData(
        menuStyle: menuStyle,
        textStyle: textTheme.bodyLarge?.copyWith(fontWeight: FontWeight.w600),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: pal.inputBg,
        contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.lg),
        border: inputBorder,
        enabledBorder: inputBorder,
        disabledBorder: inputBorder,
        focusedBorder: inputBorder.copyWith(borderSide: BorderSide(color: scheme.primary, width: 1.6)),
        errorBorder: inputBorder.copyWith(borderSide: BorderSide(color: scheme.error)),
        focusedErrorBorder: inputBorder.copyWith(borderSide: BorderSide(color: scheme.error, width: 1.6)),
        hintStyle: textTheme.bodyMedium?.copyWith(color: scheme.onSurfaceVariant.fade(0.7)),
        labelStyle: textTheme.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 68,
        elevation: 0,
        backgroundColor: scheme.surface,
        surfaceTintColor: Colors.transparent,
        indicatorColor: scheme.primaryContainer,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return textTheme.labelSmall?.copyWith(
            fontWeight: selected ? FontWeight.w800 : FontWeight.w500,
            color: selected ? scheme.onSurface : scheme.onSurfaceVariant,
          );
        }),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return IconThemeData(color: selected ? scheme.onPrimaryContainer : scheme.onSurfaceVariant);
        }),
      ),
      navigationRailTheme: NavigationRailThemeData(
        backgroundColor: scheme.surface,
        indicatorColor: scheme.primaryContainer,
        selectedIconTheme: IconThemeData(color: scheme.onPrimaryContainer),
        unselectedIconTheme: IconThemeData(color: scheme.onSurfaceVariant),
        selectedLabelTextStyle: textTheme.labelMedium?.copyWith(fontWeight: FontWeight.w800, color: scheme.onSurface),
        unselectedLabelTextStyle: textTheme.labelMedium?.copyWith(color: scheme.onSurfaceVariant),
      ),
      chipTheme: ChipThemeData(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
        side: BorderSide(color: scheme.outlineVariant),
        backgroundColor: scheme.surface,
        selectedColor: scheme.primaryContainer,
        labelStyle: textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w600),
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm, vertical: AppSpacing.xs),
        showCheckmark: false,
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: scheme.surface,
        modalBackgroundColor: scheme.surface,
        surfaceTintColor: Colors.transparent,
        showDragHandle: true,
        clipBehavior: Clip.antiAlias,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(rr(AppRadius.xl))),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        elevation: 2,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
        backgroundColor: isDark ? scheme.surfaceContainerHighest : const Color(0xFF1E293B),
        contentTextStyle: textTheme.bodyMedium?.copyWith(
          color: isDark ? scheme.onSurface : Colors.white,
          fontWeight: FontWeight.w600,
        ),
        actionTextColor: isDark ? scheme.tertiary : AppColors.primaryOnDark,
      ),
      listTileTheme: ListTileThemeData(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
        iconColor: iconRest,
        selectedColor: iconActive,
        selectedTileColor: scheme.primaryContainer.fade(0.35),
        contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
        titleTextStyle: textTheme.bodyLarge?.copyWith(fontWeight: FontWeight.w600),
        subtitleTextStyle: textTheme.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
      ),
      dividerTheme: DividerThemeData(
        color: scheme.outlineVariant.fade(0.6),
        thickness: 1,
        space: 1,
      ),
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        elevation: 2,
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.md))),
      ),
      progressIndicatorTheme: ProgressIndicatorThemeData(color: scheme.primary),
      switchTheme: SwitchThemeData(
        thumbColor: WidgetStateProperty.resolveWith((states) =>
            states.contains(WidgetState.selected) ? scheme.onPrimary : null),
        trackColor: WidgetStateProperty.resolveWith((states) =>
            states.contains(WidgetState.selected) ? scheme.primary : null),
      ),
      segmentedButtonTheme: SegmentedButtonThemeData(
        style: ButtonStyle(
          shape: WidgetStatePropertyAll(
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(rr(AppRadius.sm))),
          ),
          textStyle: WidgetStatePropertyAll(textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700)),
        ),
      ),
      badgeTheme: BadgeThemeData(
        backgroundColor: AppColors.discount,
        textColor: Colors.white,
        textStyle: textTheme.labelSmall?.copyWith(fontWeight: FontWeight.w800, fontSize: 10),
      ),
    );
  }
}
