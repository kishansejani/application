import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/formatters.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/primary_button.dart';

/// Animated OTP verification used by login and sign-up. Pops with `true` once
/// the code is verified (the user is then logged in).
///
/// * Pulsing shield header, staggered OTP boxes, digit "pop" on input
/// * Shake + red state on a wrong code, animated check on success
/// * Circular resend countdown (`resend_after` from the API, default 30 s)
/// * Paste button + long-press paste, SMS autofill, auto-submit when complete
/// * Honours `MediaQuery.disableAnimations`
class OtpVerifyScreen extends StatefulWidget {
  final String phone;
  final OtpPurpose purpose;
  final String? demoOtp;
  final int otpLength;
  final int resendAfter;

  const OtpVerifyScreen({
    super.key,
    required this.phone,
    this.purpose = OtpPurpose.login,
    this.demoOtp,
    this.otpLength = AuthProvider.defaultOtpLength,
    this.resendAfter = AuthProvider.defaultResendAfter,
  });

  @override
  State<OtpVerifyScreen> createState() => _OtpVerifyScreenState();
}

enum _OtpVisual { idle, error, success }

class _OtpVerifyScreenState extends State<OtpVerifyScreen> with TickerProviderStateMixin {
  final _otpController = TextEditingController();
  final _focusNode = FocusNode();

  late final AnimationController _pulse;
  late final AnimationController _entry;
  late final AnimationController _shake;
  late final AnimationController _successCtrl;

  late int _otpLength;
  late int _resendAfter;
  String? _demoOtp;

  // Resend countdown (wall-clock based so it stays correct in the background).
  final ValueNotifier<double> _remaining = ValueNotifier<double>(1);
  Timer? _ticker;
  DateTime? _resendAt;
  bool _canResend = false;
  bool _resending = false;

  String? _error;
  bool _verifying = false;
  bool _success = false;
  int _prevLength = 0;
  bool _reduceMotion = false;
  bool _motionInitialised = false;

