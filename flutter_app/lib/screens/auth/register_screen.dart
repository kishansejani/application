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
import '../../widgets/app_dropdown.dart';
import '../../widgets/auth_widgets.dart';
import '../../widgets/primary_button.dart';
import 'otp_verify_screen.dart';

/// What the Register screen hands back to the Login screen.
class RegisterResult {
  /// The account was created and the user is now logged in.
  final bool loggedIn;

  /// The user chose to log in instead (switch / "already registered").
  final String? loginPhone;

  /// Immediately send a login OTP to [loginPhone].
  final bool sendOtpNow;

  const RegisterResult({this.loggedIn = false, this.loginPhone, this.sendOtpNow = false});
}

/// Sign up: full name, mobile, optional email, language, terms -> OTP -> logged in.
class RegisterScreen extends StatefulWidget {
  final String? initialPhone;
  final String? notice;

  const RegisterScreen({super.key, this.initialPhone, this.notice});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  late final TextEditingController _phoneController;
  String? _language;
  bool _accepted = false;
  bool _termsError = false;

  @override
  void initState() {
    super.initState();
    _phoneController = TextEditingController(text: widget.initialPhone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  void _backToLogin({bool sendOtpNow = false}) {
    final phone = _phoneController.text.trim();
    Navigator.of(context).pop(
      RegisterResult(loginPhone: PhoneField.isValid(phone) ? phone : null, sendOtpNow: sendOtpNow),
    );
  }

  Future<void> _changeLanguage(String code) async {
    setState(() => _language = code);
    // Switch the whole app right away so the user sees the language they picked.
    await AppActions.changeLanguage(context, code);
  }

  Future<void> _submit() async {
    final valid = _formKey.currentState?.validate() ?? false;
    if (!_accepted) setState(() => _termsError = true);
    if (!valid || !_accepted) return;
    FocusScope.of(context).unfocus();

    final auth = context.read<AuthProvider>();
    final phone = _phoneController.text.trim();
    final language = _language ?? context.langCode;
    try {
      await auth.register(
        name: _nameController.text.trim(),
        phone: phone,
        email: _emailController.text.trim(),
        language: language,
      );
      if (!mounted) return;
      showAppSnack(context, context.tr('otp_sent'), type: SnackType.success);
      final verified = await Navigator.of(context).push<bool>(
        MaterialPageRoute(
          builder: (_) => OtpVerifyScreen(
            phone: phone,
            purpose: OtpPurpose.register,
            demoOtp: auth.lastDemoOtp,
            otpLength: auth.otpLength,
            resendAfter: auth.resendAfter,
          ),
        ),
      );
      if (verified == true && mounted) {
        Navigator.of(context).pop(const RegisterResult(loggedIn: true));
      }
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.alreadyRegistered) {
        await _offerLogin();
        return;
      }
      showAppSnack(context, errorMessage(e), type: SnackType.error);
    } catch (e) {
      if (mounted) showAppSnack(context, errorMessage(e), type: SnackType.error);
    }
  }

  Future<void> _offerLogin() async {
    final login = await confirmDialog(
      context,
      title: context.tr('already_registered_title'),
      message: context.tr('already_registered_msg'),
      confirmLabel: context.tr('login_with_otp'),
      icon: Icons.how_to_reg_rounded,
    );
    if (login && mounted) _backToLogin(sendOtpNow: true);
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = context.watch<AuthProvider>().isLoading;
    final scheme = context.colors;
    final language = _language ?? context.langCode;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          tooltip: MaterialLocalizations.of(context).backButtonTooltip,
          onPressed: () => Navigator.of(context).pop(),
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
                    mode: AuthMode.register,
                    onChanged: (_) => _backToLogin(),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(colors: [scheme.primary, scheme.primary.fade(0.7)]),
                      borderRadius: BorderRadius.circular(AppRadius.lg),
                    ),
                    child: Icon(Icons.person_add_alt_1_rounded, size: 36, color: scheme.onPrimary),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  Text(
                    context.tr('register_title'),
                    style: context.textStyles.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  Text(
                    context.tr('register_subtitle'),
                    style: context.textStyles.bodyLarge?.copyWith(color: scheme.onSurfaceVariant, height: 1.4),
                  ),
                  if (widget.notice != null) ...[
                    const SizedBox(height: AppSpacing.lg),
                    AuthNotice(message: widget.notice!, icon: Icons.person_search_rounded),
                  ],
                  const SizedBox(height: AppSpacing.xl),
                  TextFormField(
                    controller: _nameController,
                    autofocus: widget.initialPhone != null,
                    textCapitalization: TextCapitalization.words,
                    textInputAction: TextInputAction.next,
                    autofillHints: const [AutofillHints.name],
                    decoration: InputDecoration(
                      labelText: context.tr('full_name'),
                      prefixIcon: const Icon(Icons.person_outline_rounded),
                    ),
                    validator: (v) => (v == null || v.trim().length < 2) ? context.tr('name_required') : null,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  PhoneField(
                    controller: _phoneController,
                    textInputAction: TextInputAction.next,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.done,
                    autofillHints: const [AutofillHints.email],
                    decoration: InputDecoration(
                      labelText: '${context.tr('email')} (${context.tr('optional')})',
                      prefixIcon: const Icon(Icons.email_outlined),
                    ),
                    validator: (v) {
                      final value = v?.trim() ?? '';
                      if (value.isEmpty) return null;
                      return RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value) ? null : context.tr('invalid_email');
                    },
                  ),
                  const SizedBox(height: AppSpacing.md),
                  AppDropdownField<String>(
                    value: language,
                    label: context.tr('preferred_language'),
                    prefixIcon: Icons.translate_rounded,
                    items: [
                      AppDropdownItem(value: 'en', label: context.tr('lang_en')),
                      AppDropdownItem(value: 'gu', label: context.tr('lang_gu')),
                    ],
                    onChanged: _changeLanguage,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Checkbox(
                        value: _accepted,
                        isError: _termsError && !_accepted,
                        onChanged: (v) => setState(() {
                          _accepted = v ?? false;
                          if (_accepted) _termsError = false;
                        }),
                      ),
                      Expanded(
                        child: Wrap(
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(context.tr('accept_terms_prefix'), style: context.textStyles.bodyMedium),
                            TextButton(
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(horizontal: 4),
                                minimumSize: const Size(0, 36),
                              ),
                              onPressed: () => AppActions.openCmsPage(context, ApiConstants.slugLegal, context.tr('terms_conditions')),
                              child: Text(context.tr('terms_conditions')),
                            ),
                            Text(context.tr('and'), style: context.textStyles.bodyMedium),
                            TextButton(
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(horizontal: 4),
                                minimumSize: const Size(0, 36),
                              ),
                              onPressed: () => AppActions.openCmsPage(context, ApiConstants.slugPrivacy, context.tr('privacy_policy')),
                              child: Text(context.tr('privacy_policy')),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (_termsError && !_accepted)
                    Padding(
                      padding: const EdgeInsets.only(left: AppSpacing.md),
                      child: Text(
                        context.tr('accept_terms_required'),
                        style: context.textStyles.bodySmall?.copyWith(color: scheme.error, fontWeight: FontWeight.w600),
                      ),
                    ),
                  const SizedBox(height: AppSpacing.xl),
                  PrimaryButton(
                    label: context.tr('create_account'),
                    icon: Icons.sms_rounded,
                    isLoading: isLoading,
                    onPressed: _submit,
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  Center(
                    child: Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          context.tr('already_have_account'),
                          style: context.textStyles.bodyMedium?.copyWith(color: scheme.onSurfaceVariant),
                        ),
                        TextButton(
                          onPressed: isLoading ? null : () => _backToLogin(),
                          child: Text(context.tr('login')),
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
