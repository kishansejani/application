import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../constants/api_constants.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../widgets/custom_button.dart';
import '../auth/phone_login_screen.dart';
import '../checkout/checkout_screen.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({Key? key}) : super(key: key);

  String _getImageUrl(String? path) {
    if (path == null || path.isEmpty) return 'https://placehold.co/200x200/png?text=Grocery';
    if (path.startsWith('http')) return path;
    return '${ApiConstants.imageBaseUrl}/$path';
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = Provider.of<CartProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          context.tr('cart'),
          style: const TextStyle(
            color: AppColors.textPrimary,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        actions: [
          if (cartProvider.items.isNotEmpty)
            TextButton(
              onPressed: () => cartProvider.clearCart(),
              child: Text(
                context.tr('remove'),
                style: const TextStyle(color: AppColors.error, fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ),
        ],
      ),
      body: !authProvider.isAuthenticated
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.lock_outline_rounded, size: 64, color: AppColors.primary),
                    const SizedBox(height: 16),
                    Text(
                      context.tr('login_with_phone'),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    const Text('Please login to view and checkout your cart items', textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary)),
                    const SizedBox(height: 24),
                    CustomButton(
                      text: context.tr('login'),
                      onPressed: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const PhoneLoginScreen()));
                      },
                    ),
                  ],
                ),
              ),
            )
          : cartProvider.items.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10),
                          ],
                        ),
                        child: const Icon(Icons.shopping_cart_outlined, size: 64, color: AppColors.textMuted),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        context.tr('empty_cart'),
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Free Delivery Progress Banner
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      color: AppColors.primaryLight,
                      child: Row(
                        children: [
                          const Icon(Icons.local_shipping_outlined, color: AppColors.primaryDark, size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              cartProvider.subtotal >= 499
                                  ? '🎉 You unlocked FREE Delivery!'
                                  : 'Add ₹${(499 - cartProvider.subtotal).toStringAsFixed(0)} more for FREE Delivery',
                              style: const TextStyle(
                                color: AppColors.primaryDark,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Items List
                    Expanded(
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: cartProvider.items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final item = cartProvider.items[index];
                          final product = item.product;

                          return Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppColors.borderLight),
                            ),
                            child: Row(
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: SizedBox(
                                    width: 65,
                                    height: 65,
                                    child: CachedNetworkImage(
                                      imageUrl: _getImageUrl(product?.mainImage),
                                      fit: BoxFit.cover,
                                      errorWidget: (_, __, ___) => const Icon(Icons.shopping_basket, color: Colors.grey),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        product?.name ?? 'Grocery Item',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                          color: AppColors.textPrimary,
                                        ),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        '₹${item.unitPrice.toStringAsFixed(2)} / ${product?.unit ?? 'item'}',
                                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        '₹${item.totalPrice.toStringAsFixed(2)}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w900,
                                          fontSize: 14,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),

                                // Quantity Controls
                                Container(
                                  decoration: BoxDecoration(
                                    color: AppColors.background,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: AppColors.border),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      IconButton(
                                        icon: const Icon(Icons.remove, size: 14),
                                        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                                        padding: EdgeInsets.zero,
                                        onPressed: () => cartProvider.updateQuantity(item.id, item.quantity - 1),
                                      ),
                                      Text(
                                        '${item.quantity}',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.add, size: 14),
                                        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                                        padding: EdgeInsets.zero,
                                        onPressed: () => cartProvider.updateQuantity(item.id, item.quantity + 1),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),

                    // Bill Summary & Checkout Button
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.06),
                            blurRadius: 10,
                            offset: const Offset(0, -4),
                          ),
                        ],
                      ),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(context.tr('subtotal'), style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                              Text('₹${cartProvider.subtotal.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(context.tr('delivery_fee'), style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                              Text(
                                cartProvider.deliveryCharge == 0 ? context.tr('free') : '₹${cartProvider.deliveryCharge.toStringAsFixed(2)}',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: cartProvider.deliveryCharge == 0 ? AppColors.success : AppColors.textPrimary,
                                ),
                              ),
                            ],
                          ),
                          if (cartProvider.discountAmount > 0) ...[
                            const SizedBox(height: 6),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(context.tr('discount'), style: const TextStyle(color: AppColors.accent, fontSize: 13)),
                                Text('-₹${cartProvider.discountAmount.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.accent)),
                              ],
                            ),
                          ],
                          const Divider(height: 20),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(context.tr('total'), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              Text(
                                '₹${cartProvider.grandTotal.toStringAsFixed(2)}',
                                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: AppColors.primary),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          CustomButton(
                            text: context.tr('checkout'),
                            icon: Icons.arrow_forward_rounded,
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
