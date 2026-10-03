import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../models/order.dart';
import '../theme/app_theme.dart';

/// Vertical order tracking timeline built from `order_status`.
class OrderTimeline extends StatelessWidget {
  final String status;
  final String? placedAt;
  final String? estimatedAt;

  const OrderTimeline({super.key, required this.status, this.placedAt, this.estimatedAt});

  static const Map<String, IconData> _icons = {
    'pending': Icons.receipt_long_rounded,
    'confirmed': Icons.thumb_up_alt_rounded,
    'processing': Icons.inventory_2_rounded,
    'out_for_delivery': Icons.delivery_dining_rounded,
    'delivered': Icons.home_rounded,
  };

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;

    if (status == 'cancelled') {
      return Row(
        children: [
          const _Dot(color: AppColors.statusCancelled, icon: Icons.close_rounded, active: true),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(context.tr('status_cancelled'),
                    style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800, color: AppColors.statusCancelled)),
                Text(context.tr('order_cancelled_msg'),
                    style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant)),
              ],
            ),
          ),
        ],
      );
    }

    final current = Order.trackingSteps.indexOf(status);
    final steps = Order.trackingSteps;
    return Column(
      children: List.generate(steps.length, (i) {
        final done = current >= 0 && i < current;
        final active = i == current;
        final reached = done || active;
        final color = reached ? scheme.primary : scheme.outlineVariant;
        final isLast = i == steps.length - 1;
        String? caption;
        if (i == 0 && placedAt != null) caption = placedAt;
        if (isLast && estimatedAt != null && !reached) caption = context.tr('expected_by', {'time': estimatedAt!});
        if (active && !isLast) caption = caption ?? context.tr('in_progress');

        return IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              SizedBox(
                width: 40,
                child: Column(
                  children: [
                    _Dot(
                      color: color,
                      icon: done ? Icons.check_rounded : (_icons[steps[i]] ?? Icons.circle),
                      active: reached,
                      pulse: active,
                    ),
                    if (!isLast)
                      Expanded(
                        child: Container(
                          width: 3,
                          margin: const EdgeInsets.symmetric(vertical: 4),
                          decoration: BoxDecoration(
                            color: done ? scheme.primary : scheme.outlineVariant.fade(0.6),
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Padding(
                  padding: EdgeInsets.only(top: 6, bottom: isLast ? 0 : AppSpacing.xl),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        context.tr('status_${steps[i]}'),
                        style: context.textStyles.titleSmall?.copyWith(
                          fontWeight: reached ? FontWeight.w800 : FontWeight.w500,
                          color: reached ? scheme.onSurface : scheme.onSurfaceVariant,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        caption ?? context.tr('step_desc_${steps[i]}'),
                        style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      }),
    );
  }
}

class _Dot extends StatelessWidget {
  final Color color;
  final IconData icon;
  final bool active;
  final bool pulse;

  const _Dot({required this.color, required this.icon, required this.active, this.pulse = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        color: active ? color : context.colors.surface,
        shape: BoxShape.circle,
        border: Border.all(color: color, width: 2),
        boxShadow: pulse ? [BoxShadow(color: color.fade(0.35), blurRadius: 12, spreadRadius: 2)] : null,
      ),
      child: Icon(icon, size: 18, color: active ? Colors.white : color),
    );
  }
}
