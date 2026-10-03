import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/cart_item.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/delivery_slot_banner.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/price_text.dart';
import '../../widgets/qty_stepper.dart';
import '../../widgets/shimmer_box.dart';
import '../catalog/product_detail_screen.dart';
import '../checkout/checkout_screen.dart';
import '../main_navigation_screen.dart';

/// Cart tab. Also pushed standalone from the product page (`standalone: true`).
class CartScreen extends StatefulWidget {
  final bool standalone;

  const CartScreen({super.key, this.standalone = false});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  bool? _lastAuth;

  void _syncWithAuth(bool isAuthenticated) {
    if (_lastAuth == isAuthenticated) return;
    _lastAuth = isAuthenticated;
    if (isAuthenticated) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        final cart = context.read<CartProvider>();
        if (!cart.hasLoaded && !cart.isLoading) cart.fetchCart();
      });
    }
  }

  Future<void> _remove(CartItem item) async {
    final cart = context.read<CartProvider>();
    try {
      final removed = await cart.removeItem(item.productId);
      if (!mounted || removed == null) return;
      showAppSnack(
        context,
        context.tr('item_removed', {'name': removed.name}),
        action: SnackBarAction(
          label: context.tr('undo'),
          onPressed: () => cart.addToCart(removed.toProduct(), quantity: removed.quantity),
        ),
      );
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  void _continueShopping() => MainNavigationScreen.goToTab(context, MainNavigationScreen.homeTab);

  @override
  Widget build(BuildContext context) {
    final isAuth = context.select<AuthProvider, bool>((a) => a.isAuthenticated);
    _syncWithAuth(isAuth);
    final cart = context.watch<CartProvider>();

    Widget body;
    if (!isAuth) {
      body = const LoginRequiredState(icon: Icons.shopping_bag_outlined);
    } else if (cart.isLoading && !cart.hasLoaded) {
      body = const ListSkeleton(itemCount: 4, itemHeight: 104);
    } else if (cart.error != null && !cart.hasLoaded) {
      body = ErrorState(error: cart.error, onRetry: cart.fetchCart);
    } else if (cart.isEmpty) {
      body = RefreshIndicator(
        onRefresh: cart.fetchCart,
        child: EmptyState(
          icon: Icons.shopping_basket_outlined,
          title: context.tr('empty_cart'),
          message: context.tr('empty_cart_msg'),
          actionLabel: context.tr('start_shopping'),
          actionIcon: Icons.storefront_rounded,
          onAction: _continueShopping,
        ),
      );
    } else {
      body = _CartContent(cart: cart, onRemove: _remove);
    }

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: widget.standalone,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(context.tr('my_cart')),
            if (isAuth && cart.itemCount > 0)
              Text(
                context.tr('items_count', {'count': '${cart.itemCount}'}),
                style: context.textStyles.bodySmall?.copyWith(color: context.colors.onSurfaceVariant),
              ),
          ],
        ),
      ),
      body: body,
      bottomNavigationBar: isAuth && !cart.isEmpty && !Responsive.useRail(context) ? _CheckoutBar(cart: cart) : null,
    );
  }
}

class _CartContent extends StatelessWidget {
  final CartProvider cart;
  final Future<void> Function(CartItem item) onRemove;

