import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_theme.dart';
import '../../utils/ui_helpers.dart';
import '../../constants/api_constants.dart';
import '../../widgets/app_dropdown.dart';
import '../../widgets/error_state.dart';

enum _WebMenuAction { reload, openInBrowser, copyLink }

/// Reusable in-app browser.
///
/// * Title + host subtitle, linear progress bar, refresh action
/// * Back button navigates inside the web history first (PopScope)
/// * Main-frame load errors show an offline/error state with retry
/// * Overflow menu: Reload, Open in browser, Copy link
/// * Non-http(s) links (tel:, mailto:, whatsapp:, intent:, geo:, upi:, ...)
///   are handed to the OS via url_launcher.
class AppWebViewScreen extends StatefulWidget {
  final String url;
  final String? title;
  final Map<String, String>? headers;

  const AppWebViewScreen({super.key, required this.url, this.title, this.headers});

  /// Opens [url] in the in-app browser in the app's language: appends
  /// `?lang=gu|en` (the website switches language from it) and sends
  /// `Accept-Language` / `X-Locale` headers.
  static Future<void> open(BuildContext context, {required String url, String? title}) {
    final lang = Localizations.localeOf(context).languageCode == 'en' ? 'en' : 'gu';
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => AppWebViewScreen(
          // Only our own website understands ?lang= (external campaign pages are left untouched).
          url: url.startsWith(ApiConstants.webBaseUrl) ? ApiConstants.withLang(url, lang) : url,
          title: title,
          headers: {'Accept-Language': lang, 'X-Locale': lang},
        ),
      ),
    );
  }

  @override
  State<AppWebViewScreen> createState() => _AppWebViewScreenState();
}

class _AppWebViewScreenState extends State<AppWebViewScreen> {
  static const Set<String> _inAppSchemes = {'http', 'https', 'about', 'data', 'blob', 'javascript'};

  late final WebViewController _controller;
  int _progress = 0;
  bool _hasError = false;
  String? _errorDescription;
  String? _pageTitle;
  late String _currentUrl;
  bool _canGoBack = false;

