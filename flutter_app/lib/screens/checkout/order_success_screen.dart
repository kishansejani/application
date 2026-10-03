import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../main_navigation_screen.dart';
import '../orders/order_track_screen.dart';

class OrderSuccessScreen extends StatefulWidget {
  final String orderNumber;
  final double totalAmount;
  final String? deliverySlot;
  final String deliveryType;
  final String paymentMethod;

  const OrderSuccessScreen({
    super.key,
    required this.orderNumber,
    required this.totalAmount,
    this.deliverySlot,
    this.deliveryType = 'two_hours',
    this.paymentMethod = 'cod',
  });

  @override
  State<OrderSuccessScreen> createState() => _OrderSuccessScreenState();
}

class _OrderSuccessScreenState extends State<OrderSuccessScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _circle;
  late final Animation<double> _check;
  late final Animation<double> _ripple;
  late final Animation<double> _content;

  @override
  void initState() {
    super.initState();
    HapticFeedback.mediumImpact();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1600));
    _circle = CurvedAnimation(parent: _controller, curve: const Interval(0, 0.45, curve: Curves.elasticOut));
    _check = CurvedAnimation(parent: _controller, curve: const Interval(0.3, 0.6, curve: Curves.easeOutBack));
    _ripple = CurvedAnimation(parent: _controller, curve: const Interval(0.2, 1, curve: Curves.easeOut));
    _content = CurvedAnimation(parent: _controller, curve: const Interval(0.45, 1, curve: Curves.easeOut));
    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _goHome() => MainNavigationScreen.goToTab(context, MainNavigationScreen.homeTab);

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final express = widget.deliveryType == 'two_hours' || widget.deliveryType == '2_hours';

    return PopScope<Object?>(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) _goHome();
      },
      child: Scaffold(
        body: SafeArea(
          child: MaxWidthBox(
            maxWidth: 520,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                children: [
                  const SizedBox(height: AppSpacing.xxl),
                  SizedBox(
                    width: 180,
                    height: 180,
                    child: AnimatedBuilder(
                      animation: _controller,
                      builder: (context, _) {
                        return Stack(
                          alignment: Alignment.center,
                          children: [
                            Transform.scale(
                              scale: 0.6 + _ripple.value * 0.4,
                              child: Container(
                                width: 180,
                                height: 180,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.success.fade(0.12 * (1 - _ripple.value) + 0.04),
                                ),
                              ),
                            ),
                            Transform.scale(
                              scale: _circle.value,
                              child: Container(
                                width: 112,
                                height: 112,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  gradient: const LinearGradient(
                                    colors: [AppColors.primary, AppColors.primaryDark],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  boxShadow: [
                                    BoxShadow(color: AppColors.primary.fade(0.35), blurRadius: 24, offset: const Offset(0, 10)),
                                  ],
                                ),
                                child: Transform.scale(
                                  scale: _check.value.clamp(0.0, 1.2).toDouble(),
                                  child: const Icon(Icons.check_rounded, color: Colors.white, size: 64),
                                ),
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  FadeTransition(
                    opacity: _content,
                    child: Column(
                      children: [
                        Text(
                          context.tr('order_success'),
                          textAlign: TextAlign.center,
                          style: context.textStyles.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
                        ),
                        const SizedBox(height: AppSpacing.sm),
                        Text(
                          context.tr('order_success_msg'),
                          textAlign: TextAlign.center,
                          style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant, height: 1.5),
                        ),
                        const SizedBox(height: AppSpacing.xl),
                        AppCard(
                          child: Column(
                            children: [
                              _InfoRow(
                                icon: Icons.tag_rounded,
                                label: context.tr('order_number'),
                                value: widget.orderNumber,
                                onCopy: () => AppActions.copyToClipboard(context, widget.orderNumber),
                              ),
                              const Divider(height: AppSpacing.xl),
                              _InfoRow(
                                icon: Icons.account_balance_wallet_rounded,
                                label: context.tr('amount'),
                                value: Fmt.price(widget.totalAmount),
                              ),
                              const Divider(height: AppSpacing.xl),
                              _InfoRow(
                                icon: Icons.payments_rounded,
                                label: context.tr('payment_method'),
                                value: context.tr(widget.paymentMethod),
                              ),
                              const Divider(height: AppSpacing.xl),
                              _InfoRow(
                                icon: express ? Icons.bolt_rounded : Icons.schedule_rounded,
                                label: context.tr(express ? 'delivery_within_2_hours' : 'delivery_next_day'),
                                value: widget.deliverySlot ?? '',
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: AppSpacing.xl),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton.icon(
                            onPressed: () => Navigator.of(context).pushReplacement(
                              MaterialPageRoute(builder: (_) => OrderTrackScreen(orderNumber: widget.orderNumber)),
                            ),
                            icon: const Icon(Icons.local_shipping_rounded),
                            label: Text(context.tr('track_order')),
                          ),
                        ),
                        const SizedBox(height: AppSpacing.md),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => AppActions.openInvoice(context, widget.orderNumber),
                                icon: const Icon(Icons.receipt_long_rounded),
                                label: Text(context.tr('download_invoice')),
                              ),
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _goHome,
                                icon: const Icon(Icons.storefront_rounded),
                                label: Text(context.tr('continue_shopping')),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final VoidCallback? onCopy;

  const _InfoRow({required this.icon, required this.label, required this.value, this.onCopy});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Row(
      children: [
        Icon(icon, size: 20, color: scheme.primary),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: context.textStyles.labelMedium?.copyWith(color: scheme.onSurfaceVariant)),
              if (value.isNotEmpty)
                Text(value, style: context.textStyles.bodyLarge?.copyWith(fontWeight: FontWeight.w700)),
            ],
          ),
        ),
        if (onCopy != null)
          IconButton(
            tooltip: context.tr('copy'),
            icon: const Icon(Icons.content_copy_rounded, size: 20),
            onPressed: onCopy,
          ),
      ],
    );
  }
}
