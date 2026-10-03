import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../models/offer.dart';
import '../theme/app_theme.dart';
import '../utils/app_actions.dart';
import '../utils/formatters.dart';

/// Coupon-ticket style offer card with copy-code action.
class OfferCard extends StatelessWidget {
  final Offer offer;
  final VoidCallback? onApply;
  final bool compact;

  const OfferCard({super.key, required this.offer, this.onApply, this.compact = false});

  static const List<List<Color>> _gradients = [
    [Color(0xFF16A34A), Color(0xFF0F766E)],
    [Color(0xFFF59E0B), Color(0xFFEA580C)],
    [Color(0xFF2563EB), Color(0xFF7C3AED)],
    [Color(0xFFE11D48), Color(0xFFC026D3)],
  ];

  String _valueLabel() {
    if (offer.isPercentage) {
      final v = offer.discountValue;
      return '${v == v.roundToDouble() ? v.toStringAsFixed(0) : v.toStringAsFixed(1)}%';
    }
    return Fmt.price(offer.discountValue);
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final colors = _gradients[offer.id.abs() % _gradients.length];
    final details = <String>[
      if (offer.minOrderAmount > 0) context.tr('min_order_value', {'amount': Fmt.price(offer.minOrderAmount)}),
      if (offer.maxDiscountAmount != null) context.tr('max_discount_value', {'amount': Fmt.price(offer.maxDiscountAmount!)}),
      if (offer.expiresAt != null) context.tr('valid_till', {'date': offer.expiresAt!}),
    ];

    return Material(
      color: scheme.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        side: BorderSide(color: scheme.outlineVariant.fade(0.7)),
      ),
      clipBehavior: Clip.antiAlias,
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Left stub
            Container(
              width: compact ? 78 : 92,
              decoration: BoxDecoration(
                gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: colors),
              ),
              padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: AppSpacing.xs),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.local_offer_rounded, color: Colors.white, size: 20),
                  const SizedBox(height: 6),
                  FittedBox(
                    fit: BoxFit.scaleDown,
                    child: Text(
                      _valueLabel(),
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: compact ? 18 : 22,
                      ),
                    ),
                  ),
                  Text(
                    context.tr('off'),
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 11, letterSpacing: 1.2),
                  ),
                ],
              ),
            ),
            _DashedDivider(color: scheme.outlineVariant),
            Expanded(
              child: Padding(
                padding: EdgeInsets.all(compact ? AppSpacing.md : AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      offer.title,
                      maxLines: compact ? 1 : 2,
                      overflow: TextOverflow.ellipsis,
                      style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800),
                    ),
                    if (!compact && offer.description != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        offer.description!,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    ],
                    if (details.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        details.join(' • '),
                        maxLines: compact ? 1 : 2,
                        overflow: TextOverflow.ellipsis,
                        style: context.textStyles.labelSmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    ],
                    const SizedBox(height: AppSpacing.sm),
                    Row(
                      children: [
                        Flexible(child: _CodeChip(code: offer.code)),
                        if (onApply != null) ...[
                          const SizedBox(width: AppSpacing.sm),
                          TextButton(
                            onPressed: onApply,
                            style: TextButton.styleFrom(minimumSize: const Size(0, 34), padding: const EdgeInsets.symmetric(horizontal: 10)),
                            child: Text(context.tr('apply')),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CodeChip extends StatelessWidget {
  final String code;
  const _CodeChip({required this.code});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Material(
      color: AppColors.accent.fade(context.isDark ? 0.18 : 0.12),
      borderRadius: BorderRadius.circular(AppRadius.xs),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.xs),
        onTap: () => AppActions.copyToClipboard(
          context,
          code,
          message: context.tr('code_copied', {'code': code}),
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Flexible(
                child: Text(
                  code,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                    fontSize: 13,
                    color: context.isDark ? AppColors.accent : const Color(0xFFB45309),
                  ),
                ),
              ),
              const SizedBox(width: 6),
              Icon(Icons.content_copy_rounded, size: 14, color: scheme.onSurfaceVariant),
            ],
          ),
        ),
      ),
    );
  }
}

class _DashedDivider extends StatelessWidget {
  final Color color;
  const _DashedDivider({required this.color});

  @override
  Widget build(BuildContext context) {
    // CustomPaint (not LayoutBuilder) so it works inside IntrinsicHeight.
    return SizedBox(width: 1, child: CustomPaint(painter: _DashPainter(color)));
  }
}

class _DashPainter extends CustomPainter {
  final Color color;
  _DashPainter(this.color);

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 1;
    double y = 4;
    while (y < size.height - 4) {
      canvas.drawLine(Offset(0.5, y), Offset(0.5, y + 4), paint);
      y += 8;
    }
  }

  @override
  bool shouldRepaint(covariant _DashPainter oldDelegate) => oldDelegate.color != color;
}
