import 'dart:async';
import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../models/slider_item.dart';
import '../theme/app_theme.dart';
import 'app_network_image.dart';

/// Auto-playing PageView banner carousel with animated page indicators.
/// (Replaces carousel_slider which clashes with Flutter 3.22+'s CarouselController.)
class BannerCarousel extends StatefulWidget {
  final List<SliderItem> items;
  final ValueChanged<SliderItem>? onTap;
  final double height;
  final Duration interval;
  final double viewportFraction;

  const BannerCarousel({
    super.key,
    required this.items,
    this.onTap,
    this.height = 180,
    this.interval = const Duration(seconds: 5),
    this.viewportFraction = 1.0,
  });

  @override
  State<BannerCarousel> createState() => _BannerCarouselState();
}

class _BannerCarouselState extends State<BannerCarousel> {
  late final PageController _controller;
  Timer? _timer;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController(viewportFraction: widget.viewportFraction);
    _startAutoPlay();
  }

  @override
  void didUpdateWidget(covariant BannerCarousel oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.items.length != widget.items.length) {
      _index = 0;
      if (_controller.hasClients) _controller.jumpToPage(0);
      _startAutoPlay();
    }
  }

  void _startAutoPlay() {
    _timer?.cancel();
    if (widget.items.length < 2) return;
    _timer = Timer.periodic(widget.interval, (_) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_index + 1) % widget.items.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 550),
        curve: Curves.easeOutCubic,
      );
    });
  }

  void _stopAutoPlay() => _timer?.cancel();

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.items.isEmpty) return const SizedBox.shrink();
    return Column(
      children: [
        SizedBox(
          height: widget.height,
          child: Listener(
            onPointerDown: (_) => _stopAutoPlay(),
            onPointerUp: (_) => _startAutoPlay(),
            onPointerCancel: (_) => _startAutoPlay(),
            child: PageView.builder(
              controller: _controller,
              itemCount: widget.items.length,
              onPageChanged: (i) => setState(() => _index = i),
              itemBuilder: (context, i) {
                final item = widget.items[i];
                return Padding(
                  padding: EdgeInsets.symmetric(horizontal: widget.viewportFraction < 1 ? 6 : 0),
                  child: _BannerTile(item: item, onTap: widget.onTap == null ? null : () => widget.onTap!(item)),
                );
              },
            ),
          ),
        ),
        if (widget.items.length > 1) ...[
          const SizedBox(height: AppSpacing.md),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(widget.items.length, (i) {
              final active = i == _index;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                curve: Curves.easeOut,
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: active ? 22 : 7,
                height: 7,
                decoration: BoxDecoration(
                  color: active ? context.colors.primary : context.colors.outlineVariant,
                  borderRadius: BorderRadius.circular(AppRadius.pill),
                ),
              );
            }),
          ),
        ],
      ],
    );
  }
}

class _BannerTile extends StatelessWidget {
  final SliderItem item;
  final VoidCallback? onTap;

  const _BannerTile({required this.item, this.onTap});

  @override
  Widget build(BuildContext context) {
    final hasText = item.title.isNotEmpty || (item.subtitle?.isNotEmpty ?? false);
    return ClipRRect(
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Material(
        color: AppColors.primaryDark,
        child: InkWell(
          onTap: onTap,
          child: Stack(
            fit: StackFit.expand,
            children: [
              AppNetworkImage(url: item.image, placeholderIcon: Icons.image_outlined),
              if (hasText)
                DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.centerLeft,
                      end: Alignment.centerRight,
                      colors: [Colors.black.fade(0.70), Colors.black.fade(0.25), Colors.transparent],
                      stops: const [0, 0.55, 1],
                    ),
                  ),
                ),
              if (hasText)
                Padding(
                  padding: const EdgeInsets.all(AppSpacing.lg + 2),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      if (item.badge != null)
                        Container(
                          margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.accent,
                            borderRadius: BorderRadius.circular(AppRadius.xs),
                          ),
                          child: Text(
                            item.badge!,
                            style: const TextStyle(color: Colors.black87, fontWeight: FontWeight.w800, fontSize: 11),
                          ),
                        ),
                      FractionallySizedBox(
                        widthFactor: 0.72,
                        child: Text(
                          item.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: context.textStyles.titleLarge?.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            height: 1.2,
                          ),
                        ),
                      ),
                      if (item.subtitle != null) ...[
                        const SizedBox(height: 4),
                        FractionallySizedBox(
                          widthFactor: 0.72,
                          child: Text(
                            item.subtitle!,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: context.textStyles.bodySmall?.copyWith(color: Colors.white.fade(0.9)),
                          ),
                        ),
                      ],
                      if (onTap != null && item.linkType != 'none') ...[
                        const SizedBox(height: AppSpacing.md),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                context.tr('shop_now'),
                                style: const TextStyle(
                                  color: AppColors.primaryDark,
                                  fontWeight: FontWeight.w800,
                                  fontSize: 12,
                                ),
                              ),
                              const SizedBox(width: 4),
                              const Icon(Icons.arrow_forward_rounded, size: 14, color: AppColors.primaryDark),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
