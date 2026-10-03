import 'package:flutter/material.dart';

enum PrimaryButtonVariant { filled, tonal, outlined }

/// Full-width M3 button with loading state.
class PrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? icon;
  final bool expanded;
  final PrimaryButtonVariant variant;
  final Color? color;

  const PrimaryButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.icon,
    this.expanded = true,
    this.variant = PrimaryButtonVariant.filled,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final spinnerColor = variant == PrimaryButtonVariant.filled ? scheme.onPrimary : scheme.primary;
    final Widget child = isLoading
        ? SizedBox(
            width: 22,
            height: 22,
            child: CircularProgressIndicator(strokeWidth: 2.4, color: spinnerColor),
          )
        : Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (icon != null) ...[Icon(icon, size: 20), const SizedBox(width: 8)],
              Flexible(child: Text(label, overflow: TextOverflow.ellipsis)),
            ],
          );

    final action = isLoading ? null : onPressed;
    Widget button;
    switch (variant) {
      case PrimaryButtonVariant.filled:
        button = FilledButton(
          onPressed: action,
          style: color != null ? FilledButton.styleFrom(backgroundColor: color) : null,
          child: child,
        );
        break;
      case PrimaryButtonVariant.tonal:
        button = FilledButton.tonal(onPressed: action, child: child);
        break;
      case PrimaryButtonVariant.outlined:
        button = OutlinedButton(onPressed: action, child: child);
        break;
    }
    return expanded ? SizedBox(width: double.infinity, child: button) : button;
  }
}
