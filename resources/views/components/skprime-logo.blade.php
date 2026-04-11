@props([
    'alt' => 'SKPrimeTech',
    'imgClass' => 'h-full w-auto',
])

@php
    $lightLogo = asset('system/webapp/skprime-combimark.svg');
    $darkLogo = asset('system/webapp/skprime-combimark-white.svg');
@endphp

<div {{ $attributes->class('relative flex items-center justify-center') }}>
    <span class="sr-only">{{ $alt }}</span>

    <img
        src="{{ $lightLogo }}"
        alt=""
        aria-hidden="true"
        class="{{ $imgClass }} opacity-100 transition-opacity duration-300 ease-in-out motion-reduce:transition-none dark:opacity-0"
    />

    <img
        src="{{ $darkLogo }}"
        alt=""
        aria-hidden="true"
        class="absolute inset-0 m-auto {{ $imgClass }} opacity-0 transition-opacity duration-300 ease-in-out motion-reduce:transition-none dark:opacity-100"
    />
</div>
