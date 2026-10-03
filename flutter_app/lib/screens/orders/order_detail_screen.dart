import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/order.dart';
import '../../providers/order_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/error_state.dart';
import '../../widgets/order_timeline.dart';
import '../../widgets/shimmer_box.dart';
import '../../widgets/status_chip.dart';
import 'order_track_screen.dart';

class OrderDetailScreen extends StatefulWidget {
  final String orderNumber;

  const OrderDetailScreen({super.key, required this.orderNumber});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  Order? _order;
  Object? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    setState(() {
      _loading = _order == null;
      _error = null;
    });
    try {
      final order = await context.read<OrderProvider>().fetchOrder(widget.orderNumber);
      if (!mounted) return;
      setState(() {
        _order = order;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
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
      body = const ListSkeleton(itemCount: 4, itemHeight: 140);
    } else if (order == null) {
      body = ErrorState(error: _error, onRetry: _load);
    } else {
      body = RefreshIndicator(
        onRefresh: _load,
        child: LayoutBuilder(
          builder: (context, constraints) {
            final wide = constraints.maxWidth >= Breakpoints.medium;
            final hPad = Responsive.centeredPadding(constraints.maxWidth, maxWidth: wide ? 1100 : 720);
            final left = <Widget>[
              _header(order),
              const SizedBox(height: AppSpacing.md),
              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CardTitle(title: context.tr('order_status'), icon: Icons.timeline_rounded),
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
              _deliveryCard(order),
            ];
            final right = <Widget>[
              _itemsCard(order),
              const SizedBox(height: AppSpacing.md),
              _billCard(order),
              const SizedBox(height: AppSpacing.md),
              _actions(order),
            ];
            if (!wide) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.xxl),
                children: [...left, const SizedBox(height: AppSpacing.md), ...right],
              );
            }
            return SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.xxl),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(child: Column(children: left)),
                  const SizedBox(width: AppSpacing.lg),
                  Expanded(child: Column(children: right)),
                ],
              ),
            );
          },
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('order_details')),
        actions: [
          if (order != null)
            IconButton(
              tooltip: context.tr('invoice'),
              icon: const Icon(Icons.receipt_long_rounded),
              onPressed: () => AppActions.openInvoice(context, order.orderNumber),
            ),
          const SizedBox(width: AppSpacing.xs),
        ],
      ),
      body: body,
    );
  }

  Widget _header(Order order) {
    final scheme = context.colors;
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '#${order.orderNumber}',
                  style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
                ),
              ),
              IconButton(
                tooltip: context.tr('copy'),
                icon: const Icon(Icons.content_copy_rounded, size: 18),
                onPressed: () => AppActions.copyToClipboard(context, order.orderNumber),
              ),
            ],
          ),
          Text(
            '${context.tr('placed_on')} ${order.createdAt}',
            style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
          ),
          if (order.invoiceNumber != null)
            Text(
              '${context.tr('invoice_no')}: ${order.invoiceNumber}',
              style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
            ),
          const SizedBox(height: AppSpacing.md),
          Wrap(
            spacing: AppSpacing.sm,
            runSpacing: AppSpacing.sm,
            children: [
              StatusChip(status: order.orderStatus, label: order.statusLocalized),
              StatusChip(
                status: order.paymentStatus,
                color: order.paymentStatus == 'paid' ? AppColors.success : AppColors.warning,
                label: '${context.tr(order.paymentMethod)} • ${context.tr('payment_${order.paymentStatus}')}',
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _deliveryCard(Order order) {
    final scheme = context.colors;
    final phone = order.customerPhone;
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CardTitle(title: context.tr('delivery_details'), icon: Icons.local_shipping_rounded),
          const SizedBox(height: AppSpacing.lg),
          _iconLine(
            order.isExpress ? Icons.bolt_rounded : Icons.schedule_rounded,
            order.deliverySlot ?? context.tr(order.isExpress ? 'slot_express' : 'slot_next_day'),
          ),
          if (order.estimatedDeliveryAt != null)
            _iconLine(Icons.event_available_rounded, '${context.tr('estimated_delivery')}: ${order.estimatedDeliveryAt}'),
          if (order.deliveryAddress != null) _iconLine(Icons.location_on_rounded, order.deliveryAddress!),
          if (order.customerName != null || phone != null)
            Row(
              children: [
                Expanded(
                  child: _iconLine(
                    Icons.person_rounded,
                    [order.customerName, if (phone != null) Fmt.phone(phone)].whereType<String>().join(' • '),
                  ),
                ),
                if (phone != null)
                  IconButton.filledTonal(
                    tooltip: context.tr('call'),
                    icon: const Icon(Icons.call_rounded, size: 18),
                    onPressed: () => launchUrl(Uri(scheme: 'tel', path: phone)),
                  ),
              ],
            ),
          if (order.notes != null) _iconLine(Icons.notes_rounded, order.notes!, color: scheme.onSurfaceVariant),
        ],
      ),
    );
  }

  Widget _iconLine(IconData icon, String text, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.sm),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: context.colors.primary),
          const SizedBox(width: AppSpacing.md),
          Expanded(child: Text(text, style: context.textStyles.bodyMedium?.copyWith(height: 1.4, color: color))),
        ],
      ),
    );
  }

  Widget _itemsCard(Order order) {
    final scheme = context.colors;
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CardTitle(
            title: context.tr('order_items_count', {'count': '${order.items.length}'}),
            icon: Icons.shopping_bag_rounded,
          ),
          const SizedBox(height: AppSpacing.md),
          for (final item in order.items)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 6),
              child: Row(
                children: [
                  AppNetworkImage(
                    url: item.image,
                    width: 52,
                    height: 52,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    backgroundColor: scheme.surfaceContainerLow,
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item.name, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700)),
                        Text(
                          '${item.unit.isNotEmpty ? '${item.unit} • ' : ''}${Fmt.price(item.unitPrice)} × ${item.quantity}',
                          style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                        ),
                      ],
                    ),
                  ),
                  Text(Fmt.price(item.totalPrice), style: const TextStyle(fontWeight: FontWeight.w800)),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _billCard(Order order) {
    return AppCard(
      child: Column(
        children: [
          CardTitle(title: context.tr('bill_details'), icon: Icons.receipt_rounded),
          const SizedBox(height: AppSpacing.md),
          _billRow(context.tr('item_total'), Fmt.price(order.subtotal)),
          if (order.discountAmount > 0)
            _billRow(context.tr('coupon_discount'), '-${Fmt.price(order.discountAmount)}', color: AppColors.success),
          _billRow(
            context.tr('delivery_fee'),
            order.deliveryCharge == 0 ? context.tr('free') : Fmt.price(order.deliveryCharge),
            color: order.deliveryCharge == 0 ? AppColors.success : null,
          ),
          const Padding(padding: EdgeInsets.symmetric(vertical: AppSpacing.sm), child: Divider()),
          _billRow(context.tr('total_paid'), Fmt.price(order.totalAmount), bold: true),
        ],
      ),
    );
  }

  Widget _billRow(String label, String value, {bool bold = false, Color? color}) {
    final style = bold
        ? context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800)
        : context.textStyles.bodyMedium?.copyWith(color: context.colors.onSurfaceVariant);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          Text(value, style: (bold ? style : const TextStyle(fontWeight: FontWeight.w700))?.copyWith(color: color)),
        ],
      ),
    );
  }

  Widget _actions(Order order) {
    return Column(
      children: [
        if (order.isActive)
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => OrderTrackScreen(orderNumber: order.orderNumber)),
              ),
              icon: const Icon(Icons.local_shipping_rounded),
              label: Text(context.tr('track_order')),
            ),
          ),
        const SizedBox(height: AppSpacing.sm),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => AppActions.openInvoice(context, order.orderNumber),
                icon: const Icon(Icons.receipt_long_rounded),
                label: Text(context.tr('download_invoice')),
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => AppActions.openWebTracking(context, order.orderNumber),
                icon: const Icon(Icons.open_in_browser_rounded),
                label: Text(context.tr('web_tracking')),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
