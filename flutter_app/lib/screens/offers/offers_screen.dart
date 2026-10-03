import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../models/offer.dart';
import '../../providers/product_provider.dart';
import '../../theme/app_theme.dart';
import '../../utils/responsive.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/offer_card.dart';
import '../../widgets/shimmer_box.dart';

/// Coupons & offers. In [selectMode] the "Apply" action pops the coupon code.
class OffersScreen extends StatefulWidget {
  final bool selectMode;

  const OffersScreen({super.key, this.selectMode = false});

  @override
  State<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen> {
  List<Offer>? _offers;
  Object? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    final cached = context.read<ProductProvider>().offers;
    if (cached.isNotEmpty) {
      _offers = cached;
      _loading = false;
    }
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    if (_offers == null) setState(() => _loading = true);
    try {
      final offers = await context.read<ProductProvider>().fetchOffers();
      if (!mounted) return;
      setState(() {
        _offers = offers;
        _error = null;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e;
        _loading = false;
      });
    }
  }

  Widget _card(BuildContext context, Offer offer) {
    return OfferCard(
      offer: offer,
      onApply: widget.selectMode ? () => Navigator.of(context).pop(offer.code) : null,
    );
  }

  @override
  Widget build(BuildContext context) {
    final offers = _offers;
    return Scaffold(
      appBar: AppBar(title: Text(context.tr(widget.selectMode ? 'select_coupon' : 'offers_coupons'))),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final hPad = Responsive.centeredPadding(constraints.maxWidth);
          final columns = (constraints.maxWidth - hPad * 2) >= 760 ? 2 : 1;

          if (_loading && offers == null) {
            return ListSkeleton(itemCount: 4, itemHeight: 130, padding: EdgeInsets.all(hPad));
          }
          if (offers == null || (_error != null && offers.isEmpty)) {
            return ErrorState(error: _error, onRetry: _load);
          }
          if (offers.isEmpty) {
            return RefreshIndicator(
              onRefresh: _load,
              child: EmptyState(
                icon: Icons.local_offer_outlined,
                title: context.tr('no_offers'),
                message: context.tr('no_offers_msg'),
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: _load,
            child: CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                SliverPadding(
                  padding: EdgeInsets.fromLTRB(hPad, AppSpacing.sm, hPad, AppSpacing.lg),
                  sliver: SliverToBoxAdapter(child: _OffersHeader(count: offers.length)),
                ),
                SliverPadding(
                  padding: EdgeInsets.fromLTRB(hPad, 0, hPad, AppSpacing.xxl),
                  sliver: columns == 1
                      ? SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, i) => Padding(
                              padding: const EdgeInsets.only(bottom: AppSpacing.md),
                              child: _card(context, offers[i]),
                            ),
                            childCount: offers.length,
                          ),
                        )
                      : SliverGrid(
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            mainAxisSpacing: AppSpacing.md,
                            crossAxisSpacing: AppSpacing.md,
                            mainAxisExtent: 210,
                          ),
                          delegate: SliverChildBuilderDelegate(
                            (context, i) => _card(context, offers[i]),
                            childCount: offers.length,
                          ),
                        ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _OffersHeader extends StatelessWidget {
  final int count;
  const _OffersHeader({required this.count});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFEA580C)]),
        borderRadius: BorderRadius.circular(AppRadius.lg),
      ),
      child: Row(
        children: [
          const Icon(Icons.redeem_rounded, color: Colors.white, size: 36),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  context.tr('offers_available', {'count': '$count'}),
                  style: context.textStyles.titleMedium?.copyWith(color: Colors.white, fontWeight: FontWeight.w800),
                ),
                Text(
                  context.tr('tap_code_to_copy'),
                  style: context.textStyles.bodySmall?.copyWith(color: Colors.white.fade(0.9)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
