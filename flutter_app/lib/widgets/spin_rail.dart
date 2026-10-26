import 'package:flutter/material.dart';
import '../l10n/app_localizations.dart';
import '../models/product.dart';
import '../screens/catalog/product_detail_screen.dart';
import '../theme/app_theme.dart';
import 'price_text.dart';
import 'spin_viewer.dart';

/// Home rail: products that can be rotated in 360°. Each tile auto-rotates.
class SpinRail extends StatelessWidget {
  final List<Product> products;
  final double horizontalPadding;
  const SpinRail({super.key, required this.products, required this.horizontalPadding});

  @override
  Widget build(BuildContext context) {
    if (products.isEmpty) return const SizedBox.shrink();
    final scheme = context.colors;
    final d = context.design;
    return Container(
      margin: const EdgeInsets.only(top: AppSpacing.xl),
      padding: const EdgeInsets.only(bottom: AppSpacing.lg),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [scheme.primaryContainer.fade(context.isDark ? 0.25 : 0.55), Colors.transparent],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.fromLTRB(horizontalPadding, AppSpacing.lg, horizontalPadding, AppSpacing.md),
            child: Row(children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: scheme.primary,
                  borderRadius: BorderRadius.circular(d.r(12)),
                ),
                child: Icon(Icons.threesixty_rounded, color: scheme.onPrimary, size: 22),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(context.tr('spin_title'),
                      style: context.textStyles.titleLarge?.copyWith(fontWeight: FontWeight.w800, fontSize: 19)),
                  Text(context.tr('spin_subtitle'),
                      style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant)),
                ]),
              ),
            ]),
          ),
          SizedBox(
            height: 232,
            child: ListView.separated(
              padding: EdgeInsets.symmetric(horizontal: horizontalPadding),
              scrollDirection: Axis.horizontal,
              itemCount: products.length,
              separatorBuilder: (_, __) => const SizedBox(width: AppSpacing.md),
              itemBuilder: (context, i) => _SpinTile(product: products[i]),
            ),
          ),
        ],
      ),
    );
  }
}

class _SpinTile extends StatelessWidget {
  final Product product;
  const _SpinTile({required this.product});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final d = context.design;
    final radius = BorderRadius.circular(d.card);
    return SizedBox(
      width: 168,
      child: Material(
        color: scheme.surface,
        shape: RoundedRectangleBorder(borderRadius: radius, side: BorderSide(color: scheme.outlineVariant)),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () => Navigator.of(context).push(
            MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Expanded(
              child: IgnorePointer(
                child: Product360Viewer(
                  frames: product.spinFrames,
                  compact: true,
                  borderRadius: BorderRadius.zero,
                  backgroundColor: Colors.transparent,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.md, AppSpacing.xs, AppSpacing.md, AppSpacing.md),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(
                  product.nameFor(context.langCode),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: context.textStyles.titleSmall?.copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 2),
                PriceText(price: product.price, strikePrice: product.strikePrice, fontSize: 14),
              ]),
            ),
          ]),
        ),
      ),
    );
  }
}