  @override
  void initState() {
    super.initState();
    _currentUrl = widget.url;
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      // The website hides its own header/footer/tab bar for this user agent (see frontend layout).
      ..setUserAgent(
        'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) '
        'Chrome/124.0 Mobile Safari/537.36 FreshExpressApp/1.1',
      )
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (progress) {
            if (mounted) setState(() => _progress = progress);
          },
          onPageStarted: (url) {
            if (!mounted) return;
            setState(() {
              _currentUrl = url;
              _hasError = false;
              _progress = 0;
            });
          },
          onPageFinished: (url) async {
            final title = await _controller.getTitle();
            final canGoBack = await _controller.canGoBack();
            if (!mounted) return;
            setState(() {
              _currentUrl = url;
              _pageTitle = (title != null && title.trim().isNotEmpty) ? title.trim() : _pageTitle;
              _canGoBack = canGoBack;
              _progress = 100;
            });
          },
          onWebResourceError: (error) {
            // Ignore sub-resource failures (images, analytics, ...).
            if (error.isForMainFrame == false) return;
            if (!mounted) return;
            setState(() {
              _hasError = true;
              _errorDescription = error.description;
              _progress = 100;
            });
          },
          onNavigationRequest: (request) {
            final uri = Uri.tryParse(request.url);
            if (uri == null) return NavigationDecision.prevent;
            if (_inAppSchemes.contains(uri.scheme.toLowerCase())) {
              return NavigationDecision.navigate;
            }
            _openExternally(uri);
            return NavigationDecision.prevent;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url), headers: widget.headers ?? const <String, String>{});
  }

  /// tel:, mailto:, whatsapp:, upi:, geo:, intent:// ... -> native apps.
  Future<void> _openExternally(Uri uri) async {
    try {
      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (launched) return;
    } catch (_) {
      // Fall through to the intent fallback below.
    }
    // Android `intent://...;S.browser_fallback_url=...;end` links.
    if (uri.scheme == 'intent') {
      final match = RegExp(r'S\.browser_fallback_url=([^;]+)').firstMatch(uri.toString());
      if (match != null) {
        final fallback = Uri.tryParse(Uri.decodeComponent(match.group(1)!));
        if (fallback != null) {
          await _controller.loadRequest(fallback);
          return;
        }
      }
    }
    if (mounted) showAppSnack(context, context.tr('no_app_to_open'), type: SnackType.error);
  }

  Future<void> _reload() async {
    setState(() {
      _hasError = false;
      _progress = 0;
    });
    final current = await _controller.currentUrl();
    if (current == null || current.isEmpty || current == 'about:blank') {
      await _controller.loadRequest(Uri.parse(_currentUrl), headers: widget.headers ?? const <String, String>{});
    } else {
      await _controller.reload();
    }
  }

  Future<void> _openInBrowser() async {
    final url = await _controller.currentUrl() ?? _currentUrl;
    try {
      final ok = await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
      if (!ok && mounted) showAppSnack(context, context.tr('no_app_to_open'), type: SnackType.error);
    } catch (_) {
      if (mounted) showAppSnack(context, context.tr('no_app_to_open'), type: SnackType.error);
    }
  }

  Future<void> _copyLink() async {
    final url = await _controller.currentUrl() ?? _currentUrl;
    await Clipboard.setData(ClipboardData(text: url));
    if (mounted) showAppSnack(context, context.tr('link_copied'), type: SnackType.success);
  }

  Future<void> _handleBack() async {
    if (await _controller.canGoBack()) {
      await _controller.goBack();
      return;
    }
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final scheme = context.colors;
    final uri = Uri.tryParse(_currentUrl);
    final host = uri?.host ?? '';
    final secure = uri?.scheme == 'https';
    final loading = _progress < 100;

    return PopScope<Object?>(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) _handleBack();
      },
      child: Scaffold(
        appBar: AppBar(
          titleSpacing: 0,
          leading: IconButton(
            tooltip: context.tr('close'),
            icon: const Icon(Icons.close_rounded),
            onPressed: () => Navigator.of(context).pop(),
          ),
          title: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                widget.title ?? _pageTitle ?? host,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: context.textStyles.titleMedium?.copyWith(fontWeight: FontWeight.w800),
              ),
              if (host.isNotEmpty)
                Row(
                  children: [
                    Icon(
                      secure ? Icons.lock_rounded : Icons.info_outline_rounded,
                      size: 12,
                      color: scheme.onSurfaceVariant,
                    ),
                    const SizedBox(width: 4),
                    Flexible(
                      child: Text(
                        host,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: context.textStyles.labelSmall?.copyWith(color: scheme.onSurfaceVariant),
                      ),
                    ),
                  ],
                ),
            ],
          ),
          actions: [
            if (_canGoBack)
              IconButton(
                tooltip: MaterialLocalizations.of(context).backButtonTooltip,
                icon: const Icon(Icons.arrow_back_rounded),
                onPressed: _handleBack,
              ),
            IconButton(
              tooltip: context.tr('refresh'),
              icon: const Icon(Icons.refresh_rounded),
              onPressed: _reload,
            ),
            PopupMenuButton<_WebMenuAction>(
              tooltip: context.tr('more'),
              icon: const Icon(Icons.more_vert_rounded),
              onSelected: (action) {
                switch (action) {
                  case _WebMenuAction.reload:
                    _reload();
                    break;
                  case _WebMenuAction.openInBrowser:
                    _openInBrowser();
                    break;
                  case _WebMenuAction.copyLink:
                    _copyLink();
                    break;
                }
              },
              itemBuilder: (ctx) => [
                appMenuItem(value: _WebMenuAction.reload, icon: Icons.refresh_rounded, label: ctx.tr('reload')),
                appMenuItem(value: _WebMenuAction.openInBrowser, icon: Icons.open_in_browser_rounded, label: ctx.tr('open_in_browser')),
                appMenuItem(value: _WebMenuAction.copyLink, icon: Icons.link_rounded, label: ctx.tr('copy_link')),
              ],
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(3),
            child: AnimatedOpacity(
              opacity: loading ? 1 : 0,
              duration: const Duration(milliseconds: 250),
              child: LinearProgressIndicator(
                value: _progress > 0 && _progress < 100 ? _progress / 100 : null,
                minHeight: 3,
                backgroundColor: Colors.transparent,
              ),
            ),
          ),
        ),
        body: Stack(
          children: [
            WebViewWidget(controller: _controller),
            if (_hasError)
              Positioned.fill(
                child: ColoredBox(
                  color: context.theme.scaffoldBackgroundColor,
                  child: ErrorState(
                    forceOffline: true,
                    message: _errorDescription,
                    onRetry: _reload,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
