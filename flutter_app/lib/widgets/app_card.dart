import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/// Themed surface card: rounded 16, hairline outline, optional ink ripple.
/// (Used instead of ThemeData.cardTheme so the app compiles on every Flutter
/// version regardless of the CardTheme -> CardThemeData migration.)
class AppCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final VoidCallback? onTap;
  final Color? color;
  final double radius;
  final Color? borderColor;
  final bool elevated;

  const AppCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(AppSpacing.lg),
    this.margin,
    this.onTap,
    this.color,
    this.radius = AppRadius.md,
    this.borderColor,
    this.elevated = false,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final shape = RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(radius),
      side: BorderSide(color: borderColor ?? scheme.outlineVariant.fade(context.isDark ? 0.5 : 0.7)),
    );
    Widget content = padding != null ? Padding(padding: padding!, child: child) : child;
    if (onTap != null) {
      content = InkWell(onTap: onTap, customBorder: shape, child: content);
    }
    final card = Material(
      color: color ?? scheme.surface,
      shape: shape,
      elevation: elevated ? 1 : 0,
      shadowColor: Colors.black.fade(0.08),
      clipBehavior: Clip.antiAlias,
      child: content,
    );
    return margin != null ? Padding(padding: margin!, child: card) : card;
  }
}

/// Small heading used inside cards / sections ("1  Delivery address").
class CardTitle extends StatelessWidget {
  final String title;
  final IconData? icon;
  final Widget? trailing;

  const CardTitle({super.key, required this.title, this.icon, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        if (icon != null) ...[
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: context.colors.primaryContainer,
              borderRadius: BorderRadius.circular(AppRadius.xs),
            ),
            child: Icon(icon, size: 18, color: context.colors.onPrimaryContainer),
          ),
          const SizedBox(width: AppSpacing.md),
        ],
        Expanded(
          child: Text(
            title,
            style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
          ),
        ),
        if (trailing != null) trailing!,
      ],
    );
  }
}
