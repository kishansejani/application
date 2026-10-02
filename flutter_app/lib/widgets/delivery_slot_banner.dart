import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';

class DeliverySlotBanner extends StatelessWidget {
  final Map<String, dynamic>? slotInfo;

  const DeliverySlotBanner({Key? key, this.slotInfo}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final is2Hours = slotInfo?['is_2_hours'] == true;
    final title = slotInfo?['title'] ?? (is2Hours ? context.tr('delivery_within_2_hours') : context.tr('delivery_next_day'));
    final description = slotInfo?['description'] ?? (is2Hours ? context.tr('order_before_12') : context.tr('order_after_12'));
    final timeStr = slotInfo?['delivery_time_formatted'];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: is2Hours
              ? [const Color(0xFF16A34A), const Color(0xFF0D9488)]
              : [const Color(0xFFD97706), const Color(0xFFEA580C)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: (is2Hours ? AppColors.primary : AppColors.accent).withOpacity(0.25),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: Icon(
              is2Hours ? Icons.electric_bolt_rounded : Icons.wb_sunny_rounded,
              color: Colors.white,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 13,
                        ),
                      ),
                    ),
                    if (timeStr != null) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          timeStr,
                          style: TextStyle(
                            color: is2Hours ? AppColors.primaryDark : Colors.orange.shade900,
                            fontWeight: FontWeight.bold,
                            fontSize: 10,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  description,
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.9),
                    fontSize: 11,
                    height: 1.2,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
