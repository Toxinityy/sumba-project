{{-- resources/views/components/picture.blade.php --}}
<picture>
  @foreach ($orderedSources() as $source)
    <source type="{{ $source['mime'] }}"
            srcset="{{ $source['srcset'] }}"
            sizes="{{ $sizes }}">
  @endforeach

  {{-- Explicit width/height reserve the box before the bytes arrive, so
       nothing below the image jumps when it loads. --}}
  <img src="{{ $fallbackSrc() }}"
       srcset="{{ $jpegSrcset() }}"
       sizes="{{ $sizes }}"
       width="{{ $width }}"
       height="{{ $height }}"
       alt="{{ $alt }}"
       loading="{{ $eager ? 'eager' : 'lazy' }}"
       decoding="{{ $eager ? 'sync' : 'async' }}"
       @if ($eager) fetchpriority="high" @endif
       {{ $attributes->merge(['class' => 'block max-w-full h-auto']) }}>
</picture>
