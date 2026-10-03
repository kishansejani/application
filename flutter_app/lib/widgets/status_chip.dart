import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';

/// Coloured pill for order / payment status.
class StatusChip extends StatelessWidget {
  final String status;
  final String? label;
  final Color? color;
  final bool dense;

  const StatusChip({super.key, required this.status, this.label, this.color, this.dense = false});

  @override
  Widget build(BuildContext context) {
    final c = color ?? AppColors.statusColor(status);
    final text = label ?? context.tr('status_$status');
    return Container(
      padding: EdgeInsets.symmetric(horizontal: dense ? 8 : 10, vertical: dense ? 3 : 5),
      decoration: BoxDecoration(
        color: c.fade(context.isDark ? 0.22 : 0.12),
        borderRadius: BorderRadius.circular(AppRadius.pill),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 7, height: 7, decoration: BoxDecoration(color: c, shape: BoxShape.circle)),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              text,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: context.isDark ? Color.lerp(c, Colors.white, 0.35) : c,
                fontWeight: FontWeight.w700,
                fontSize: dense ? 11 : 12,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
