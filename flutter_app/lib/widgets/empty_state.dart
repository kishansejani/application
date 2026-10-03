import 'package:flutter/material.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';
import '../utils/ui_helpers.dart';

/// Friendly illustration-style empty state.
class EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final IconData? actionIcon;
  final VoidCallback? onAction;
  final bool scrollable;

  const EmptyState({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.actionLabel,
    this.actionIcon,
    this.onAction,
    this.scrollable = true,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final content = Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.xxl),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 112,
            height: 112,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [scheme.primaryContainer, scheme.primaryContainer.fade(0.35)],
              ),
            ),
            child: Icon(icon, size: 52, color: scheme.onPrimaryContainer),
          ),
          const SizedBox(height: AppSpacing.xl),
          Text(
            title,
            textAlign: TextAlign.center,
            style: context.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800),
          ),
          if (message != null) ...[
            const SizedBox(height: AppSpacing.sm),
            Text(
              message!,
              textAlign: TextAlign.center,
              style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant, height: 1.5),
            ),
          ],
          if (actionLabel != null && onAction != null) ...[
            const SizedBox(height: AppSpacing.xl),
            FilledButton.icon(
              onPressed: onAction,
              icon: Icon(actionIcon ?? Icons.arrow_forward_rounded, size: 20),
              label: Text(actionLabel!),
            ),
          ],
        ],
      ),
    );

    if (!scrollable) return Center(child: content);
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: constraints.maxHeight.isFinite ? constraints.maxHeight : 0),
          child: Center(child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 480), child: content)),
        ),
      ),
    );
  }
}

/// Shown on screens that need an account (cart, orders, wishlist ...).
class LoginRequiredState extends StatelessWidget {
  final IconData icon;
  final String? message;

  const LoginRequiredState({super.key, this.icon = Icons.lock_outline_rounded, this.message});

  @override
  Widget build(BuildContext context) {
    return EmptyState(
      icon: icon,
      title: context.tr('login_required'),
      message: message ?? context.tr('login_required_msg'),
      actionLabel: context.tr('login'),
      actionIcon: Icons.login_rounded,
      onAction: () => ensureLoggedIn(context),
    );
  }
}
