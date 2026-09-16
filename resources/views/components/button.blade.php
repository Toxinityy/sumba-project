{{-- resources/views/components/button.blade.php --}}
@props(['href' => null, 'variant' => 'primary'])

@php($classes = implode(' ', array_filter([
    'inline-flex min-h-11 items-center justify-center gap-2 rounded-pill border-[1.5px] border-transparent',
    'px-7 py-4 text-[15px] font-bold leading-none text-center transition-colors',
    // No nowrap, no truncate, no fixed width: Indonesian labels run 15-20%
    // longer than their English equivalents and must be allowed to wrap.
    $variant === 'primary' ? 'bg-accent text-accent-ink hover:bg-accent-strong' : null,
    $variant === 'secondary' ? 'border-ink bg-transparent text-ink hover:bg-ink hover:text-surface' : null,
])))

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
  <button type="button" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
