import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/// Compact – [qty] + stepper used on cards, cart rows and the product page.
class QtyStepper extends StatelessWidget {
  final int quantity;
  final VoidCallback? onIncrement;
  final VoidCallback? onDecrement;
  final bool loading;
  final bool large;

  const QtyStepper({
    super.key,
    required this.quantity,
    this.onIncrement,
    this.onDecrement,
    this.loading = false,
    this.large = false,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final double height = large ? 48 : 34;
    final double buttonWidth = large ? 44 : 30;
    return Container(
      height: height,
      decoration: BoxDecoration(
        color: scheme.primary,
        borderRadius: BorderRadius.circular(large ? AppRadius.sm : AppRadius.xs + 2),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _StepButton(
            icon: quantity <= 1 ? Icons.delete_outline_rounded : Icons.remove_rounded,
            onTap: loading ? null : onDecrement,
            width: buttonWidth,
            color: scheme.onPrimary,
            semantic: 'decrease',
          ),
          SizedBox(
            width: large ? 36 : 24,
            child: Center(
              child: loading
                  ? SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(strokeWidth: 2, color: scheme.onPrimary),
                    )
                  : AnimatedSwitcher(
                      duration: const Duration(milliseconds: 180),
                      transitionBuilder: (child, anim) => ScaleTransition(scale: anim, child: child),
                      child: Text(
                        '$quantity',
                        key: ValueKey<int>(quantity),
                        style: TextStyle(
                          color: scheme.onPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize: large ? 16 : 14,
                        ),
                      ),
                    ),
            ),
          ),
          _StepButton(
            icon: Icons.add_rounded,
            onTap: loading ? null : onIncrement,
            width: buttonWidth,
            color: scheme.onPrimary,
            semantic: 'increase',
          ),
        ],
      ),
    );
  }
}

class _StepButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onTap;
  final double width;
  final Color color;
  final String semantic;

  const _StepButton({
    required this.icon,
    required this.onTap,
    required this.width,
    required this.color,
    required this.semantic,
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: semantic,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppRadius.xs),
        child: SizedBox(
          width: width,
          height: double.infinity,
          child: Icon(icon, size: 18, color: onTap == null ? color.fade(0.5) : color),
        ),
      ),
    );
  }
}
