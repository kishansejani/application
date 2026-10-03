import 'package:flutter/material.dart';
import '../../l10n/app_localizations.dart';
import '../../models/category.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_network_image.dart';
import '../../widgets/category_card.dart';
import 'product_list_screen.dart';

class SubcategoriesScreen extends StatelessWidget {
  final Category category;

  const SubcategoriesScreen({super.key, required this.category});

  void _openProducts(BuildContext context, {SubCategory? sub}) {
    final lang = context.langCode;
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductListScreen(
          title: sub?.nameFor(lang) ?? category.nameFor(lang),
          categoryId: category.id,
          subCategoryId: sub?.id,
          subCategories: category.subCategories,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final lang = context.langCode;
    final scheme = context.colors;
    final subs = category.subCategories;

    return Scaffold(
      appBar: AppBar(title: Text(category.nameFor(lang))),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final hPad = Responsive.centeredPadding(width);
          final content = width - hPad * 2;
          final columns = content >= 1000 ? 5 : (content >= 700 ? 4 : (content >= 480 ? 3 : 2));

          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.lg),
                sliver: SliverToBoxAdapter(
                  child: AppCard(
                    onTap: () => _openProducts(context),
                    color: scheme.primaryContainer.fade(context.isDark ? 0.3 : 0.6),
                    borderColor: Colors.transparent,
                    child: Row(
                      children: [
                        Container(
                          width: 52,
                          height: 52,
                          decoration: BoxDecoration(
                            color: scheme.surface,
                            borderRadius: BorderRadius.circular(AppRadius.sm),
                          ),
                          child: Icon(categoryIcon(category.icon), color: scheme.primary),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                context.tr('all_in_category', {'name': category.nameFor(lang)}),
                                style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
                              ),
                              Text(
                                context.tr('browse_products'),
                                style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                              ),
                            ],
                          ),
                        ),
                        Icon(Icons.arrow_forward_rounded, color: scheme.primary),
                      ],
                    ),
                  ),
                ),
              ),
              SliverPadding(
                padding: EdgeInsets.fromLTRB(hPad, 0, hPad, AppSpacing.xxl),
                sliver: SliverGrid(
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: columns,
                    mainAxisSpacing: AppSpacing.md,
                    crossAxisSpacing: AppSpacing.md,
                    childAspectRatio: 0.82,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, i) {
                      final sub = subs[i];
                      return AppCard(
                        padding: const EdgeInsets.all(AppSpacing.sm),
                        onTap: () => _openProducts(context, sub: sub),
                        child: Column(
                          children: [
                            Expanded(
                              child: Container(
                                width: double.infinity,
                                decoration: BoxDecoration(
                                  color: categoryTint(context, i),
                                  borderRadius: BorderRadius.circular(AppRadius.sm),
                                ),
                                child: AppNetworkImage(
                                  url: sub.image,
                                  borderRadius: BorderRadius.circular(AppRadius.sm),
                                  placeholderIcon: categoryIcon(category.icon),
                                ),
                              ),
                            ),
                            const SizedBox(height: AppSpacing.sm),
                            SizedBox(
                              height: 36,
                              child: Center(
                                child: Text(
                                  sub.nameFor(lang),
                                  maxLines: 2,
                                  textAlign: TextAlign.center,
                                  overflow: TextOverflow.ellipsis,
                                  style: context.textStyles.labelLarge?.copyWith(fontWeight: FontWeight.w700, height: 1.2),
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                    childCount: subs.length,
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
