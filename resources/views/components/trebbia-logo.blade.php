@props([
    'variant' => 'full',
])

@php
    $logoId = 'trebbia-logo-'.uniqid();
@endphp

@if ($variant === 'mark')
    <svg {{ $attributes->merge(['class' => 'h-11 w-11']) }} viewBox="0 0 72 72" fill="none" role="img" aria-label="trebbia">
        <defs>
            <linearGradient id="{{ $logoId }}-surface" x1="10" y1="8" x2="64" y2="66" gradientUnits="userSpaceOnUse">
                <stop stop-color="#163D45" />
                <stop offset="0.52" stop-color="#245B63" />
                <stop offset="1" stop-color="#2D6F73" />
            </linearGradient>
            <linearGradient id="{{ $logoId }}-orbit" x1="18" y1="20" x2="56" y2="54" gradientUnits="userSpaceOnUse">
                <stop stop-color="#73D7CD" />
                <stop offset="1" stop-color="#7CB7E8" />
            </linearGradient>
            <filter id="{{ $logoId }}-soft" x="0" y="0" width="72" height="72" color-interpolation-filters="sRGB">
                <feDropShadow dx="0" dy="10" stdDeviation="8" flood-color="#163D45" flood-opacity="0.14" />
            </filter>
        </defs>
        <rect x="8" y="8" width="56" height="56" rx="17" fill="url(#{{ $logoId }}-surface)" filter="url(#{{ $logoId }}-soft)" />
        <path d="M25 37.5C25 27.84 32.84 20 42.5 20C51.06 20 58 26.94 58 35.5C58 44.06 51.06 51 42.5 51H30.5" stroke="url(#{{ $logoId }}-orbit)" stroke-width="4.5" stroke-linecap="round" />
        <path d="M32 22V47C32 50.31 34.69 53 38 53H44" stroke="#F8FAFC" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M24 31H46" stroke="#F8FAFC" stroke-width="5" stroke-linecap="round" />
        <circle cx="53" cy="26" r="5.5" fill="#73D7CD" stroke="#E5F7F5" stroke-width="2" />
    </svg>
@else
    <svg {{ $attributes->merge(['class' => 'h-auto w-64 max-w-full']) }} viewBox="0 0 680 144" fill="none" role="img" aria-label="trebbia. Centro Inteligente de Reservas y Agendamiento">
        <defs>
            <linearGradient id="{{ $logoId }}-surface" x1="14" y1="14" x2="104" y2="108" gradientUnits="userSpaceOnUse">
                <stop stop-color="#163D45" />
                <stop offset="0.52" stop-color="#245B63" />
                <stop offset="1" stop-color="#2D6F73" />
            </linearGradient>
            <linearGradient id="{{ $logoId }}-orbit" x1="30" y1="33" x2="91" y2="88" gradientUnits="userSpaceOnUse">
                <stop stop-color="#73D7CD" />
                <stop offset="1" stop-color="#7CB7E8" />
            </linearGradient>
            <linearGradient id="{{ $logoId }}-dot" x1="279" y1="53" x2="303" y2="77" gradientUnits="userSpaceOnUse">
                <stop stop-color="#73D7CD" />
                <stop offset="1" stop-color="#7CB7E8" />
            </linearGradient>
            <filter id="{{ $logoId }}-soft" x="0" y="0" width="124" height="124" color-interpolation-filters="sRGB">
                <feDropShadow dx="0" dy="12" stdDeviation="10" flood-color="#163D45" flood-opacity="0.14" />
            </filter>
        </defs>
        <rect x="10" y="10" width="104" height="104" rx="30" fill="url(#{{ $logoId }}-surface)" filter="url(#{{ $logoId }}-soft)" />
        <path d="M41 66C41 48.88 54.88 35 72 35C87.46 35 100 47.54 100 63C100 78.46 87.46 91 72 91H52" stroke="url(#{{ $logoId }}-orbit)" stroke-width="8" stroke-linecap="round" />
        <path d="M55 39V82C55 88.63 60.37 94 67 94H78" stroke="#F8FAFC" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M39 54H78" stroke="#F8FAFC" stroke-width="9" stroke-linecap="round" />
        <circle cx="92" cy="45" r="9.5" fill="#73D7CD" stroke="#E5F7F5" stroke-width="3" />
        <text x="138" y="69" fill="#1F2933" font-family="'Avenir Next', 'Nunito Sans', 'Inter', 'Segoe UI', Arial, sans-serif" font-size="48" font-weight="700" letter-spacing="0">trebbia</text>
        <circle cx="316" cy="59" r="5.5" fill="url(#{{ $logoId }}-dot)" />
        <text x="140" y="101" fill="#66747E" font-family="'Avenir Next', 'Nunito Sans', 'Inter', 'Segoe UI', Arial, sans-serif" font-size="12" font-weight="600" letter-spacing="2.6">CENTRO INTELIGENTE DE RESERVAS Y AGENDAMIENTO</text>
    </svg>
@endif
