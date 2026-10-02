import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/api_constants.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/address.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../../services/api_service.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/delivery_slot_banner.dart';
import '../addresses/add_address_map_screen.dart';
import 'order_success_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({Key? key}) : super(key: key);

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  List<Address> _addresses = [];
  Address? _selectedAddress;
  String _paymentMethod = 'cod';
  final _couponController = TextEditingController();
  final _noteController = TextEditingController();
  bool _isLoadingAddresses = true;
  bool _isPlacingOrder = false;

  @override
  void initState() {
    super.initState();
    _loadAddresses();
  }

  Future<void> _loadAddresses() async {
    try {
      final res = await ApiService.get(ApiConstants.addresses, requireAuth: true);
      if (res['data'] != null) {
        final list = (res['data'] as List).map((i) => Address.fromJson(i)).toList();
        setState(() {
          _addresses = list;
          if (_addresses.isNotEmpty) {
            _selectedAddress = _addresses.firstWhere((a) => a.isDefault, orElse: () => _addresses.first);
          }
          _isLoadingAddresses = false;
        });
      }
    } catch (e) {
      setState(() => _isLoadingAddresses = false);
    }
  }

  Future<void> _placeOrder() async {
    if (_selectedAddress == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select or add a delivery address!'), backgroundColor: AppColors.error),
      );
      return;
    }

    final cartProvider = Provider.of<CartProvider>(context, listen: false);

    setState(() => _isPlacingOrder = true);
    try {
      final res = await ApiService.post(
        ApiConstants.orders,
        {
          'address_id': _selectedAddress!.id,
          'payment_method': _paymentMethod,
          'coupon_code': cartProvider.couponCode,
          'delivery_note': _noteController.text.trim(),
        },
        requireAuth: true,
      );

      setState(() => _isPlacingOrder = false);

      if (res['order'] != null) {
        cartProvider.clearCart();
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => OrderSuccessScreen(
              orderNumber: res['order']['order_number'] ?? '#ORD',
              deliverySlot: res['order']['delivery_slot'] ?? '2_hours',
              slotTime: res['order']['delivery_slot_time'] ?? '',
            ),
          ),
        );
      }
    } catch (e) {
      setState(() => _isPlacingOrder = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = Provider.of<CartProvider>(context);
    final productProvider = Provider.of<ProductProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          context.tr('checkout'),
          style: const TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Delivery Slot Information
            DeliverySlotBanner(slotInfo: productProvider.deliverySlotInfo),

            // Delivery Address Section
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.location_on, color: AppColors.primary, size: 20),
                          const SizedBox(width: 8),
                          Text(
                            context.tr('saved_addresses'),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                        ],
                      ),
                      TextButton.icon(
                        onPressed: () async {
                          final result = await Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const AddAddressMapScreen()),
                          );
                          if (result == true) _loadAddresses();
                        },
                        icon: const Icon(Icons.add_location_alt_outlined, size: 16, color: AppColors.primary),
                        label: Text(
                          context.tr('add_new_address'),
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  if (_isLoadingAddresses)
                    const Center(child: CircularProgressIndicator())
                  else if (_addresses.isEmpty)
                    Center(
                      child: Column(
                        children: [
                          const Text('No addresses saved yet', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                          const SizedBox(height: 8),
                          ElevatedButton.icon(
                            onPressed: () async {
                              final res = await Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const AddAddressMapScreen()),
                              );
                              if (res == true) _loadAddresses();
                            },
                            icon: const Icon(Icons.pin_drop, size: 16),
                            label: Text(context.tr('pin_location_map')),
                            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                          ),
                        ],
                      ),
                    )
                  else
                    Column(
                      children: _addresses.map((addr) {
                        final isSelected = _selectedAddress?.id == addr.id;
                        return GestureDetector(
                          onTap: () => setState(() => _selectedAddress = addr),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: isSelected ? AppColors.primaryLight : AppColors.background,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: isSelected ? AppColors.primary : AppColors.border,
                                width: isSelected ? 1.5 : 1.0,
                              ),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Icon(
                                  isSelected ? Icons.check_circle : Icons.radio_button_unchecked,
                                  color: isSelected ? AppColors.primary : AppColors.textMuted,
                                  size: 20,
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Text(
                                            addr.name,
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          const SizedBox(width: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: Colors.white,
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: Text(
                                              addr.type.toUpperCase(),
                                              style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 3),
                                      Text(
                                        addr.fullAddress,
                                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                ],
              ),
            ),

            // Coupon Code Section
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.discount_outlined, color: AppColors.accent, size: 20),
                      const SizedBox(width: 8),
                      Text(context.tr('apply_coupon'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _couponController,
                          textCapitalization: TextCapitalization.characters,
                          decoration: InputDecoration(
                            hintText: 'Enter code (e.g. FRESH50)',
                            hintStyle: const TextStyle(fontSize: 12),
                            isDense: true,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        onPressed: () async {
                          if (_couponController.text.trim().isEmpty) return;
                          try {
                            final ok = await cartProvider.applyCoupon(_couponController.text.trim());
                            if (ok) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Coupon applied successfully!'), backgroundColor: AppColors.success),
                              );
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Invalid or expired coupon'), backgroundColor: AppColors.error),
                              );
                            }
                          } catch (e) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: AppColors.error),
                            );
                          }
                        },
                        style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                        child: const Text('Apply'),
                      ),
                    ],
                  ),
                  if (cartProvider.couponCode != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Row(
                        children: [
                          Text('Coupon "${cartProvider.couponCode}" applied: -₹${cartProvider.discountAmount.toStringAsFixed(2)}', style: const TextStyle(color: AppColors.success, fontSize: 12, fontWeight: FontWeight.bold)),
                          const Spacer(),
                          GestureDetector(
                            onTap: () => cartProvider.removeCoupon(),
                            child: const Text('Remove', style: TextStyle(color: AppColors.error, fontSize: 12, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),

            // Payment Methods
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.payment_outlined, color: AppColors.primary, size: 20),
                      const SizedBox(width: 8),
                      Text(context.tr('payment_method'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    ],
                  ),
                  const SizedBox(height: 10),
                  RadioListTile<String>(
                    value: 'cod',
                    groupValue: _paymentMethod,
                    activeColor: AppColors.primary,
                    title: Text(context.tr('cod'), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    subtitle: const Text('Pay cash or UPI upon delivery', style: TextStyle(fontSize: 11)),
                    onChanged: (val) => setState(() => _paymentMethod = val!),
                  ),
                  RadioListTile<String>(
                    value: 'online',
                    groupValue: _paymentMethod,
                    activeColor: AppColors.primary,
                    title: Text(context.tr('online_payment'), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    subtitle: const Text('Google Pay / PhonePe / Cards', style: TextStyle(fontSize: 11)),
                    onChanged: (val) => setState(() => _paymentMethod = val!),
                  ),
                ],
              ),
            ),

            // Delivery Note
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: TextField(
                controller: _noteController,
                maxLines: 2,
                decoration: const InputDecoration(
                  hintText: 'Delivery note / instructions (e.g. ring bell, leave at door)',
                  hintStyle: TextStyle(fontSize: 12),
                  border: InputBorder.none,
                ),
              ),
            ),

            // Order Placement CTA
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: CustomButton(
                text: '${context.tr('place_order')} • ₹${cartProvider.grandTotal.toStringAsFixed(2)}',
                isLoading: _isPlacingOrder,
                onPressed: _placeOrder,
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}
