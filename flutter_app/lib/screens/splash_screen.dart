import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../constants/app_colors.dart';
import '../l10n/app_localizations.dart';
import '../providers/auth_provider.dart';
import '../providers/product_provider.dart';
import '../theme/app_theme.dart';
import '../utils/app_actions.dart';
import 'main_navigation_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _logoScale;
  late final Animation<double> _fade;
  late final Animation<Offset> _slide;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1400));
    _logoScale = CurvedAnimation(parent: _controller, curve: const Interval(0, 0.6, curve: Curves.easeOutBack));
    _fade = CurvedAnimation(parent: _controller, curve: const Interval(0.35, 1, curve: Curves.easeOut));
    _slide = Tween<Offset>(begin: const Offset(0, 0.25), end: Offset.zero).animate(
      CurvedAnimation(parent: _controller, curve: const Interval(0.35, 1, curve: Curves.easeOutCubic)),
    );
    _controller.forward();
    WidgetsBinding.instance.addPostFrameCallback((_) => _initAppData());
  }

  Future<void> _initAppData() async {
    final productProvider = context.read<ProductProvider>();
    final authProvider = context.read<AuthProvider>();

    try {
      await Future.wait([
        productProvider.fetchHomeData(),
        authProvider.initAuth(),
        Future<void>.delayed(const Duration(milliseconds: 1600)),
      ]);
    } catch (_) {
      // Errors are surfaced on the home screen with a retry button.
    }

    if (!mounted) return;
    if (authProvider.isAuthenticated) {
      AppActions.loadUserData(context);
    }

    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 500),
        pageBuilder: (_, __, ___) => const MainNavigationScreen(),
        transitionsBuilder: (_, animation, __, child) => FadeTransition(opacity: animation, child: child),
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        body: Container(
          width: double.infinity,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [AppColors.primary, AppColors.primaryDark, AppColors.primaryDeep],
            ),
          ),
          child: Stack(
            children: [
              // Decorative circles
              Positioned(top: -80, right: -60, child: _Bubble(size: 240, opacity: 0.08)),
              Positioned(bottom: -100, left: -70, child: _Bubble(size: 300, opacity: 0.06)),
              Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    ScaleTransition(
                      scale: _logoScale,
                      child: Container(
                        width: 112,
                        height: 112,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(32),
                          boxShadow: [
                            BoxShadow(color: Colors.black.fade(0.18), blurRadius: 30, offset: const Offset(0, 12)),
                          ],
                        ),
                        child: const Icon(Icons.shopping_basket_rounded, size: 60, color: AppColors.primary),
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),
                    FadeTransition(
                      opacity: _fade,
                      child: SlideTransition(
                        position: _slide,
                        child: Column(
                          children: [
                            Text(
                              context.tr('app_name'),
                              style: context.textStyles.headlineMedium?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: AppSpacing.sm),
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl),
                              child: Text(
                                context.tr('tagline'),
                                textAlign: TextAlign.center,
                                style: context.textStyles.bodyLarge?.copyWith(color: Colors.white.fade(0.85)),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: MediaQuery.paddingOf(context).bottom + AppSpacing.xxl,
                child: FadeTransition(
                  opacity: _fade,
                  child: Column(
                    children: [
                      const SizedBox(
                        width: 26,
                        height: 26,
                        child: CircularProgressIndicator(strokeWidth: 2.6, color: Colors.white),
                      ),
                      const SizedBox(height: AppSpacing.md),
                      Text(
                        context.tr('splash_footer'),
                        style: context.textStyles.labelMedium?.copyWith(color: Colors.white.fade(0.8), letterSpacing: 0.6),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Bubble extends StatelessWidget {
  final double size;
  final double opacity;
  const _Bubble({required this.size, required this.opacity});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.white.fade(opacity)),
    );
  }
}