  @override
  void initState() {
    super.initState();
    _otpLength = widget.otpLength;
    _resendAfter = widget.resendAfter;
    _demoOtp = widget.demoOtp;

    _pulse = AnimationController(vsync: this, duration: const Duration(milliseconds: 2400));
    _entry = AnimationController(vsync: this, duration: Duration(milliseconds: 650 + _otpLength * 90));
    _shake = AnimationController(vsync: this, duration: const Duration(milliseconds: 480));
    _successCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 750));
    _focusNode.addListener(_onFocusChange);
    _startCountdown();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final reduce = MediaQuery.of(context).disableAnimations;
    if (_motionInitialised && reduce == _reduceMotion) return;
    _motionInitialised = true;
    _reduceMotion = reduce;
    if (reduce) {
      _pulse.stop();
      _pulse.value = 0;
      _entry.value = 1;
    } else {
      if (!_pulse.isAnimating) _pulse.repeat();
      if (_entry.value == 0) _entry.forward();
    }
  }

  @override
  void dispose() {
    _ticker?.cancel();
    _remaining.dispose();
    _pulse.dispose();
    _entry.dispose();
    _shake.dispose();
    _successCtrl.dispose();
    _focusNode.removeListener(_onFocusChange);
    _otpController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _onFocusChange() {
    if (mounted) setState(() {});
  }

  // ---------------------------------------------------------------------------
  // Countdown
  // ---------------------------------------------------------------------------
  void _startCountdown() {
    _ticker?.cancel();
    _resendAt = DateTime.now().add(Duration(seconds: _resendAfter));
    _canResend = false;
    _remaining.value = 1;
    _ticker = Timer.periodic(const Duration(milliseconds: 100), (_) => _tick());
  }

  void _tick() {
    final at = _resendAt;
    if (at == null) return;
    final leftMs = at.difference(DateTime.now()).inMilliseconds;
    if (leftMs <= 0) {
      _ticker?.cancel();
      _remaining.value = 0;
      if (mounted && !_canResend) setState(() => _canResend = true);
      return;
    }
    _remaining.value = leftMs / (_resendAfter * 1000);
  }

  // ---------------------------------------------------------------------------
  // Input
  // ---------------------------------------------------------------------------
  void _onChanged(String value) {
    if (_success) return;
    if (value.length > _prevLength) HapticFeedback.lightImpact();
    _prevLength = value.length;
    setState(() => _error = null);
    if (value.length == _otpLength) _verify();
  }

  Future<void> _paste() async {
    final data = await Clipboard.getData(Clipboard.kTextPlain);
    if (!mounted) return;
    final digits = (data?.text ?? '').replaceAll(RegExp(r'[^0-9]'), '');
    if (digits.length < _otpLength) {
      showAppSnack(context, context.tr('no_code_in_clipboard'), type: SnackType.info);
      return;
    }
    _fill(digits.substring(0, _otpLength));
  }

  void _fill(String code) {
    _otpController.value = TextEditingValue(
      text: code,
      selection: TextSelection.collapsed(offset: code.length),
    );
    HapticFeedback.lightImpact();
    setState(() {
      _error = null;
      _prevLength = code.length;
    });
    _verify();
  }

  // ---------------------------------------------------------------------------
  // Verify / resend
  // ---------------------------------------------------------------------------
  Future<void> _verify() async {
    if (_verifying || _success) return;
    final otp = _otpController.text.trim();
    if (otp.length != _otpLength) {
      _onWrong(context.tr('enter_otp', {'length': '$_otpLength'}), clear: false);
      return;
    }
    setState(() {
      _verifying = true;
      _error = null;
    });
    final auth = context.read<AuthProvider>();
    try {
      final ok = await auth.verifyOtp(widget.phone, otp, purpose: widget.purpose);
      if (!mounted) return;
      if (ok) {
        setState(() => _verifying = false);
        await _onSuccess();
        return;
      }
      _onWrong(context.tr('invalid_otp'));
    } on ApiException catch (e) {
      if (!mounted) return;
      final wrongCode = e.statusCode == 422 || e.statusCode == 400;
      // OTP errors from the API carry a `reason` and an already translated message.
      final message = e.reason != null ? e.message : (wrongCode ? context.tr('invalid_otp') : e.message);
      const codeGone = {'expired', 'too_many_attempts', 'not_found'};
      if (codeGone.contains(e.reason)) {
        // The code can no longer be used - let the user request a new one right away.
        _ticker?.cancel();
        _remaining.value = 0;
        _canResend = true;
      }
      _onWrong(message, clear: wrongCode);
    } catch (e) {
      if (!mounted) return;
      _onWrong(errorMessage(e), clear: false);
    } finally {
      if (mounted && _verifying) setState(() => _verifying = false);
    }
  }

  void _onWrong(String message, {bool clear = true}) {
    HapticFeedback.heavyImpact();
    setState(() => _error = message);
    if (!_reduceMotion) _shake.forward(from: 0);
    if (!clear) return;
    final wrong = _otpController.text;
    // Keep the red digits visible during the shake, then clear for a new try.
    Future<void>.delayed(Duration(milliseconds: _reduceMotion ? 900 : 650), () {
      if (!mounted || _success || _otpController.text != wrong) return;
      _otpController.clear();
      _prevLength = 0;
      setState(() {});
      _focusNode.requestFocus();
    });
  }

  Future<void> _onSuccess() async {
    HapticFeedback.mediumImpact();
    setState(() {
      _success = true;
      _error = null;
    });
    _focusNode.unfocus();
    AppActions.loadUserData(context);
    final message = context.tr(widget.purpose == OtpPurpose.register ? 'register_success' : 'login_success');
    if (_reduceMotion) {
      _successCtrl.value = 1;
      await Future<void>.delayed(const Duration(milliseconds: 300));
    } else {
      await _successCtrl.forward(from: 0);
      await Future<void>.delayed(const Duration(milliseconds: 350));
    }
    if (!mounted) return;
    showAppSnack(context, message, type: SnackType.success);
    Navigator.of(context).pop(true);
  }

  Future<void> _resend() async {
    if (_resending || !_canResend || _success) return;
    setState(() => _resending = true);
    final auth = context.read<AuthProvider>();
    try {
      await auth.resendOtp(widget.phone, widget.purpose);
      if (!mounted) return;
      HapticFeedback.lightImpact();
      _otpController.clear();
      _prevLength = 0;
      setState(() {
        _error = null;
        _otpLength = auth.otpLength;
        _resendAfter = auth.resendAfter;
        _demoOtp = auth.lastDemoOtp;
      });
      _startCountdown();
      if (!_reduceMotion) _entry.forward(from: 0);
      showAppSnack(context, context.tr('otp_resent'), type: SnackType.success);
      _focusNode.requestFocus();
    } on ApiException catch (e) {
      if (!mounted) return;
      final wait = e.retryAfter;
      if (e.statusCode == 429 && wait != null && wait > 0) {
        // Server-side cooldown: show it on the circular countdown.
        setState(() => _resendAfter = wait);
        _startCountdown();
      }
      showAppSnack(context, errorMessage(e), type: SnackType.error);
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    } finally {
      if (mounted) setState(() => _resending = false);
    }
  }

  // ---------------------------------------------------------------------------
  // UI
  // ---------------------------------------------------------------------------
  _OtpVisual get _visual {
    if (_success) return _OtpVisual.success;
    if (_error != null) return _OtpVisual.error;
    return _OtpVisual.idle;
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final otp = _otpController.text;

    return Scaffold(
      appBar: AppBar(),
      body: SafeArea(
        child: MaxWidthBox(
          maxWidth: 480,
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(AppSpacing.xl, 0, AppSpacing.xl, AppSpacing.xl),
            child: Column(
              children: [
                ExcludeSemantics(
                  child: _OtpHeader(
                    pulse: _pulse,
                    success: _successCtrl,
                    visual: _visual,
                    reduceMotion: _reduceMotion,
                  ),
                ),
                const SizedBox(height: AppSpacing.lg),
                AnimatedSwitcher(
                  duration: _reduceMotion ? Duration.zero : const Duration(milliseconds: 250),
                  child: Text(
                    context.tr(_success ? 'otp_verified' : 'verify_otp'),
                    key: ValueKey<bool>(_success),
                    textAlign: TextAlign.center,
                    style: context.textStyles.headlineSmall?.copyWith(
                      fontWeight: FontWeight.w800,
                      color: _success ? scheme.primary : scheme.onSurface,
                    ),
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                Text.rich(
                  TextSpan(
                    text: '${context.tr('otp_sent_to', {'length': '$_otpLength'})} ',
                    children: [
                      TextSpan(
                        text: Fmt.phone(widget.phone),
                        style: TextStyle(fontWeight: FontWeight.w800, color: scheme.onSurface),
                      ),
                    ],
                  ),
                  textAlign: TextAlign.center,
                  style: context.textStyles.bodyLarge?.copyWith(color: scheme.onSurfaceVariant, height: 1.4),
                ),
                TextButton.icon(
                  onPressed: _success ? null : () => Navigator.of(context).pop(false),
                  icon: const Icon(Icons.edit_rounded, size: 16),
                  label: Text(context.tr('change_number')),
                ),
                const SizedBox(height: AppSpacing.lg),
                _buildBoxes(otp),
                AnimatedSize(
                  duration: _reduceMotion ? Duration.zero : const Duration(milliseconds: 200),
                  alignment: Alignment.topCenter,
                  child: _error == null
                      ? const SizedBox(width: double.infinity)
                      : Padding(
                          padding: const EdgeInsets.only(top: AppSpacing.md),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.error_outline_rounded, size: 18, color: scheme.error),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Semantics(
                                  liveRegion: true,
                                  child: Text(
                                    _error!,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(color: scheme.error, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                ),
                const SizedBox(height: AppSpacing.sm),
                TextButton.icon(
                  onPressed: (_success || _verifying) ? null : _paste,
                  icon: const Icon(Icons.content_paste_rounded, size: 18),
                  label: Text(context.tr('paste_code')),
                ),
                const SizedBox(height: AppSpacing.md),
                PrimaryButton(
                  label: context.tr(_verifying ? 'verifying' : (_success ? 'otp_verified' : 'verify_otp')),
                  icon: _success ? Icons.check_circle_rounded : Icons.lock_open_rounded,
                  isLoading: _verifying,
                  onPressed: (otp.length == _otpLength && !_success) ? _verify : null,
                ),
                const SizedBox(height: AppSpacing.xl),
                _buildResend(),
                if (_demoOtp != null && !_success) ...[
                  const SizedBox(height: AppSpacing.lg),
                  ActionChip(
                    avatar: const Icon(Icons.bolt_rounded, size: 18),
                    label: Text(context.tr('demo_code_hint', {'otp': _demoOtp!})),
                    onPressed: () {
                      final code = _demoOtp!;
                      if (code.length == _otpLength) _fill(code);
                    },
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildBoxes(String otp) {
    final scheme = context.colors;
    return LayoutBuilder(
      builder: (context, constraints) {
        const gap = AppSpacing.md;
        final maxW = constraints.maxWidth;
        final boxW = math.min(60.0, (maxW - gap * (_otpLength - 1)) / _otpLength);
        final boxH = boxW * 1.15;

        final boxes = AnimatedBuilder(
          animation: Listenable.merge([_entry, _pulse]),
          builder: (context, _) {
            return Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_otpLength, (i) {
                final start = (i / _otpLength) * 0.5;
                final t = Interval(start, math.min(1.0, start + 0.5)).transform(_entry.value);
                final scale = Curves.easeOutBack.transform(t);
                final caretOn = _reduceMotion || ((_pulse.value * 4) % 1.0) < 0.55;
                return Padding(
                  padding: EdgeInsetsDirectional.only(end: i == _otpLength - 1 ? 0 : gap),
                  child: Opacity(
                    opacity: t.clamp(0.0, 1.0).toDouble(),
                    child: Transform.translate(
                      offset: Offset(0, (1 - t) * 18),
                      child: Transform.scale(
                        scale: 0.6 + 0.4 * scale,
                        child: _OtpBox(
                          width: boxW,
                          height: boxH,
                          digit: i < otp.length ? otp[i] : '',
                          index: i,
                          active: i == otp.length && _focusNode.hasFocus && !_success,
                          showCaret: caretOn,
                          visual: _visual,
                          reduceMotion: _reduceMotion,
                        ),
                      ),
                    ),
                  ),
                );
              }),
            );
          },
        );

        final shaken = AnimatedBuilder(
          animation: _shake,
          builder: (context, child) {
            final v = _shake.value;
            final dx = (v == 0 || v == 1) ? 0.0 : math.sin(v * math.pi * 8) * 10 * (1 - v);
            return Transform.translate(offset: Offset(dx, 0), child: child);
          },
          child: boxes,
        );

        return Stack(
          children: [
            shaken,
            // A single (almost) invisible TextField drives the boxes: robust
            // paste (long-press), SMS autofill and backspace behaviour.
            Positioned.fill(
              child: Opacity(
                opacity: 0.01,
                child: TextField(
                  controller: _otpController,
                  focusNode: _focusNode,
                  autofocus: true,
                  enabled: !_success,
                  keyboardType: TextInputType.number,
                  maxLength: _otpLength,
                  showCursor: false,
                  autofillHints: const [AutofillHints.oneTimeCode],
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(_otpLength),
                  ],
                  style: TextStyle(color: scheme.surface.fade(0)),
                  decoration: const InputDecoration(
                    counterText: '',
                    filled: false,
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    errorBorder: InputBorder.none,
                    disabledBorder: InputBorder.none,
                    focusedErrorBorder: InputBorder.none,
                    contentPadding: EdgeInsets.zero,
                  ),
                  onChanged: _onChanged,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildResend() {
    final scheme = context.colors;
    if (_canResend) {
      return Column(
        children: [
          Text(
            context.tr('didnt_receive_code'),
            style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
          ),
          TextButton.icon(
            onPressed: (_resending || _success) ? null : _resend,
            icon: _resending
                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.refresh_rounded, size: 18),
            label: Text(context.tr('resend_otp')),
          ),
        ],
      );
    }
    return ValueListenableBuilder<double>(
      valueListenable: _remaining,
      builder: (context, fraction, _) {
        final seconds = math.max(1, (fraction * _resendAfter).ceil());
        return Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              width: 38,
              height: 38,
              child: Stack(
                alignment: Alignment.center,
                children: [
                  SizedBox.expand(
                    child: CircularProgressIndicator(
                      value: fraction.clamp(0.0, 1.0).toDouble(),
                      strokeWidth: 3,
                      color: scheme.primary,
                      backgroundColor: scheme.outlineVariant.fade(0.45),
                    ),
                  ),
                  Text(
                    '$seconds',
                    style: context.textStyles.labelMedium?.copyWith(fontWeight: FontWeight.w800, color: scheme.primary),
                  ),
                ],
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Flexible(
              child: Text(
                context.tr('resend_in', {'seconds': '$seconds'}),
                style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
              ),
            ),
          ],
        );
      },
    );
  }
}

/// One OTP digit box with state colours and a "pop" animation for new digits.
class _OtpBox extends StatelessWidget {
  final double width;
  final double height;
  final String digit;
  final int index;
  final bool active;
  final bool showCaret;
  final _OtpVisual visual;
  final bool reduceMotion;

  const _OtpBox({
    required this.width,
    required this.height,
    required this.digit,
    required this.index,
    required this.active,
    required this.showCaret,
    required this.visual,
    required this.reduceMotion,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final filled = digit.isNotEmpty;

    Color fill = scheme.surface;
    Color border = scheme.outlineVariant;
    Color textColor = scheme.onSurface;
    double borderWidth = 1.4;
    switch (visual) {
      case _OtpVisual.success:
        fill = scheme.primary;
        border = scheme.primary;
        textColor = scheme.onPrimary;
        break;
      case _OtpVisual.error:
        fill = scheme.errorContainer.fade(context.isDark ? 0.35 : 0.6);
        border = scheme.error;
        textColor = scheme.error;
        borderWidth = 1.8;
        break;
      case _OtpVisual.idle:
        if (active) {
          fill = scheme.surface;
          border = scheme.primary;
          borderWidth = 2;
        } else if (filled) {
          fill = scheme.primaryContainer.fade(0.45);
          border = scheme.primary.fade(0.6);
        } else {
          fill = context.isDark ? scheme.surfaceContainerHigh : scheme.surface;
          border = scheme.outlineVariant;
        }
        break;
    }

    return AnimatedContainer(
      duration: reduceMotion ? Duration.zero : const Duration(milliseconds: 200),
      curve: Curves.easeOut,
      width: width,
      height: height,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: fill,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: border, width: borderWidth),
        boxShadow: active && visual == _OtpVisual.idle
            ? [BoxShadow(color: scheme.primary.fade(0.22), blurRadius: 14, spreadRadius: 1)]
            : const [],
      ),
      child: AnimatedSwitcher(
        duration: reduceMotion ? Duration.zero : const Duration(milliseconds: 240),
        switchInCurve: Curves.easeOutBack,
        switchOutCurve: Curves.easeIn,
        transitionBuilder: (child, animation) => ScaleTransition(
          scale: animation,
          child: FadeTransition(opacity: animation, child: child),
        ),
        child: filled
            ? Text(
                digit,
                key: ValueKey<String>('d$index$digit'),
                style: context.textStyles.headlineSmall?.copyWith(fontWeight: FontWeight.w800, color: textColor),
              )
            : (active
                ? AnimatedOpacity(
                    key: const ValueKey<String>('caret'),
                    opacity: showCaret ? 1 : 0,
                    duration: reduceMotion ? Duration.zero : const Duration(milliseconds: 120),
                    child: Container(
                      width: 2,
                      height: height * 0.4,
                      decoration: BoxDecoration(
                        color: scheme.primary,
                        borderRadius: BorderRadius.circular(1),
                      ),
                    ),
                  )
                : SizedBox(key: const ValueKey<String>('empty'), width: 2, height: height * 0.4)),
      ),
    );
  }
}

/// Pulsing shield illustration that turns red on error and draws a check on success.
class _OtpHeader extends StatelessWidget {
  final Animation<double> pulse;
  final Animation<double> success;
  final _OtpVisual visual;
  final bool reduceMotion;

  const _OtpHeader({
    required this.pulse,
    required this.success,
    required this.visual,
    required this.reduceMotion,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final Color tone = visual == _OtpVisual.error ? scheme.error : scheme.primary;
    final Color onTone = visual == _OtpVisual.error ? scheme.onError : scheme.onPrimary;

    return SizedBox(
      width: 150,
      height: 150,
      child: AnimatedBuilder(
        animation: Listenable.merge([pulse, success]),
        builder: (context, _) {
          final p = pulse.value;
          final s = success.value;
          final float = reduceMotion ? 0.0 : math.sin(p * math.pi * 2) * 3;
          final bounce = visual == _OtpVisual.success ? 1 + 0.12 * math.sin(s * math.pi) : 1.0;

          return Stack(
            alignment: Alignment.center,
            children: [
              Positioned.fill(
                child: CustomPaint(
                  painter: _PulsePainter(
                    progress: p,
                    color: tone,
                    animate: !reduceMotion && visual != _OtpVisual.success,
                  ),
                ),
              ),
              Transform.translate(
                offset: Offset(0, float),
                child: Transform.scale(
                  scale: bounce,
                  child: AnimatedContainer(
                    duration: reduceMotion ? Duration.zero : const Duration(milliseconds: 250),
                    width: 82,
                    height: 82,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [tone, Color.lerp(tone, Colors.black, 0.25) ?? tone],
                      ),
                      boxShadow: [BoxShadow(color: tone.fade(0.35), blurRadius: 18, offset: const Offset(0, 6))],
                    ),
                    child: visual == _OtpVisual.success
                        ? CustomPaint(painter: _CheckPainter(progress: s, color: onTone))
                        : Icon(
                            visual == _OtpVisual.error ? Icons.gpp_bad_rounded : Icons.verified_user_rounded,
                            size: 40,
                            color: onTone,
                          ),
                  ),
                ),
              ),
              if (visual == _OtpVisual.idle)
                Positioned(
                  right: 22,
                  top: 26 + float,
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: scheme.surface,
                      shape: BoxShape.circle,
                      boxShadow: [BoxShadow(color: Colors.black.fade(0.12), blurRadius: 8)],
                    ),
                    child: Icon(Icons.sms_rounded, size: 18, color: scheme.primary),
                  ),
                ),
            ],
          );
        },
      ),
    );
  }
}

class _PulsePainter extends CustomPainter {
  final double progress;
  final Color color;
  final bool animate;

  _PulsePainter({required this.progress, required this.color, required this.animate});

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final maxR = size.shortestSide / 2;
    const innerR = 42.0;

    // Soft static halo.
    canvas.drawCircle(center, innerR + 12, Paint()..color = color.fade(0.10));
    if (!animate) return;

    for (var k = 0; k < 3; k++) {
      final t = (progress + k / 3) % 1.0;
      final r = innerR + (maxR - innerR) * Curves.easeOut.transform(t);
      final paint = Paint()
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2
        ..color = color.fade((1 - t) * 0.45);
      canvas.drawCircle(center, r, paint);
    }
  }

  @override
  bool shouldRepaint(_PulsePainter old) =>
      old.progress != progress || old.color != color || old.animate != animate;
}

class _CheckPainter extends CustomPainter {
  final double progress;
  final Color color;

  _CheckPainter({required this.progress, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final path = Path()
      ..moveTo(w * 0.28, h * 0.52)
      ..lineTo(w * 0.44, h * 0.67)
      ..lineTo(w * 0.73, h * 0.37);
    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = 6
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round
      ..color = color;
    final t = Curves.easeOutCubic.transform(progress.clamp(0.0, 1.0).toDouble());
    for (final metric in path.computeMetrics()) {
      canvas.drawPath(metric.extractPath(0, metric.length * t), paint);
    }
  }

  @override
  bool shouldRepaint(_CheckPainter old) => old.progress != progress || old.color != color;
}
