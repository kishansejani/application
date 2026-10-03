@extends('emails.layout', ['subject' => __('auth_ui.mail_test_subject')])

@section('body')
    <h1 style="margin:0 0 12px 0;font-size:22px;line-height:30px;font-weight:800;color:#0f172a;">&#9989; {{ __('auth_ui.mail_test_subject') }}</h1>
    <p style="margin:0 0 18px 0;font-size:15px;line-height:24px;color:#475569;">{{ __('auth_ui.mail_test_body') }}</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;color:#334155;background:#f8fafc;border-radius:12px;">
        @foreach($details as $k => $v)
            <tr>
                <td style="padding:8px 14px;font-weight:700;width:120px;">{{ $k }}</td>
                <td style="padding:8px 14px;font-family:Consolas,Menlo,monospace;">{{ $v }}</td>
            </tr>
        @endforeach
    </table>
@endsection
