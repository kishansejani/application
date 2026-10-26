import 'dart:math' as math;
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_theme.dart';
import '../utils/image_url.dart';

/// Interactive 360° product viewer: drag with inertia, auto-rotate, slider,
/// progressive preloading and full-screen. Configured by the admin panel
/// (Settings → Design & Experience) through [AppDesign].
class Product360Viewer extends StatefulWidget {
  final List<String> frames;

  /// Compact mode (cards): no toolbar, no hint, slow auto-rotate, tap handled by parent.
  final bool compact;
  final bool? autoplay;
  final BorderRadius? borderRadius;
  final Color? backgroundColor;
  final bool allowFullscreen;

  const Product360Viewer({
    super.key,
    required this.frames,
    this.compact = false,
    this.autoplay,
    this.borderRadius,
    this.backgroundColor,
    this.allowFullscreen = true,
  });

  @override
  State<Product360Viewer> createState() => _Product360ViewerState();
}

class _Product360ViewerState extends State<Product360Viewer> with SingleTickerProviderStateMixin {
  late final List<ImageProvider> _providers;
  late Ticker _ticker;
  Duration _last = Duration.zero;

  double _pos = 0; // fractional frame index
  double _velocity = 0; // frames / second
  bool _dragging = false;
  bool _auto = true;
  bool _hintVisible = true;
  int _loaded = 0;
  bool _didPrecache = false;

  int get _n => _providers.length;

