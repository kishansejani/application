<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 shadow-xl border border-slate-100">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Enter Verification Code</h1>
            <p class="text-sm text-slate-500 mt-1">We sent a 4-digit code to <strong class="text-slate-800">{{ $target }}</strong></p>
            <div class="mt-2 inline-block px-3 py-1 bg-amber-50 border border-amber-200 rounded-full text-xs font-semibold text-amber-700">
                Demo OTP: <strong class="text-sm tracking-widest text-black">1234</strong>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.verify.post') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 text-center">Enter 4-Digit OTP</label>
                <div class="flex justify-center">
                    <input type="text" name="otp" maxlength="4" required autofocus placeholder="1234"
                           class="w-44 text-center tracking-[0.8em] text-3xl font-black py-3 border-2 border-slate-300 rounded-2xl bg-slate-50 text-slate-900 focus:outline-none focus:border-black focus:ring-4 focus:ring-slate-200">
                </div>
                @error('otp') <span class="text-xs text-rose-500 text-center block mt-2 font-medium">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full py-3.5 bg-black hover:bg-slate-900 text-white font-bold rounded-xl shadow-lg transition active:scale-95">
                Verify OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('password.forgot') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-1"></i> Resend or change address
            </a>
        </div>
    </div>
</body>
</html>
