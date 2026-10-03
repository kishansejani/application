{{-- Shared e-mail shell: table layout + inline styles so it renders in Gmail, Outlook and Apple Mail --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $subject ?? 'Fresh Express' }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,'Noto Sans Gujarati',sans-serif;color:#0f172a;">
    @isset($preheader)
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>
    @endisset
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                    <tr>
                        <td style="padding:0 4px 18px 4px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:40px;height:40px;border-radius:12px;background:#059669;color:#ffffff;text-align:center;font-size:20px;font-weight:800;line-height:40px;">F</td>
                                    <td style="padding-left:10px;font-size:18px;font-weight:800;color:#0f172a;letter-spacing:-.2px;">Fresh <span style="color:#059669;">Express</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border-radius:20px;border:1px solid #e2e8f0;overflow:hidden;">
                            <div style="height:6px;background:#10b981;background-image:linear-gradient(90deg,#34d399,#059669,#0f766e);"></div>
                            <div style="padding:32px 32px 28px 32px;">
                                @yield('body')
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 8px 0 8px;text-align:center;font-size:12px;line-height:18px;color:#94a3b8;">
                            {{ __('auth_ui.mail_footer') }}<br>
                            <a href="{{ url('/') }}" style="color:#64748b;text-decoration:underline;">{{ preg_replace('#^https?://#', '', url('/')) }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
