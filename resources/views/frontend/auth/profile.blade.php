@extends('frontend.layouts.app')

@section('title', __('messages.profile') . ' - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ __('messages.my_profile') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('messages.manage_profile_desc') ?? 'Manage your personal details, language preferences and settings' }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sidebar Navigation / Quick Stats -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-primary-600 to-emerald-400 text-white flex items-center justify-center text-3xl font-black mx-auto mb-3 shadow-lg shadow-primary-500/20">
                    {{ strtoupper(substr(auth()->user()->name ?: 'U', 0, 1)) }}
                </div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ auth()->user()->name ?: __('messages.customer') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-mono">{{ auth()->user()->phone }}</p>
                @if(auth()->user()->email)
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ auth()->user()->email }}</p>
                @endif
                <span class="inline-block mt-3 px-3 py-1 bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 text-xs font-semibold rounded-full">
                    {{ __('messages.verified_user') ?? 'Verified Customer' }}
                </span>
            </div>

            <!-- Quick Links -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm divide-y divide-slate-100 dark:divide-slate-700">
                <a href="{{ route('orders.index') }}" class="flex items-center justify-between p-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center"><i class="fas fa-box"></i></div>
                        <span>{{ __('messages.my_orders') }}</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                </a>
                <a href="{{ route('wishlist.index') }}" class="flex items-center justify-between p-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center"><i class="fas fa-heart"></i></div>
                        <span>{{ __('messages.wishlist') }}</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                </a>
                <a href="{{ route('addresses.index') }}" class="flex items-center justify-between p-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center"><i class="fas fa-map-marker-alt"></i></div>
                        <span>{{ __('messages.saved_addresses') }}</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                </a>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm">
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.full_name') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-white focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.phone') }}
                            </label>
                            <input type="text" value="{{ auth()->user()->phone }}" disabled
                                   class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 cursor-not-allowed">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('messages.phone_cannot_be_changed') ?? 'Phone number is verified and tied to account' }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.email') }}
                            </label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                   placeholder="example@mail.com"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-white focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                {{ __('messages.preferred_language') }}
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3.5 border rounded-2xl cursor-pointer transition {{ app()->getLocale() == 'en' ? 'border-primary-500 bg-primary-50/40 dark:bg-primary-950/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                                    <input type="radio" name="locale" value="en" {{ app()->getLocale() == 'en' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🇬🇧</span>
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">English</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3.5 border rounded-2xl cursor-pointer transition {{ app()->getLocale() == 'gu' ? 'border-primary-500 bg-primary-50/40 dark:bg-primary-950/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50' }}">
                                    <input type="radio" name="locale" value="gu" {{ app()->getLocale() == 'gu' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🇮🇳</span>
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">ગુજરાતી (Gujarati)</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button type="submit"
                                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-600/30 transition transform active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>{{ __('messages.save_changes') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
