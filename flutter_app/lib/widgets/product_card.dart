import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../l10n/app_localizations.dart';
import '../models/product.dart';
import '../providers/cart_provider.dart';
import '../providers/wishlist_provider.dart';
import '../screens/catalog/product_detail_screen.dart';
import '../theme/app_theme.dart';
import '../utils/app_actions.dart';
import 'app_network_image.dart';
import 'price_text.dart';
import 'qty_stepper.dart';

/// Grid / rail product card. Give it a bounded height
/// (e.g. grid `mainAxisExtent: 290` or a `SizedBox(height: 290)` in rails).
class ProductCard extends StatelessWidget {
  final Product product;
  final double? width;

  const ProductCard({super.key, required this.product, this.width});

  void _open(BuildContext context) {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final name = product.nameFor(context.langCode);

    final card = Material(
      color: scheme.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        side: BorderSide(color: scheme.outlineVariant.fade(context.isDark ? 0.5 : 0.7)),
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => _open(context),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Positioned.fill(
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.sm),
                      child: AppNetworkImage(
                        url: product.mainImage,
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                        backgroundColor: scheme.surfaceContainerLow,
                      ),
                    ),
                  ),
                  if (product.hasDiscount)
                    Positioned(
                      top: AppSpacing.md,
                      left: AppSpacing.md,
                      child: DiscountBadge(percent: product.discountPercentage),
                    ),
                  Positioned(
                    top: AppSpacing.xs + 2,
                    right: AppSpacing.xs + 2,
                    child: _FavoriteButton(product: product),
                  ),
                  if (!product.isInStock)
                    Positioned.fill(
                      child: Container(
                        margin: const EdgeInsets.all(AppSpacing.sm),
                        decoration: BoxDecoration(
                          color: scheme.surface.fade(0.6),
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                        ),
                        alignment: Alignment.center,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: scheme.inverseSurface,
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                          ),
                          child: Text(
                            context.tr('out_of_stock'),
                            style: TextStyle(color: scheme.onInverseSurface, fontSize: 11, fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.md, 0, AppSpacing.md, AppSpacing.md),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 38,
                    child: Text(
                      name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w700, height: 1.25, fontSize: 13.5),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    product.unit,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: FittedBox(
                          fit: BoxFit.scaleDown,
                          alignment: Alignment.centerLeft,
                          child: PriceText(
                            price: product.price,
                            strikePrice: product.strikePrice,
                            fontSize: 15,
                            vertical: true,
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.xs),
                      CartControl(product: product),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );

    return width != null ? SizedBox(width: width, child: card) : card;
  }
}

/// "ADD" button that turns into a quantity stepper once the item is in the cart.
class CartControl extends StatelessWidget {
  final Product product;
  final bool large;

  const CartControl({super.key, required this.product, this.large = false});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final qty = cart.getProductQuantity(product.id);
    final busy = cart.isBusy(product.id);
    final scheme = context.colors;

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 200),
      transitionBuilder: (child, anim) => FadeTransition(
        opacity: anim,
        child: ScaleTransition(scale: Tween<double>(begin: 0.9, end: 1).animate(anim), child: child),
      ),
      child: qty > 0
          ? QtyStepper(
              key: const ValueKey('stepper'),
              quantity: qty,
              loading: busy,
              large: large,
              onDecrement: () => AppActions.setQuantity(context, product.id, qty - 1),
              onIncrement: () {
                final item = cart.itemFor(product.id);
                if (item != null && !item.canIncrement) return;
                AppActions.setQuantity(context, product.id, qty + 1);
              },
            )
          : SizedBox(
              key: const ValueKey('add'),
              height: large ? 48 : 34,
              child: OutlinedButton(
                onPressed: (!product.isInStock || busy) ? null : () => AppActions.addToCart(context, product),
                style: OutlinedButton.styleFrom(
                  minimumSize: Size(large ? 120 : 64, large ? 48 : 34),
                  padding: EdgeInsets.symmetric(horizontal: large ? 20 : 12),
                  foregroundColor: scheme.primary,
                  backgroundColor: scheme.primaryContainer.fade(context.isDark ? 0.25 : 0.45),
                  side: BorderSide(color: scheme.primary.fade(0.6)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(large ? AppRadius.sm : AppRadius.xs + 2)),
                  textStyle: TextStyle(fontWeight: FontWeight.w800, fontSize: large ? 15 : 13),
                ),
                child: busy
                    ? SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: scheme.primary))
                    : Text(context.tr(large ? 'add_to_cart' : 'add')),
              ),
            ),
    );
  }
}

class _FavoriteButton extends StatelessWidget {
  final Product product;
  const _FavoriteButton({required this.product});

  @override
  Widget build(BuildContext context) {
    final isFav = context.select<WishlistProvider, bool>((w) => w.isFavorite(product.id));
    final scheme = context.colors;
    return Material(
      color: scheme.surface.fade(0.9),
      shape: const CircleBorder(),
      elevation: 0,
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: () => AppActions.toggleWishlist(context, product),
        child: Padding(
          padding: const EdgeInsets.all(6),
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 220),
            transitionBuilder: (child, anim) => ScaleTransition(scale: anim, child: child),
            child: Icon(
              isFav ? Icons.favorite_rounded : Icons.favorite_border_rounded,
              key: ValueKey<bool>(isFav),
              size: 18,
              color: isFav ? const Color(0xFFE11D48) : scheme.onSurfaceVariant,
            ),
          ),
        ),
      ),
    );
  }
}
