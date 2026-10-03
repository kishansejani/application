@php $fgu = app()->getLocale() === 'gu'; @endphp
<div class="space-y-6">
    <div>
        <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('messages.categories') }}</h5>
        <div class="space-y-0.5 text-[13px]">
            <a href="{{ route('products.index', request()->only(['q', 'sort'])) }}" class="flex items-center justify-between py-2 px-2.5 rounded-xl font-semibold {{ !request('category') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span>{{ __('messages.all_categories') }}</span>
                @if(!request('category'))<i class="ph-bold ph-check"></i>@endif
            </a>
            @foreach($categories as $cat)
                @php $active = request('category') == $cat->slug; @endphp
                <a href="{{ route('products.index', array_merge(request()->only(['q', 'sort']), ['category' => $cat->slug])) }}" class="flex items-center gap-2.5 py-2 px-2.5 rounded-xl font-semibold {{ $active ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i class="{{ $cat->icon ?: 'fa-solid fa-layer-group' }} w-4 text-center text-[12px] {{ $active ? '' : 'text-slate-400' }}"></i>
                    <span class="flex-1 truncate">{{ $cat->localized_name }}</span>
                    <span class="text-[11px] font-bold px-1.5 rounded-md {{ $active ? 'bg-brand-100 dark:bg-brand-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">{{ $cat->products_count ?? $cat->products->count() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <form action="{{ route('products.index') }}" method="GET" class="pt-5 border-t border-slate-100 dark:border-slate-800 space-y-3">
        @foreach(['category', 'subcategory', 'q', 'sort'] as $keep)
            @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
        @endforeach
        <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $fgu ? 'કિંમત શ્રેણી (₹)' : 'Price range (₹)' }}</h5>
        <div class="grid grid-cols-2 gap-2">
            <label><span class="sr-only">{{ __('messages.min') }}</span><input type="number" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="{{ $fgu ? 'ન્યૂનતમ' : 'Min' }}" class="fx-input !py-2"></label>
            <label><span class="sr-only">{{ __('messages.max') }}</span><input type="number" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="{{ $fgu ? 'મહત્તમ' : 'Max' }}" class="fx-input !py-2"></label>
        </div>
        <button type="submit" class="fx-btn fx-btn-dark w-full">{{ $fgu ? 'કિંમત ફિલ્ટર લાગુ કરો' : 'Apply price filter' }}</button>
    </form>

    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 text-emerald-900 dark:text-emerald-200 text-[12px] space-y-1">
        <p class="flex items-center gap-1.5 font-extrabold"><i class="ph-fill ph-lightning text-amber-500"></i>{{ trim(str_replace('⚡', '', __('messages.delivery_promise_title'))) }}</p>
        <p class="opacity-80">{{ $fgu ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'] }}</p>
    </div>
</div>
