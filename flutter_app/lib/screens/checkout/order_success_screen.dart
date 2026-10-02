import 'package:flutter/material.dart';
import '../../constants/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../widgets/custom_button.dart';
import '../main_navigation_screen.dart';
import '../orders/orders_screen.dart';

class OrderSuccessScreen extends StatelessWidget {
  final String orderNumber;
  final String deliverySlot;
  final String slotTime;

  const OrderSuccessScreen({
    Key? key,
    required this.orderNumber,
    required this.deliverySlot,
    required this.slotTime,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final is2Hours = deliverySlot == '2_hours';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 90,
                height: 90,
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, size: 54, color: AppColors.primary),
              ),
              const SizedBox(height: 24),
              Text(
                context.tr('order_success'),
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 8),
              Text(
                'Order ID: $orderNumber',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.primaryDark),
              ),
              const SizedBox(height: 20),

              // Delivery Commitment Badge
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: AppColors.borderLight),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10),
                  ],
                ),
                child: Row(
                  children: [
                    Icon(
                      is2Hours ? Icons.electric_bolt_rounded : Icons.wb_sunny_rounded,
                      color: is2Hours ? AppColors.primary : AppColors.accent,
                      size: 28,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            is2Hours ? context.tr('delivery_within_2_hours') : context.tr('delivery_next_day'),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                          if (slotTime.isNotEmpty)
                            Text(
                              'Expected: $slotTime',
                              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 32),

              CustomButton(
                text: context.tr('track_order'),
                icon: Icons.map_outlined,
                onPressed: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (_) => const OrdersScreen()),
                  );
                },
              ),

              const SizedBox(height: 12),

              OutlinedButton(
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                    (route) => false,
                  );
                },
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: const Text('Continue Shopping', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
