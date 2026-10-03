import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/order_provider.dart';
import '../../providers/product_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/delivery_slot_banner.dart';
import '../../widgets/empty_state.dart';
import '../addresses/add_address_map_screen.dart';
import '../cart/cart_screen.dart';
import '../offers/offers_screen.dart';
import 'order_success_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _couponController = TextEditingController();
  final _notesController = TextEditingController();

  int? _selectedAddressId;
  String _paymentMethod = 'cod';
  Map<String, dynamic>? _slotInfo;
  bool _applyingCoupon = false;
  bool _placingOrder = false;
  String? _couponError;

  static const List<_PaymentOption> _paymentOptions = [
    _PaymentOption('cod', Icons.payments_rounded, 'cod', 'cod_desc'),
    _PaymentOption('upi', Icons.qr_code_2_rounded, 'upi', 'upi_desc'),
    _PaymentOption('card', Icons.credit_card_rounded, 'card', 'card_desc'),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _init());
  }

  @override
  void dispose() {
    _couponController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    final addresses = context.read<AddressProvider>();
    final products = context.read<ProductProvider>();
    if (!addresses.hasLoaded) await addresses.fetchAddresses();
    if (!mounted) return;
    setState(() => _selectedAddressId ??= addresses.defaultAddress?.id);
    try {
      final slot = await products.fetchDeliverySlot();
      if (mounted) setState(() => _slotInfo = slot);
    } catch (_) {
      if (mounted) setState(() => _slotInfo = products.deliverySlotInfo);
    }
  }

  Future<void> _applyCoupon([String? code]) async {
    final value = (code ?? _couponController.text).trim().toUpperCase();
    if (value.isEmpty) {
      setState(() => _couponError = context.tr('enter_coupon'));
      return;
    }
    FocusScope.of(context).unfocus();
    setState(() {
      _applyingCoupon = true;
      _couponError = null;
    });
    try {
      final ok = await context.read<CartProvider>().applyCoupon(value);
      if (!mounted) return;
      if (ok) {
        _couponController.clear();
        showAppSnack(context, context.tr('coupon_applied', {'code': value}), type: SnackType.success);
      } else {
        setState(() => _couponError = context.tr('invalid_coupon'));
      }
    } catch (e) {
      if (mounted) setState(() => _couponError = errorMessage(e));
    } finally {
      if (mounted) setState(() => _applyingCoupon = false);
    }
  }

  Future<void> _browseOffers() async {
    final code = await Navigator.of(context).push<String>(
      MaterialPageRoute(builder: (_) => const OffersScreen(selectMode: true)),
    );
    if (code != null && code.isNotEmpty && mounted) {
      _couponController.text = code;
      _applyCoupon(code);
    }
  }

  Future<void> _addAddress() async {
    final created = await Navigator.of(context).push<Address>(
      MaterialPageRoute(builder: (_) => const AddAddressMapScreen()),
    );
    if (!mounted) return;
    final addresses = context.read<AddressProvider>();
    setState(() => _selectedAddressId = created?.id ?? _selectedAddressId ?? addresses.defaultAddress?.id);
  }

  Future<void> _chooseAddress() async {
    final addresses = context.read<AddressProvider>().addresses;
    final picked = await showModalBottomSheet<int>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: ConstrainedBox(
          constraints: BoxConstraints(maxHeight: MediaQuery.sizeOf(ctx).height * 0.75),
          child: ListView(
            shrinkWrap: true,
            padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.lg),
            children: [
              Text(ctx.tr('select_address'), style: ctx.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
              const SizedBox(height: AppSpacing.md),
              for (final a in addresses) ...[
                _AddressOption(
                  address: a,
                  selected: a.id == _selectedAddressId,
                  onTap: () => Navigator.of(ctx).pop(a.id),
                ),
                const SizedBox(height: AppSpacing.sm),
              ],
              OutlinedButton.icon(
                onPressed: () => Navigator.of(ctx).pop(-1),
                icon: const Icon(Icons.add_location_alt_rounded),
                label: Text(ctx.tr('add_new_address')),
              ),
            ],
          ),
        ),
      ),
    );
    if (!mounted || picked == null) return;
    if (picked == -1) {
      _addAddress();
    } else {
      setState(() => _selectedAddressId = picked);
    }
  }

  Future<void> _placeOrder() async {
    final cart = context.read<CartProvider>();
    final address = context.read<AddressProvider>().byId(_selectedAddressId);
    if (address == null) {
      showAppSnack(context, context.tr('select_address_first'), type: SnackType.error);
      return;
    }
    setState(() => _placingOrder = true);
    try {
      final total = cart.grandTotal;
      final result = await context.read<OrderProvider>().placeOrder(
            addressId: address.id,
            paymentMethod: _paymentMethod,
            couponCode: cart.couponCode,
            notes: _notesController.text,
          );
      if (!mounted) return;
      cart.clearCart();
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => OrderSuccessScreen(
            orderNumber: (result['order_number'] ?? '').toString(),
            totalAmount: double.tryParse('${result['total_amount']}') ?? total,
            deliverySlot: result['delivery_slot']?.toString(),
            deliveryType: (result['delivery_type'] ?? 'two_hours').toString(),
            paymentMethod: _paymentMethod,
          ),
        ),
      );
    } catch (e) {
      if (mounted) {
        setState(() => _placingOrder = false);
        showAppSnack(context, errorMessage(e), type: SnackType.error);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final addressProvider = context.watch<AddressProvider>();
    final selected = addressProvider.byId(_selectedAddressId) ?? addressProvider.defaultAddress;
    if (selected != null && _selectedAddressId == null) _selectedAddressId = selected.id;

    if (cart.isEmpty && !_placingOrder) {
      return Scaffold(
        appBar: AppBar(title: Text(context.tr('checkout_title'))),
        body: EmptyState(
          icon: Icons.shopping_basket_outlined,
          title: context.tr('empty_cart'),
          actionLabel: context.tr('go_back'),
          onAction: () => Navigator.of(context).maybePop(),
        ),
      );
    }

    final sections = <Widget>[
      _section(context, 1, context.tr('delivery_address'), Icons.location_on_rounded, _buildAddress(addressProvider, selected)),
      _section(context, 2, context.tr('delivery_slot'), Icons.schedule_rounded, _buildSlot()),
      _section(context, 3, context.tr('apply_coupon'), Icons.local_offer_rounded, _buildCoupon(cart)),
      _section(context, 4, context.tr('payment_method'), Icons.account_balance_wallet_rounded, _buildPayment()),
      _section(context, 5, context.tr('delivery_instructions'), Icons.notes_rounded, _buildNotes()),
      _buildItemsSummary(cart),
    ];

    return Scaffold(
      appBar: AppBar(title: Text(context.tr('checkout_title'))),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final wide = constraints.maxWidth >= Breakpoints.medium;
          final hPad = Responsive.centeredPadding(constraints.maxWidth, maxWidth: Breakpoints.maxFormWidth + 80);
          if (!wide) {
            return ListView(
              padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.xxl),
              children: [
                for (final s in sections) ...[s, const SizedBox(height: AppSpacing.md)],
                BillSummaryCard(cart: cart),
              ],
            );
          }
          return MaxWidthBox(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.sm, AppSpacing.lg, AppSpacing.xxl),
                    children: [for (final s in sections) ...[s, const SizedBox(height: AppSpacing.md)]],
                  ),
                ),
                SizedBox(
                  width: 380,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(0, AppSpacing.sm, AppSpacing.xl, AppSpacing.xl),
                    child: Column(
                      children: [
                        BillSummaryCard(cart: cart),
                        const SizedBox(height: AppSpacing.lg),
                        _placeButton(cart, selected != null),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
      bottomNavigationBar: Responsive.useRail(context)
          ? null
          : Container(
              decoration: BoxDecoration(
                color: context.colors.surface,
                border: Border(top: BorderSide(color: context.colors.outlineVariant.fade(0.6))),
              ),
              child: SafeArea(
                top: false,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
                  child: _placeButton(cart, selected != null),
                ),
              ),
            ),
    );
  }

  Widget _placeButton(CartProvider cart, bool hasAddress) {
    final scheme = context.colors;
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: FilledButton(
        onPressed: (_placingOrder || !hasAddress) ? null : _placeOrder,
        child: _placingOrder
            ? SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: scheme.onPrimary))
            : Row(
                children: [
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(Fmt.price(cart.grandTotal), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                      Text(context.tr('total'), style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
                    ],
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Text(
                      hasAddress ? context.tr('place_order') : context.tr('select_address_first'),
                      textAlign: TextAlign.end,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 4),
                  const Icon(Icons.arrow_forward_rounded, size: 20),
                ],
              ),
      ),
    );
  }

  Widget _section(BuildContext context, int step, String title, IconData icon, Widget child) {
    final scheme = context.colors;
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 28,
                height: 28,
                alignment: Alignment.center,
                decoration: BoxDecoration(color: scheme.primary, shape: BoxShape.circle),
                child: Text('$step', style: TextStyle(color: scheme.onPrimary, fontWeight: FontWeight.w800, fontSize: 13)),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Text(title, style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
              ),
              Icon(icon, color: scheme.onSurfaceVariant, size: 20),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),
          child,
        ],
      ),
    );
  }

  Widget _buildAddress(AddressProvider provider, Address? selected) {
    if (provider.isLoading && !provider.hasLoaded) {
      return const Center(child: Padding(padding: EdgeInsets.all(AppSpacing.md), child: CircularProgressIndicator()));
    }
    if (selected == null) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(context.tr('no_address_msg'), style: TextStyle(color: context.colors.onSurfaceVariant)),
          const SizedBox(height: AppSpacing.md),
          FilledButton.tonalIcon(
            onPressed: _addAddress,
            icon: const Icon(Icons.add_location_alt_rounded),
            label: Text(context.tr('add_new_address')),
          ),
        ],
      );
    }
    return _AddressOption(
      address: selected,
      selected: true,
      trailing: TextButton(onPressed: _chooseAddress, child: Text(context.tr('change'))),
      onTap: _chooseAddress,
    );
  }

  Widget _buildSlot() {
    final express = DeliverySlotBanner.isExpress(_slotInfo);
    final slotText = (_slotInfo?['slot_text'] ?? _slotInfo?['slot'])?.toString();
    final rule = _slotInfo?['rule_description']?.toString();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: _SlotTile(
                icon: Icons.bolt_rounded,
                title: context.tr('slot_express'),
                subtitle: context.tr('slot_express_sub'),
                selected: express,
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: _SlotTile(
                icon: Icons.wb_sunny_rounded,
                title: context.tr('slot_next_day'),
                subtitle: context.tr('slot_next_day_sub'),
                selected: !express,
              ),
            ),
          ],
        ),
        if (slotText != null) ...[
          const SizedBox(height: AppSpacing.md),
          Row(
            children: [
              Icon(Icons.event_available_rounded, size: 18, color: context.colors.primary),
              const SizedBox(width: AppSpacing.sm),
              Expanded(child: Text(slotText, style: const TextStyle(fontWeight: FontWeight.w700))),
            ],
          ),
        ],
        const SizedBox(height: AppSpacing.sm),
        Text(
          rule ?? context.tr(express ? 'order_before_12' : 'order_after_12'),
          style: context.textStyles.bodySmall?.copyWith(color: context.colors.onSurfaceVariant),
        ),
      ],
    );
  }

  Widget _buildCoupon(CartProvider cart) {
    if (cart.couponCode != null) {
      return Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: AppColors.success.fade(context.isDark ? 0.2 : 0.08),
          borderRadius: BorderRadius.circular(AppRadius.sm),
          border: Border.all(color: AppColors.success.fade(0.4)),
        ),
        child: Row(
          children: [
            const Icon(Icons.verified_rounded, color: AppColors.success),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${cart.couponCode} ${context.tr('applied')}',
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                  Text(
                    context.tr('you_saved', {'amount': Fmt.price(cart.discountAmount)}),
                    style: context.textStyles.bodySmall?.copyWith(color: AppColors.success, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            TextButton(onPressed: cart.removeCoupon, child: Text(context.tr('remove'))),
          ],
        ),
      );
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: TextField(
                controller: _couponController,
                textCapitalization: TextCapitalization.characters,
                textInputAction: TextInputAction.done,
                onSubmitted: (_) => _applyCoupon(),
                decoration: InputDecoration(
                  hintText: context.tr('coupon_code'),
                  prefixIcon: const Icon(Icons.confirmation_number_outlined),
                  errorText: _couponError,
                  errorMaxLines: 2,
                ),
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            SizedBox(
              height: 56,
              child: FilledButton.tonal(
                onPressed: _applyingCoupon ? null : () => _applyCoupon(),
                child: _applyingCoupon
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : Text(context.tr('apply')),
              ),
            ),
          ],
        ),
        TextButton.icon(
          onPressed: _browseOffers,
          style: TextButton.styleFrom(padding: EdgeInsets.zero),
          icon: const Icon(Icons.local_offer_outlined, size: 18),
          label: Text(context.tr('view_all_offers')),
        ),
      ],
    );
  }

  Widget _buildPayment() {
    final scheme = context.colors;
    return Column(
      children: [
        for (final option in _paymentOptions) ...[
          _PaymentTile(
            option: option,
            selected: _paymentMethod == option.value,
            onTap: () => setState(() => _paymentMethod = option.value),
          ),
          const SizedBox(height: AppSpacing.sm),
        ],
        if (_paymentMethod != 'cod')
          Row(
            children: [
              Icon(Icons.info_outline_rounded, size: 16, color: scheme.onSurfaceVariant),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  context.tr('online_payment_demo'),
                  style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                ),
              ),
            ],
          ),
      ],
    );
  }

  Widget _buildNotes() {
    return TextField(
      controller: _notesController,
      maxLines: 2,
      maxLength: 200,
      decoration: InputDecoration(
        hintText: context.tr('delivery_instructions_hint'),
      ),
    );
  }

  Widget _buildItemsSummary(CartProvider cart) {
    final scheme = context.colors;
    return AppCard(
      padding: EdgeInsets.zero,
      child: Theme(
        data: context.theme.copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
          childrenPadding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.md),
          leading: Icon(Icons.shopping_bag_rounded, color: scheme.primary),
          title: Text(
            context.tr('order_items_count', {'count': '${cart.itemCount}'}),
            style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w800),
          ),
          subtitle: Text(Fmt.price(cart.subtotal)),
          children: [
            for (final item in cart.items)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  children: [
                    AppNetworkImage(url: item.image, width: 44, height: 44, borderRadius: BorderRadius.circular(AppRadius.xs)),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Text(
                        '${item.name} × ${item.quantity}',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: context.textStyles.bodyMedium,
                      ),
                    ),
                    Text(Fmt.price(item.totalPrice), style: const TextStyle(fontWeight: FontWeight.w700)),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _PaymentOption {
  final String value;
  final IconData icon;
  final String titleKey;
  final String descKey;
  const _PaymentOption(this.value, this.icon, this.titleKey, this.descKey);
}

class _PaymentTile extends StatelessWidget {
  final _PaymentOption option;
  final bool selected;
  final VoidCallback onTap;

  const _PaymentTile({required this.option, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 180),
      decoration: BoxDecoration(
        color: selected ? scheme.primaryContainer.fade(context.isDark ? 0.3 : 0.5) : Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.sm),
        border: Border.all(color: selected ? scheme.primary : scheme.outlineVariant, width: selected ? 1.6 : 1),
      ),
      child: Material(
        type: MaterialType.transparency,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.sm),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Row(
              children: [
                Icon(option.icon, color: selected ? scheme.primary : scheme.onSurfaceVariant),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(context.tr(option.titleKey), style: const TextStyle(fontWeight: FontWeight.w700)),
                      Text(
                        context.tr(option.descKey),
                        style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    ],
                  ),
                ),
                Icon(
                  selected ? Icons.radio_button_checked_rounded : Icons.radio_button_unchecked_rounded,
                  color: selected ? scheme.primary : scheme.outline,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _SlotTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final bool selected;

  const _SlotTile({required this.icon, required this.title, required this.subtitle, required this.selected});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Opacity(
      opacity: selected ? 1 : 0.55,
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: selected ? scheme.primaryContainer.fade(context.isDark ? 0.3 : 0.5) : scheme.surface,
          borderRadius: BorderRadius.circular(AppRadius.sm),
          border: Border.all(color: selected ? scheme.primary : scheme.outlineVariant, width: selected ? 1.6 : 1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: selected ? scheme.primary : scheme.onSurfaceVariant, size: 20),
                const Spacer(),
                if (selected) Icon(Icons.check_circle_rounded, color: scheme.primary, size: 18),
              ],
            ),
            const SizedBox(height: AppSpacing.sm),
            Text(title, style: const TextStyle(fontWeight: FontWeight.w800)),
            Text(subtitle, style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant)),
          ],
        ),
      ),
    );
  }
}

class _AddressOption extends StatelessWidget {
  final Address address;
  final bool selected;
  final VoidCallback onTap;
  final Widget? trailing;

  const _AddressOption({required this.address, required this.selected, required this.onTap, this.trailing});

  static IconData iconFor(String type) {
    switch (type) {
      case 'Work':
        return Icons.work_rounded;
      case 'Other':
        return Icons.place_rounded;
      default:
        return Icons.home_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Material(
      color: selected ? scheme.primaryContainer.fade(context.isDark ? 0.25 : 0.4) : scheme.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.sm),
        side: BorderSide(color: selected ? scheme.primary : scheme.outlineVariant),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.sm),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.md),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(iconFor(address.type), color: scheme.primary),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${context.tr('address_type_${address.type.toLowerCase()}')} • ${address.recipientName}',
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      address.fullAddress,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant, height: 1.4),
                    ),
                    const SizedBox(height: 2),
                    Text(Fmt.phone(address.recipientPhone), style: context.textStyles.bodySmall),
                  ],
                ),
              ),
              if (trailing != null) trailing!,
            ],
          ),
        ),
      ),
    );
  }
}
