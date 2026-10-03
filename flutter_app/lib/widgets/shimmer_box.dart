import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../theme/app_theme.dart';

/// Skeleton block with shimmer animation.
class ShimmerBox extends StatelessWidget {
  final double? width;
  final double? height;
  final double radius;
  final bool circle;

  const ShimmerBox({super.key, this.width, this.height, this.radius = AppRadius.sm, this.circle = false});

  @override
  Widget build(BuildContext context) {
    final dark = context.isDark;
    return Shimmer.fromColors(
      baseColor: dark ? const Color(0xFF232A25) : const Color(0xFFE6EBE8),
      highlightColor: dark ? const Color(0xFF323B35) : const Color(0xFFF7F9F8),
      child: Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: Colors.white,
          shape: circle ? BoxShape.circle : BoxShape.rectangle,
          borderRadius: circle ? null : BorderRadius.circular(radius),
        ),
      ),
    );
  }
}

/// Skeleton matching [ProductCard].
class ProductCardSkeleton extends StatelessWidget {
  const ProductCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: context.colors.surface,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: context.colors.outlineVariant.fade(0.5)),
      ),
      padding: const EdgeInsets.all(AppSpacing.sm),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(child: ShimmerBox(width: double.infinity)),
          SizedBox(height: AppSpacing.md),
          ShimmerBox(width: double.infinity, height: 14),
          SizedBox(height: AppSpacing.xs),
          ShimmerBox(width: 70, height: 12),
          SizedBox(height: AppSpacing.md),
          Row(
            children: [
              ShimmerBox(width: 54, height: 18),
              Spacer(),
              ShimmerBox(width: 64, height: 32),
            ],
          ),
        ],
      ),
    );
  }
}

/// Generic list skeleton (orders, cart, addresses ...).
class ListSkeleton extends StatelessWidget {
  final int itemCount;
  final double itemHeight;
  final EdgeInsetsGeometry padding;

  const ListSkeleton({
    super.key,
    this.itemCount = 5,
    this.itemHeight = 96,
    this.padding = const EdgeInsets.all(AppSpacing.lg),
  });

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      physics: const NeverScrollableScrollPhysics(),
      padding: padding,
      itemCount: itemCount,
      separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
      itemBuilder: (_, __) => ShimmerBox(height: itemHeight, radius: AppRadius.md),
    );
  }
}

/// Responsive product grid skeleton.
class ProductGridSkeleton extends StatelessWidget {
  final int columns;
  final int itemCount;
  final EdgeInsetsGeometry padding;

  const ProductGridSkeleton({
    super.key,
    required this.columns,
    this.itemCount = 6,
    this.padding = const EdgeInsets.all(AppSpacing.lg),
  });

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      physics: const NeverScrollableScrollPhysics(),
      padding: padding,
      itemCount: itemCount,
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: columns,
        mainAxisSpacing: AppSpacing.md,
        crossAxisSpacing: AppSpacing.md,
        mainAxisExtent: 290,
      ),
      itemBuilder: (_, __) => const ProductCardSkeleton(),
    );
  }
}
