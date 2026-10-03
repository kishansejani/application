import 'package:flutter/material.dart';
import '../l10n/app_localizations.dart';
import '../models/json_utils.dart';
import '../theme/app_theme.dart';

/// Express (2-hour) vs next-day banner driven by the backend delivery slot.
/// Accepts the home `delivery_banner` ({type, slot, promise_text}) or the
/// `/delivery-slot` payload ({type, slot_text, rule_description}).
class DeliverySlotBanner extends StatelessWidget {
  final Map<String, dynamic>? slotInfo;
  final bool compact;

  const DeliverySlotBanner({super.key, this.slotInfo, this.compact = false});

  static bool isExpress(Map<String, dynamic>? info) {
    final type = asStringOrNull(info?['type']);
    if (type != null) return type == 'two_hours' || type == '2_hours';
    return DateTime.now().hour < 12;
  }

  @override
  Widget build(BuildContext context) {
    final express = isExpress(slotInfo);
    final title = context.tr(express ? 'delivery_within_2_hours' : 'delivery_next_day');
    final slot = asStringOrNull(slotInfo?['slot'] ?? slotInfo?['slot_text']);
    final promise = asStringOrNull(slotInfo?['promise_text'] ?? slotInfo?['rule_description']) ??
        context.tr(express ? 'order_before_12' : 'order_after_12');

    final colors = express
        ? const [Color(0xFF16A34A), Color(0xFF0D9488)]
        : const [Color(0xFF4F46E5), Color(0xFF7C3AED)];

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: colors),
        borderRadius: BorderRadius.circular(AppRadius.md),
        boxShadow: [BoxShadow(color: colors.first.fade(0.25), blurRadius: 16, offset: const Offset(0, 6))],
      ),
      padding: EdgeInsets.all(compact ? AppSpacing.md : AppSpacing.lg),
      child: Row(
        children: [
          Container(
            width: compact ? 40 : 48,
            height: compact ? 40 : 48,
            decoration: BoxDecoration(
              color: Colors.white.fade(0.18),
              borderRadius: BorderRadius.circular(AppRadius.sm),
            ),
            child: Icon(
              express ? Icons.bolt_rounded : Icons.schedule_rounded,
              color: Colors.white,
              size: compact ? 22 : 28,
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: context.textStyles.titleSmall?.copyWith(color: Colors.white, fontWeight: FontWeight.w800),
                ),
                if (slot != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    slot,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: context.textStyles.bodySmall?.copyWith(color: Colors.white, fontWeight: FontWeight.w600),
                  ),
                ],
                if (!compact) ...[
                  const SizedBox(height: 2),
                  Text(
                    promise,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: context.textStyles.bodySmall?.copyWith(color: Colors.white.fade(0.85)),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
