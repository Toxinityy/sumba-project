{{-- resources/views/components/theme-switcher.blade.php --}}
<div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 rounded border border-line bg-raised p-3 shadow-lg"
     role="group"
     aria-label="{{ __('theme.label') }}">
  <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('theme.label') }}</p>
  <div class="flex gap-1.5">
    @foreach (['light', 'dark', 'system'] as $choice)
      <button type="button"
              data-theme-btn="{{ $choice }}"
              aria-pressed="false"
              class="flex min-h-11 min-w-11 items-center justify-center rounded-sm border-[1.5px] border-line px-3 text-[12.5px] font-bold text-ink">
        {{ __('theme.' . $choice) }}
      </button>
    @endforeach
  </div>
</div>