  const _CartContent({required this.cart, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final wide = constraints.maxWidth >= Breakpoints.medium;
        final hPad = Responsive.centeredPadding(constraints.maxWidth);

        final list = <Widget>[
          if (cart.amountForFreeDelivery > 0) _FreeDeliveryProgress(cart: cart) else const _FreeDeliveryUnlocked(),
          const SizedBox(height: AppSpacing.md),
          DeliverySlotBanner(
            compact: true,
            slotInfo: cart.deliveryType == null ? null : {'type': cart.deliveryType, 'slot': cart.deliverySlotText},
          ),
          const SizedBox(height: AppSpacing.lg),
          for (final item in cart.items) ...[
            _CartItemTile(item: item, onRemove: () => onRemove(item)),
            const SizedBox(height: AppSpacing.md),
          ],
          if (!wide) ...[
            const SizedBox(height: AppSpacing.sm),
            BillSummaryCard(cart: cart),
          ],
          const SizedBox(height: AppSpacing.xl),
        ];

        final listView = RefreshIndicator(
          onRefresh: () => cart.fetchCart(silent: true),
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(wide ? AppSpacing.xl : hPad, AppSpacing.sm, wide ? AppSpacing.lg : hPad, AppSpacing.lg),
            children: list,
          ),
        );

        if (!wide) return listView;

        return MaxWidthBox(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: listView),
              SizedBox(
                width: 380,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(0, AppSpacing.sm, AppSpacing.xl, AppSpacing.xl),
                  child: Column(
                    children: [
                      BillSummaryCard(cart: cart),
                      const SizedBox(height: AppSpacing.lg),
                      _CheckoutButton(cart: cart),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _CartItemTile extends StatelessWidget {
  final CartItem item;
  final VoidCallback onRemove;

  const _CartItemTile({required this.item, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final busy = cart.isBusy(item.productId);
    final scheme = context.colors;

    return Dismissible(
      key: ValueKey('cart-${item.productId}'),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl),
        decoration: BoxDecoration(
          color: scheme.errorContainer,
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(context.tr('remove'), style: TextStyle(color: scheme.onErrorContainer, fontWeight: FontWeight.w700)),
            const SizedBox(width: AppSpacing.sm),
            Icon(Icons.delete_rounded, color: scheme.onErrorContainer),
          ],
        ),
      ),
      onDismissed: (_) => onRemove(),
      child: AppCard(
        padding: const EdgeInsets.all(AppSpacing.md),
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ProductDetailScreen(product: item.toProduct())),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AppNetworkImage(
              url: item.image,
              width: 76,
              height: 76,
              borderRadius: BorderRadius.circular(AppRadius.sm),
              backgroundColor: scheme.surfaceContainerLow,
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 2),
                  Text(item.unit, style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant)),
                  const SizedBox(height: AppSpacing.sm),
                  Row(
                    children: [
                      Expanded(child: PriceText(price: item.unitPrice, strikePrice: item.mrp, fontSize: 14)),
                      QtyStepper(
                        quantity: item.quantity,
                        loading: busy,
                        onDecrement: () {
                          if (item.quantity <= 1) {
                            onRemove();
                          } else {
                            AppActions.setQuantity(context, item.productId, item.quantity - 1);
                          }
                        },
                        onIncrement: item.canIncrement
                            ? () => AppActions.setQuantity(context, item.productId, item.quantity + 1)
                            : null,
                      ),
                    ],
                  ),
                  if (!item.canIncrement) ...[
                    const SizedBox(height: 4),
                    Text(
                      context.tr('max_stock_reached'),
                      style: context.textStyles.labelSmall?.copyWith(color: AppColors.warning),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FreeDeliveryProgress extends StatelessWidget {
  final CartProvider cart;
  const _FreeDeliveryProgress({required this.cart});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return AppCard(
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.local_shipping_rounded, color: scheme.primary, size: 20),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Text(
                  context.tr('add_more_free_delivery', {'amount': Fmt.price(cart.amountForFreeDelivery)}),
                  style: context.textStyles.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.sm),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppRadius.pill),
            child: LinearProgressIndicator(
              value: cart.freeDeliveryProgress,
              minHeight: 8,
              backgroundColor: scheme.surfaceContainerHighest,
            ),
          ),
        ],
      ),
    );
  }
}

class _FreeDeliveryUnlocked extends StatelessWidget {
  const _FreeDeliveryUnlocked();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.success.fade(context.isDark ? 0.2 : 0.1),
        borderRadius: BorderRadius.circular(AppRadius.md),
      ),
      child: Row(
        children: [
          const Icon(Icons.emoji_events_rounded, color: AppColors.success, size: 20),
          const SizedBox(width: AppSpacing.sm),
          Expanded(
            child: Text(
              context.tr('free_delivery_unlocked'),
              style: context.textStyles.bodyMedium?.copyWith(fontWeight: FontWeight.w700, color: AppColors.success),
            ),
          ),
        ],
      ),
    );
  }
}

