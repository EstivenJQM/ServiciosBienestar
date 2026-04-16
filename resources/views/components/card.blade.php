@props(['title' => null, 'color' => 'primary', 'padding' => true])

@php
    $customColors = ['sibi' => '#196844'];
    $isCustom     = array_key_exists($color, $customColors);
    $headerClass  = $isCustom
        ? 'text-white'
        : ('bg-' . $color . ' ' . (in_array($color, ['warning', 'light']) ? '' : 'text-white'));
    $headerStyle  = $isCustom ? 'background-color:' . $customColors[$color] : '';
@endphp

<div class="card shadow-sm">
    @if($title)
        <div class="card-header {{ $headerClass }}"
             @if($headerStyle) style="{{ $headerStyle }}" @endif>
            <h5 class="mb-0">{{ $title }}</h5>
        </div>
    @endif
    <div class="{{ $padding ? 'card-body' : 'card-body p-0' }}">
        {{ $slot }}
    </div>
</div>