  @override
  void initState() {
    super.initState();
    _providers = [
      for (final f in widget.frames)
        CachedNetworkImageProvider(ImageUrl.resolve(f) ?? f),
    ];
    _ticker = createTicker(_onTick)..start();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_didPrecache) {
      _didPrecache = true;
      _auto = widget.autoplay ?? (widget.compact ? true : context.design.spinAutoplay);
      _hintVisible = !widget.compact && context.design.spinHint;
      _preload();
    }
  }

  /// Loads frame 0 first, then the rest, a few at a time.
  Future<void> _preload() async {
    if (_n == 0) return;
    try {
      await precacheImage(_providers[0], context);
    } catch (_) {}
    if (!mounted) return;
    setState(() => _loaded = 1);
    const batch = 4;
    for (var i = 1; i < _n; i += batch) {
      final futures = <Future<void>>[];
      for (var j = i; j < math.min(i + batch, _n); j++) {
        futures.add(precacheImage(_providers[j], context).catchError((_) {}));
      }
      await Future.wait(futures);
      if (!mounted) return;
      setState(() => _loaded = math.min(i + batch, _n));
    }
  }

  bool get _ready => _loaded >= _n;

  // base rotation speed (frames/sec) from admin "speed" (1-100)
  double get _autoSpeed {
    final s = context.design.spinSpeed.clamp(1, 100);
    final base = (_n / 6.0) * (0.4 + s / 50.0); // full turn in ~3-15 s
    return widget.compact ? base * 0.7 : base;
  }

  void _onTick(Duration now) {
    final dt = ((now - _last).inMicroseconds / 1e6).clamp(0.0, 0.05);
    _last = now;
    if (_n < 2 || !_ready || dt == 0) return;
    var changed = false;
    if (!_dragging) {
      if (_velocity.abs() > 0.05) {
        _pos += _velocity * dt;
        _velocity *= math.pow(0.04, dt).toDouble(); // friction
        changed = true;
      } else if (_auto) {
        _pos += _autoSpeed * dt;
        changed = true;
      }
    }
    if (changed) {
      _pos = _pos % _n;
      if (_pos < 0) _pos += _n;
      setState(() {});
    }
  }

  void _stopAuto() {
    _auto = false;
    if (_hintVisible) _hintVisible = false;
  }

  void _onDragStart(DragStartDetails d) {
    _dragging = true;
    _velocity = 0;
    _stopAuto();
    setState(() {});
  }

  void _onDragUpdate(DragUpdateDetails d, double width) {
    if (_n < 2) return;
    final sens = (0.5 + context.design.spinSensitivity / 50.0); // 0.5 - 2.5
    final perPixel = (_n / (width * 0.9)) * sens;
    _pos = (_pos - d.delta.dx * perPixel) % _n;
    if (_pos < 0) _pos += _n;
    _velocity = -d.delta.dx * perPixel * 60;
    setState(() {});
  }

  void _onDragEnd(DragEndDetails d, double width) {
    _dragging = false;
    final sens = (0.5 + context.design.spinSensitivity / 50.0);
    _velocity = -(d.velocity.pixelsPerSecond.dx) * (_n / (width * 0.9)) * sens * 0.6;
    _velocity = _velocity.clamp(-_n * 3.0, _n * 3.0);
    setState(() {});
  }

  @override
  void dispose() {
    _ticker.dispose();
    super.dispose();
  }

  void _openFullscreen() {
    Navigator.of(context).push(PageRouteBuilder<void>(
      opaque: true,
      transitionDuration: const Duration(milliseconds: 260),
      pageBuilder: (_, __, ___) => _SpinFullscreen(frames: widget.frames),
      transitionsBuilder: (_, a, __, child) => FadeTransition(opacity: a, child: child),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final d = context.design;
    final radius = widget.borderRadius ?? BorderRadius.circular(d.card);
    if (_n == 0) return const SizedBox.shrink();
    final idx = _pos.floor() % _n;

    return ClipRRect(
      borderRadius: radius,
      child: Container(
        decoration: BoxDecoration(
          color: widget.backgroundColor ?? scheme.surface,
          gradient: RadialGradient(
            center: const Alignment(0, 0.2),
            radius: 0.85,
            colors: [scheme.primary.fade(context.isDark ? 0.18 : 0.12), Colors.transparent],
          ),
        ),
        child: LayoutBuilder(builder: (context, c) {
          return Stack(
            fit: StackFit.expand,
            children: [
              // floor shadow
              Positioned(
                left: c.maxWidth * 0.25,
                right: c.maxWidth * 0.25,
                bottom: c.maxHeight * (widget.compact ? 0.06 : 0.17),
                height: c.maxHeight * 0.05,
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(100),
                    boxShadow: [BoxShadow(color: Colors.black.fade(0.18), blurRadius: 14, spreadRadius: 2)],
                  ),
                ),
              ),
              GestureDetector(
                behavior: HitTestBehavior.opaque,
                onHorizontalDragStart: widget.compact ? null : _onDragStart,
                onHorizontalDragUpdate: widget.compact ? null : (u) => _onDragUpdate(u, c.maxWidth),
                onHorizontalDragEnd: widget.compact ? null : (e) => _onDragEnd(e, c.maxWidth),
                child: Padding(
                  padding: EdgeInsets.fromLTRB(
                    widget.compact ? 8 : 16,
                    widget.compact ? 8 : 16,
                    widget.compact ? 8 : 16,
                    widget.compact ? 8 : 64,
                  ),
                  child: Image(
                    image: _providers[idx],
                    fit: BoxFit.contain,
                    gaplessPlayback: true,
                    errorBuilder: (_, __, ___) => Icon(Icons.image_not_supported_outlined, color: scheme.outline),
                  ),
                ),
              ),
              // badge
              Positioned(
                top: widget.compact ? 6 : 12,
                left: widget.compact ? 6 : 12,
                child: _Badge360(small: widget.compact),
              ),
              if (!_ready)
                Positioned(
                  bottom: widget.compact ? 8 : 78,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: SizedBox(
                      width: 34,
                      height: 34,
                      child: CircularProgressIndicator(
                        strokeWidth: 3,
                        value: _n == 0 ? null : (_loaded / _n).clamp(0.05, 1.0),
                        color: scheme.primary,
                        backgroundColor: scheme.outlineVariant,
                      ),
                    ),
                  ),
                ),
              if (!widget.compact && _ready && _hintVisible)
                Positioned(
                  top: 14,
                  left: 0,
                  right: 0,
                  child: Center(child: _Hint(label: context.tr('spin_drag_hint'))),
                ),
              if (!widget.compact) _Toolbar(
                n: _n,
                pos: _pos,
                auto: _auto,
                enabled: _ready,
                onSlide: (v) {
                  _stopAuto();
                  _velocity = 0;
                  setState(() => _pos = v);
                },
                onToggleAuto: () => setState(() {
                  _auto = !_auto;
                  if (_auto) _hintVisible = false;
                }),
                onFullscreen: widget.allowFullscreen ? _openFullscreen : null,
              ),
            ],
          );
        }),
      ),
    );
  }
}

