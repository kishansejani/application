import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../models/category.dart';
import '../theme/app_theme.dart';
import 'app_network_image.dart';

/// Maps the backend's Font Awesome class names to Material icons (fallback when
/// the category has no image).
IconData categoryIcon(String? faIcon) {
  final s = (faIcon ?? '').toLowerCase();
  if (s.contains('carrot') || s.contains('leaf') || s.contains('seedling') || s.contains('lemon')) return Icons.eco_rounded;
  if (s.contains('cheese') || s.contains('milk') || s.contains('cow') || s.contains('glass')) return Icons.local_drink_rounded;
  if (s.contains('bread') || s.contains('wheat') || s.contains('cake')) return Icons.bakery_dining_rounded;
  if (s.contains('cookie') || s.contains('candy')) return Icons.icecream_rounded;
  if (s.contains('mug') || s.contains('coffee') || s.contains('bottle') || s.contains('wine')) return Icons.local_cafe_rounded;
  if (s.contains('pepper') || s.contains('fire') || s.contains('mortar')) return Icons.local_fire_department_rounded;
  if (s.contains('soap') || s.contains('spray') || s.contains('broom')) return Icons.cleaning_services_rounded;
  return Icons.category_rounded;
}

Color categoryTint(BuildContext context, int index) {
  final list = context.isDark ? AppColors.categoryTintsDark : AppColors.categoryTints;
  return list[index.abs() % list.length];
}

/// Rounded image tile with the category name underneath (home grid).
class CategoryCard extends StatelessWidget {
  final Category category;
  final VoidCallback? onTap;
  final int index;

  const CategoryCard({super.key, required this.category, this.onTap, this.index = 0});

  @override
  Widget build(BuildContext context) {
    final tint = categoryTint(context, index);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.xs),
        child: Column(
          children: [
            AspectRatio(
              aspectRatio: 1,
              child: Container(
                decoration: BoxDecoration(
                  color: tint,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                padding: const EdgeInsets.all(6),
                child: category.image != null
                    ? AppNetworkImage(
                        url: category.image,
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                        placeholderIcon: categoryIcon(category.icon),
                      )
                    : Icon(categoryIcon(category.icon), size: 32, color: context.colors.primary),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              category.nameFor(context.langCode),
              maxLines: 2,
              textAlign: TextAlign.center,
              overflow: TextOverflow.ellipsis,
              style: context.textStyles.labelMedium?.copyWith(fontWeight: FontWeight.w700, height: 1.2),
            ),
          ],
        ),
      ),
    );
  }
}
