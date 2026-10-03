import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';
import '../utils/formatters.dart';

/// Selling price with optional struck-through MRP.
class PriceText extends StatelessWidget {
  final double price;
  final double? strikePrice;
  final double fontSize;
  final bool vertical;

  const PriceText({
    super.key,
    required this.price,
    this.strikePrice,
    this.fontSize = 16,
    this.vertical = false,
  });

  @override
  Widget build(BuildContext context) {
    final showStrike = strikePrice != null && strikePrice! > price;
    final main = Text(
      Fmt.price(price),
      maxLines: 1,
      style: context.textStyles.titleMedium?.copyWith(
        fontSize: fontSize,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.2,
      ),
    );
    if (!showStrike) return main;
    final strike = Text(
      Fmt.price(strikePrice!),
      maxLines: 1,
      style: context.textStyles.bodySmall?.copyWith(
        fontSize: fontSize * 0.72,
        color: context.colors.onSurfaceVariant,
        decoration: TextDecoration.lineThrough,
        decorationColor: context.colors.onSurfaceVariant,
      ),
    );
    if (vertical) {
      return Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [main, strike],
      );
    }
    return Wrap(
      crossAxisAlignment: WrapCrossAlignment.end,
      spacing: 6,
      children: [main, strike],
    );
  }
}

/// "20% OFF" ribbon.
class DiscountBadge extends StatelessWidget {
  final int percent;
  final bool large;

  const DiscountBadge({super.key, required this.percent, this.large = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: large ? 10 : 7, vertical: large ? 5 : 3),
      decoration: BoxDecoration(
        color: AppColors.discount,
        borderRadius: BorderRadius.circular(AppRadius.xs),
      ),
      child: Text(
        context.tr('percent_off', {'percent': '$percent'}),
        style: TextStyle(
          color: Colors.white,
          fontSize: large ? 12 : 10,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.2,
        ),
      ),
    );
  }
}