class _Badge360 extends StatelessWidget {
  final bool small;
  const _Badge360({this.small = false});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Container(
      padding: EdgeInsets.symmetric(horizontal: small ? 6 : 10, vertical: small ? 3 : 5),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(100),
        gradient: LinearGradient(colors: [scheme.primary, Color.lerp(scheme.primary, const Color(0xFF7C3AED), 0.55)!]),
        boxShadow: [BoxShadow(color: scheme.primary.fade(0.35), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.threesixty_rounded, size: small ? 12 : 16, color: scheme.onPrimary),
        SizedBox(width: small ? 2 : 4),
        Text('360°', style: TextStyle(color: scheme.onPrimary, fontWeight: FontWeight.w800, fontSize: small ? 9.5 : 11.5)),
      ]),
    );
  }
}

/// Public 360° chip for cards.
class Spin360Badge extends StatelessWidget {
  const Spin360Badge({super.key});
  @override
  Widget build(BuildContext context) => const _Badge360(small: true);
}

class _Hint extends StatelessWidget {
  final String label;
  const _Hint({required this.label});

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: scheme.surface.fade(0.85),
        borderRadius: BorderRadius.circular(100),
        border: Border.all(color: scheme.outlineVariant),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.swipe_rounded, size: 16, color: scheme.primary),
        const SizedBox(width: 6),
        Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: scheme.onSurface)),
      ]),
    );
  }
}

class _Toolbar extends StatelessWidget {
  final int n;
  final double pos;
  final bool auto, enabled;
  final ValueChanged<double> onSlide;
  final VoidCallback onToggleAuto;
  final VoidCallback? onFullscreen;

  const _Toolbar({
    required this.n,
    required this.pos,
    required this.auto,
    required this.enabled,
    required this.onSlide,
    required this.onToggleAuto,
    required this.onFullscreen,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    return Positioned(
      left: 10,
      right: 10,
      bottom: 10,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
        decoration: BoxDecoration(
          color: scheme.surface.fade(0.88),
          borderRadius: BorderRadius.circular(context.design.r(18)),
          border: Border.all(color: scheme.outlineVariant.fade(0.8)),
          boxShadow: [BoxShadow(color: Colors.black.fade(0.12), blurRadius: 18, offset: const Offset(0, 8))],
        ),
        child: Row(children: [
          IconButton(
            tooltip: context.tr(auto ? 'spin_pause' : 'spin_play'),
            onPressed: enabled ? onToggleAuto : null,
            icon: Icon(auto ? Icons.pause_rounded : Icons.play_arrow_rounded),
            color: auto ? scheme.primary : null,
          ),
          Expanded(
            child: SliderTheme(
              data: SliderTheme.of(context).copyWith(trackHeight: 5, overlayShape: SliderComponentShape.noOverlay),
              child: Slider(
                min: 0,
                max: math.max(1, n - 1).toDouble(),
                value: pos.clamp(0, math.max(1, n - 1).toDouble()),
                onChanged: enabled ? onSlide : null,
              ),
            ),
          ),
          if (onFullscreen != null)
            IconButton(
              tooltip: context.tr('spin_fullscreen'),
              onPressed: onFullscreen,
              icon: const Icon(Icons.fullscreen_rounded),
            ),
        ]),
      ),
    );
  }
}

class _SpinFullscreen extends StatelessWidget {
  final List<String> frames;
  const _SpinFullscreen({required this.frames});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: context.theme.scaffoldBackgroundColor,
      body: Stack(children: [
        Positioned.fill(
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.all(8),
              child: Product360Viewer(frames: frames, allowFullscreen: false, borderRadius: BorderRadius.circular(context.design.card)),
            ),
          ),
        ),
        Positioned(
          top: MediaQuery.of(context).padding.top + 14,
          right: 16,
          child: IconButton.filledTonal(
            onPressed: () => Navigator.of(context).maybePop(),
            icon: const Icon(Icons.close_rounded),
          ),
        ),
      ]),
    );
  }
}