/// Bill details card (cart + checkout).
class BillSummaryCard extends StatelessWidget {
  final CartProvider cart;
  const BillSummaryCard({super.key, required this.cart});

  @override
  Widget build(BuildContext context) {
    final itemSavings = cart.totalSavings - cart.discountAmount;
    return AppCard(
      child: Column(
        children: [
          CardTitle(title: context.tr('bill_details'), icon: Icons.receipt_rounded),
          const SizedBox(height: AppSpacing.lg),
          _BillRow(label: context.tr('item_total'), value: Fmt.price(cart.subtotal)),
          if (itemSavings > 0)
            _BillRow(label: context.tr('product_discount'), value: '-${Fmt.price(itemSavings)}', valueColor: AppColors.success),
          if (cart.discountAmount > 0)
            _BillRow(
              label: '${context.tr('coupon_discount')} (${cart.couponCode})',
              value: '-${Fmt.price(cart.discountAmount)}',
              valueColor: AppColors.success,
            ),
          _BillRow(
            label: context.tr('delivery_fee'),
            value: cart.deliveryCharge == 0 ? context.tr('free') : Fmt.price(cart.deliveryCharge),
            valueColor: cart.deliveryCharge == 0 ? AppColors.success : null,
          ),
          const Padding(padding: EdgeInsets.symmetric(vertical: AppSpacing.sm), child: Divider()),
          _BillRow(label: context.tr('to_pay'), value: Fmt.price(cart.grandTotal), bold: true),
          if (cart.totalSavings > 0) ...[
            const SizedBox(height: AppSpacing.sm),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: AppSpacing.sm, horizontal: AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.success.fade(context.isDark ? 0.2 : 0.1),
                borderRadius: BorderRadius.circular(AppRadius.sm),
              ),
              child: Text(
                context.tr('total_savings', {'amount': Fmt.price(cart.totalSavings)}),
                textAlign: TextAlign.center,
                style: TextStyle(color: context.isDark ? AppColors.primaryOnDark : AppColors.primaryDark, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _BillRow extends StatelessWidget {
  final String label;
  final String value;
  final bool bold;
  final Color? valueColor;

  const _BillRow({required this.label, required this.value, this.bold = false, this.valueColor});

  @override
  Widget build(BuildContext context) {
    final style = bold
        ? context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800)
        : context.textStyles.bodyMedium?.copyWith(color: context.colors.onSurfaceVariant);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          Text(
            value,
            style: (bold ? style : context.textStyles.bodyMedium?.copyWith(fontWeight: FontWeight.w700))
                ?.copyWith(color: valueColor),
          ),
        ],
      ),
    );
  }
}

class _CheckoutButton extends StatelessWidget {
  final CartProvider cart;
  const _CheckoutButton({required this.cart});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: FilledButton(
        onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CheckoutScreen())),
        child: Row(
          children: [
            Text(Fmt.price(cart.grandTotal), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
            const Spacer(),
            Text(context.tr('checkout')),
            const SizedBox(width: 4),
            const Icon(Icons.arrow_forward_rounded, size: 20),
          ],
        ),
      ),
    );
  }
}

class _CheckoutBar extends StatelessWidget {
  final CartProvider cart;
  const _CheckoutBar({required this.cart});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Container(
      decoration: BoxDecoration(
        color: scheme.surface,
        border: Border(top: BorderSide(color: scheme.outlineVariant.fade(0.6))),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
          child: _CheckoutButton(cart: cart),
        ),
      ),
    );
  }
}
