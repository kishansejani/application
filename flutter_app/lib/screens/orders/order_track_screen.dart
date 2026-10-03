import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/order.dart';
import '../../providers/order_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../../widgets/error_state.dart';
import '../../widgets/order_timeline.dart';
import '../../widgets/shimmer_box.dart';
import 'order_detail_screen.dart';

/// Native tracking screen (uses `GET /orders/{orderNumber}`), auto-refreshing
/// every 30 seconds while the order is active. A button opens the public web
/// tracking page in the in-app WebView.
class OrderTrackScreen extends StatefulWidget {
  final String orderNumber;

  const OrderTrackScreen({super.key, required this.orderNumber});

  @override
  State<OrderTrackScreen> createState() => _OrderTrackScreenState();
}

class _OrderTrackScreenState extends State<OrderTrackScreen> {
  Order? _order;
  Object? _error;
  bool _loading = true;
  Timer? _poller;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
    _poller = Timer.periodic(const Duration(seconds: 30), (_) {
      if (mounted && (_order?.isActive ?? false)) _load(silent: true);
    });
  }

  @override
  void dispose() {
    _poller?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _loading = _order == null;
        _error = null;
      });
    }
    try {
      final order = await context.read<OrderProvider>().fetchOrder(widget.orderNumber);
      if (!mounted) return;
      setState(() {
        _order = order;
        _loading = false;
      });
    } catch (e) {
      if (!mounted || silent) return;
      setState(() {
        _error = e;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final order = _order;
    Widget body;
    if (_loading) {
      body = const ListSkeleton(itemCount: 3, itemHeight: 160);
    } else if (order == null) {
      body = ErrorState(error: _error, onRetry: _load);
    } else {
      body = RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.xxl),
          children: [
            MaxWidthBox(
              maxWidth: 640,
              child: Column(
                children: [
                  _StatusHero(order: order),
                  const SizedBox(height: AppSpacing.md),
                  AppCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CardTitle(title: context.tr('tracking_timeline'), icon: Icons.timeline_rounded),
                        const SizedBox(height: AppSpacing.lg),
                        OrderTimeline(
                          status: order.orderStatus,
                          placedAt: order.createdAt,
                          estimatedAt: order.estimatedDeliveryAt,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  AppCard(
                    child: Row(
                      children: [
                        Icon(Icons.location_on_rounded, color: context.colors.primary),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: Text(
                            order.deliveryAddress ?? '-',
                            style: context.textStyles.bodyMedium?.copyWith(height: 1.4),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.tonalIcon(
                      onPressed: () => AppActions.openWebTracking(context, order.orderNumber),
                      icon: const Icon(Icons.open_in_browser_rounded),
                      label: Text(context.tr('web_tracking')),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => OrderDetailScreen(orderNumber: order.orderNumber)),
                      ),
                      icon: const Icon(Icons.receipt_long_rounded),
                      label: Text(context.tr('view_details')),
                    ),
                  ),
                  if (order.isActive) ...[
                    const SizedBox(height: AppSpacing.lg),
                    Text(
                      context.tr('auto_refresh_note'),
                      textAlign: TextAlign.center,
                      style: context.textStyles.labelSmall?.copyWith(color: context.colors.onSurfaceVariant),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('track_order')),
        actions: [
          IconButton(
            tooltip: context.tr('refresh'),
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _load,
          ),
        ],
      ),
      body: body,
    );
  }
}

class _StatusHero extends StatelessWidget {
  final Order order;
  const _StatusHero({required this.order});

  @override
  Widget build(BuildContext context) {
    final color = AppColors.statusColor(order.orderStatus);
    final IconData icon;
    switch (order.orderStatus) {
      case 'delivered':
        icon = Icons.home_rounded;
        break;
      case 'cancelled':
        icon = Icons.cancel_rounded;
        break;
      case 'out_for_delivery':
        icon = Icons.delivery_dining_rounded;
        break;
      case 'processing':
        icon = Icons.inventory_2_rounded;
        break;
      default:
        icon = Icons.receipt_long_rounded;
    }
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.xl),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [color, Color.lerp(color, Colors.black, 0.25) ?? color],
        ),
        borderRadius: BorderRadius.circular(AppRadius.lg),
      ),
      child: Row(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(color: Colors.white.fade(0.2), shape: BoxShape.circle),
            child: Icon(icon, color: Colors.white, size: 34),
          ),
          const SizedBox(width: AppSpacing.lg),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  order.statusLocalized ?? context.tr('status_${order.orderStatus}'),
                  style: context.textStyles.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 4),
                Text(
                  '#${order.orderNumber} • ${Fmt.price(order.totalAmount)}',
                  style: context.textStyles.bodySmall?.copyWith(color: Colors.white.fade(0.9)),
                ),
                if (order.isActive && order.estimatedDeliveryAt != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    context.tr('expected_by', {'time': order.estimatedDeliveryAt!}),
                    style: context.textStyles.bodySmall?.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
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
