import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/api_constants.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/app_actions.dart';
import '../../utils/responsive.dart';
import '../../utils/ui_helpers.dart';
import '../../widgets/auth_widgets.dart';
import '../../widgets/primary_button.dart';
import 'otp_verify_screen.dart';
import 'register_screen.dart';

/// Login with mobile number + OTP. Pops with `true` once the user is logged in
/// (either directly or through the Register flow).
class PhoneLoginScreen extends StatefulWidget {
  final String? initialPhone;
  final String? notice;

  const PhoneLoginScreen({super.key, this.initialPhone, this.notice});

  @override
  State<PhoneLoginScreen> createState() => _PhoneLoginScreenState();
}

class _PhoneLoginScreenState extends State<PhoneLoginScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _phoneController;
  String? _notice;

  @override
  void initState() {
    super.initState();
    _phoneController = TextEditingController(text: widget.initialPhone ?? '');
    _notice = widget.notice;
  }

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _sendOtp() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    FocusScope.of(context).unfocus();
    final phone = _phoneController.text.trim();
    final auth = context.read<AuthProvider>();
    try {
      await auth.sendOtp(phone);
      if (!mounted) return;
      showAppSnack(context, context.tr('otp_sent'), type: SnackType.success);
      final verified = await Navigator.of(context).push<bool>(
        MaterialPageRoute(
          builder: (_) => OtpVerifyScreen(
            phone: phone,
            purpose: OtpPurpose.login,
            demoOtp: auth.lastDemoOtp,
            otpLength: auth.otpLength,
            resendAfter: auth.resendAfter,
          ),
        ),
      );
      if (verified == true && mounted) {
        Navigator.of(context).pop(true);
      }
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.needsRegistration) {
        await _openRegister(phone: phone, notice: context.tr('needs_registration_msg'));
        return;
      }
      showAppSnack(context, errorMessage(e), type: SnackType.error);
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  Future<void> _openRegister({String? phone, String? notice}) async {
    final current = _phoneController.text.trim();
    final result = await Navigator.of(context).push<RegisterResult>(
      MaterialPageRoute(
        builder: (_) => RegisterScreen(
          initialPhone: phone ?? (PhoneField.isValid(current) ? current : null),
          notice: notice,
        ),
      ),
    );
    if (!mounted || result == null) return;
    if (result.loggedIn) {
      Navigator.of(context).pop(true);
      return;
    }
    final loginPhone = result.loginPhone;
    if (loginPhone != null && loginPhone.isNotEmpty) {
      _phoneController.text = loginPhone;
      setState(() => _notice = null);
      if (result.sendOtpNow) _sendOtp();
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = context.watch<AuthProvider>().isLoading;
    final scheme = context.colors;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          tooltip: context.tr('close'),
          onPressed: () => Navigator.of(context).pop(false),
        ),
      ),
      body: SafeArea(
        child: MaxWidthBox(
          maxWidth: 480,
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.sm, AppSpacing.xl, AppSpacing.xl),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  AuthModeSwitch(
                    mode: AuthMode.login,
                    onChanged: (_) => _openRegister(),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(colors: [scheme.primary, scheme.primary.fade(0.7)]),
                      borderRadius: BorderRadius.circular(AppRadius.lg),
                    ),
                    child: Icon(Icons.phone_iphone_rounded, size: 36, color: scheme.onPrimary),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  Text(
                    context.tr('login_title'),
                    style: context.textStyles.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Text(
                    context.tr('login_subtitle'),
                    style: context.textStyles.bodyLarge?.copyWith(color: scheme.onSurfaceVariant, height: 1.4),
                  ),
                  if (_notice != null) ...[
                    const SizedBox(height: AppSpacing.lg),
                    AuthNotice(message: _notice!),
                  ],
                  const SizedBox(height: AppSpacing.xxl),
                  PhoneField(
                    controller: _phoneController,
                    autofocus: true,
                    onSubmitted: (_) => _sendOtp(),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  ValueListenableBuilder<TextEditingValue>(
                    valueListenable: _phoneController,
                    builder: (context, value, _) => PrimaryButton(
                      label: context.tr('send_otp'),
                      icon: Icons.sms_rounded,
                      isLoading: isLoading,
                      onPressed: PhoneField.isValid(value.text) ? _sendOtp : null,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  Center(
                    child: Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          context.tr('new_here'),
                          style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
                        ),
                        TextButton(
                          onPressed: isLoading ? null : () => _openRegister(),
                          child: Text(context.tr('create_account')),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  SizedBox(
                    width: double.infinity,
                    child: Column(
                      children: [
                        Text(
                          context.tr('agree_terms'),
                          textAlign: TextAlign.center,
                          style: context.textStyles.bodySmall?.copyWith(color: scheme.onSurfaceVariant),
                        ),
                        Wrap(
                          alignment: WrapAlignment.center,
                          children: [
                            TextButton(
                              onPressed: () => AppActions.openCmsPage(context, ApiConstants.slugLegal, context.tr('terms_conditions')),
                              child: Text(context.tr('terms_conditions')),
                            ),
                            TextButton(
                              onPressed: () => AppActions.openCmsPage(context, ApiConstants.slugPrivacy, context.tr('privacy_policy')),
                              child: Text(context.tr('privacy_policy')),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
