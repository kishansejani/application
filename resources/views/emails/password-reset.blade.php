@extends('emails.layout', [
    'subject' => __('auth_ui.mail_subject', ['code' => $code]),
    'preheader' => __('auth_ui.mail_preheader', ['code' => $code, 'minutes' => $minutes]),
])

@section('body')
    <p style="margin:0 0 6px 0;font-size:13px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#059669;">{{ __('auth_ui.forgot_badge') }}</p>
    <h1 style="margin:0 0 14px 0;font-size:22px;line-height:30px;font-weight:800;color:#0f172a;">{{ __('auth_ui.mail_greeting', ['name' => $user->name ?: 'there']) }}</h1>
    <p style="margin:0 0 22px 0;font-size:15px;line-height:24px;color:#475569;">{{ __('auth_ui.mail_intro') }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="background:#ecfdf5;border:1px dashed #6ee7b7;border-radius:16px;padding:22px 12px;">
                <div style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:40px;line-height:44px;font-weight:800;letter-spacing:14px;color:#065f46;padding-left:14px;">{{ $code }}</div>
                <div style="margin-top:10px;font-size:13px;color:#047857;font-weight:600;">&#9201; {{ __('auth_ui.mail_expiry', ['minutes' => $minutes]) }}</div>
            </td>
        </tr>
    </table>

    @if($resetUrl)
        <p style="margin:26px 0 12px 0;font-size:14px;line-height:22px;color:#475569;text-align:center;">{{ __('auth_ui.mail_or') }}</p>
        <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
            <tr>
                <td style="border-radius:12px;background:#059669;">
                    <a href="{{ $resetUrl }}" target="_blank" style="display:inline-block;padding:14px 30px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:12px;">{{ __('auth_ui.mail_button') }} &rarr;</a>
                </td>
            </tr>
        </table>
        <p style="margin:10px 0 0 0;font-size:12px;color:#94a3b8;text-align:center;">{{ __('auth_ui.mail_link_expiry', ['minutes' => $linkMinutes]) }}</p>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:26px;">
        <tr>
            <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:12px 14px;font-size:13px;line-height:20px;color:#92400e;">
                &#128274; {{ __('auth_ui.mail_security') }}
            </td>
        </tr>
    </table>

    <p style="margin:22px 0 0 0;font-size:13px;line-height:20px;color:#64748b;">{{ __('auth_ui.mail_ignore') }}</p>

    @if($resetUrl)
        <p style="margin:18px 0 0 0;padding-top:16px;border-top:1px solid #e2e8f0;font-size:11px;line-height:17px;color:#94a3b8;word-break:break-all;">
            {{ __('auth_ui.mail_trouble') }}<br><a href="{{ $resetUrl }}" style="color:#059669;">{{ $resetUrl }}</a>
        </p>
    @endif
@endsection
