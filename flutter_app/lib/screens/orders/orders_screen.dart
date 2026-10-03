import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../models/order.dart';
import '../../providers/auth_provider.dart';
import '../../providers/order_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/shimmer_box.dart';
import '../../widgets/status_chip.dart';
import '../main_navigation_screen.dart';
import 'order_detail_screen.dart';
import 'order_track_screen.dart';

enum _OrderFilter { all, active, delivered, cancelled }

/// Orders tab (or pushed standalone from the drawer / profile).
class OrdersScreen extends StatefulWidget {
  final bool standalone;

  const OrdersScreen({super.key, this.standalone = false});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  final _scrollController = ScrollController();
  _OrderFilter _filter = _OrderFilter.all;
  bool? _lastAuth;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(() {
      if (!_scrollController.hasClients) return;
      final pos = _scrollController.position;
      if (pos.pixels >= pos.maxScrollExtent - 400) context.read<OrderProvider>().loadMore();
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _syncWithAuth(bool isAuthenticated) {
    if (_lastAuth == isAuthenticated) return;
    _lastAuth = isAuthenticated;
    if (isAuthenticated) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        final orders = context.read<OrderProvider>();
        if (!orders.hasLoaded && !orders.isLoading) orders.fetchOrders();
      });
    }
  }

  List<Order> _filtered(List<Order> orders) {
    return switch (_filter) {
      _OrderFilter.active => orders.where((o) => o.isActive).toList(),
      _OrderFilter.delivered => orders.where((o) => o.isDelivered).toList(),
      _OrderFilter.cancelled => orders.where((o) => o.isCancelled).toList(),
      _OrderFilter.all => orders,
    };
  }

  @override
  Widget build(BuildContext context) {
    final isAuth = context.select<AuthProvider, bool>((a) => a.isAuthenticated);
    _syncWithAuth(isAuth);
    final provider = context.watch<OrderProvider>();

    Widget body;
    if (!isAuth) {
      body = const LoginRequiredState(icon: Icons.receipt_long_outlined);
    } else if (provider.isLoading && !provider.hasLoaded) {
      body = const ListSkeleton(itemCount: 5, itemHeight: 150);
    } else if (provider.error != null && !provider.hasLoaded) {
      body = ErrorState(error: provider.error, onRetry: provider.fetchOrders);
    } else if (provider.orders.isEmpty) {
      body = RefreshIndicator(
        onRefresh: provider.fetchOrders,
        child: EmptyState(
          icon: Icons.receipt_long_outlined,
          title: context.tr('empty_orders'),
          message: context.tr('empty_orders_msg'),
          actionLabel: context.tr('start_shopping'),
          actionIcon: Icons.storefront_rounded,
          onAction: () => MainNavigationScreen.goToTab(context, MainNavigationScreen.homeTab),
        ),
      );
    } else {
      final orders = _filtered(provider.orders);
      body = LayoutBuilder(
        builder: (context, constraints) {
          final hPad = Responsive.centeredPadding(constraints.maxWidth, maxWidth: 900);
          return Column(
            children: [
              SizedBox(
                height: 56,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: EdgeInsets.symmetric(horizontal: hPad, vertical: AppSpacing.sm),
                  children: [
                    for (final f in _OrderFilter.values) ...[
                      ChoiceChip(
                        label: Text(context.tr('filter_${f.name}')),
                        selected: _filter == f,
                        onSelected: (_) => setState(() => _filter = f),
                      ),
                      const SizedBox(width: AppSpacing.sm),
                    ],
                  ],
                ),
              ),
              Expanded(
                child: RefreshIndicator(
                  onRefresh: provider.fetchOrders,
                  child: orders.isEmpty
                      ? EmptyState(icon: Icons.inbox_rounded, title: context.tr('no_orders_filter'))
                      : ListView.separated(
                          controller: _scrollController,
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: EdgeInsets.fromLTRB(hPad, AppSpacing.xs, hPad, AppSpacing.xxl),
                          itemCount: orders.length + (provider.isLoadingMore ? 1 : 0),
                          separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                          itemBuilder: (context, i) {
                            if (i >= orders.length) {
                              return const Center(
                                child: Padding(padding: EdgeInsets.all(AppSpacing.lg), child: CircularProgressIndicator()),
                              );
                            }
                            return _OrderCard(order: orders[i]);
                          },
                        ),
                ),
              ),
            ],
          );
        },
      );
    }

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: widget.standalone,
        title: Text(context.tr('my_orders')),
        actions: [
          IconButton(
            tooltip: context.tr('track_order'),
            icon: const Icon(Icons.manage_search_rounded),
            onPressed: () => AppActions.promptTrackOrder(context),
          ),
        ],
      ),
      body: body,
    );
  }
}

class _OrderCard extends StatelessWidget {
  final Order order;
  const _OrderCard({required this.order});

  void _openDetail(BuildContext context) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderNumber: order.orderNumber)));
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final more = order.itemsCount - 1;
    return AppCard(
      onTap: () => _openDetail(context),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '#${order.orderNumber}',
                      style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 2),
                    Text(order.createdAt, style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant)),
                  ],
                ),
              ),
              StatusChip(status: order.orderStatus, label: order.statusLocalized),
            ],
          ),
          const Padding(padding: EdgeInsets.symmetric(vertical: AppSpacing.md), child: Divider()),
          Row(
            children: [
              AppNetworkImage(
                url: order.firstItemImage,
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
                    Text(
                      order.firstItemName ?? context.tr('items_count', {'count': '${order.itemsCount}'}),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: context.textStyles.bodyMedium?.copyWith(fontWeight: FontWeight.w700),
                    ),
                    if (more > 0)
                      Text(
                        context.tr('and_more_items', {'count': '$more'}),
                        style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(order.isExpress ? Icons.bolt_rounded : Icons.schedule_rounded, size: 14, color: scheme.primary),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            order.deliverySlot ?? context.tr(order.isExpress ? 'slot_express' : 'slot_next_day'),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: context.textStyles.labelSmall?.copyWith(color: scheme.onSurfaceVariant),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: AppSpacing.sm),
              Text(Fmt.price(order.totalAmount), style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Row(
            children: [
              if (order.isActive)
                Expanded(
                  child: FilledButton.tonalIcon(
                    style: FilledButton.styleFrom(minimumSize: const Size(0, 42)),
                    onPressed: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => OrderTrackScreen(orderNumber: order.orderNumber)),
                    ),
                    icon: const Icon(Icons.local_shipping_rounded, size: 18),
                    label: Text(context.tr('track_order')),
                  ),
                )
              else
                Expanded(
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(minimumSize: const Size(0, 42)),
                    onPressed: () => AppActions.openInvoice(context, order.orderNumber),
                    icon: const Icon(Icons.receipt_long_rounded, size: 18),
                    label: Text(context.tr('invoice')),
                  ),
                ),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(minimumSize: const Size(0, 42)),
                  onPressed: () => _openDetail(context),
                  child: Text(context.tr('view_details')),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
