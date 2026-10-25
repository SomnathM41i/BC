@props(['name', 'class' => 'h-5 w-5'])

@php
    $icons = [
        'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M8 7h9v9"/>',
        'arrow-left' => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18z"/>',
        'phone' => '<path d="M6.5 3h3l1.5 4-2 1.5a12 12 0 0 0 6.5 6.5l1.5-2 4 1.5v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4.5 5.2 2 2 0 0 1 6.5 3z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'pin' => '<path d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'whatsapp' => '<path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.5L3.5 20.5l1.4-4.3A8.5 8.5 0 1 1 20.5 11.6z"/><path d="M9 9.2c0 3 2.2 5.2 5.2 5.2.6 0 1-.4 1-.9v-.7l-1.6-.7-.8.9a5 5 0 0 1-1.5-1.5l.9-.8-.7-1.6h-.8c-.5 0-.9.4-.9 1z"/>',
        'home' => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"/>',
        'building' => '<path d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16"/><path d="M15 9h4a1 1 0 0 1 1 1v11"/><path d="M7 8h2M7 12h2M7 16h2M11 8h1M11 12h1M11 16h1M3 21h18"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
        'blueprint' => '<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 9h18M9 9v11M14 13h4M14 16h4"/>',
        'cube' => '<path d="M12 3 4 7.5v9L12 21l8-4.5v-9z"/><path d="M4 7.5 12 12l8-4.5M12 12v9"/>',
        'structure' => '<path d="M3 21h18M5 21V8M19 21V8M5 8h14M9 21V12M15 21V12M3 5h18"/>',
        'sofa' => '<rect x="4" y="11" width="16" height="6" rx="2"/><path d="M4 14V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6"/><path d="M7 17v2M17 17v2"/>',
        'tree' => '<path d="M12 3 6 11h3l-3 5h12l-3-5h3z"/><path d="M12 16v5"/>',
        'bolt' => '<path d="m13 2-8 12h6l-1 8 8-12h-6z"/>',
        'droplet' => '<path d="M12 3s6 6.2 6 10.5A6 6 0 0 1 6 13.5C6 9.2 12 3 12 3z"/>',
        'clipboard' => '<path d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1z"/><path d="M8 6H6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-2"/><path d="m9 13 2 2 4-4"/>',
        'handshake' => '<path d="m11 17-2 2a1.5 1.5 0 0 1-2-2l3-3"/><path d="m13 17 2 2a1.5 1.5 0 0 0 2-2l-3-3"/><path d="M4 12V7l4-2 4 2 4-2 4 2v5"/><path d="m8 12 3 3a1.4 1.4 0 0 0 2 0l3-3"/>',
        'calculator' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8v3H8z"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01M16 17.5h.01"/>',
        'play' => '<circle cx="12" cy="12" r="9"/><path d="m10 8.5 5.5 3.5L10 15.5z"/>',
        'star' => '<path d="m12 3 2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6L3.3 9.4l6.1-.8z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'spark' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
        'quote' => '<path d="M9 7c-2.5 0-4 2-4 4.5S6.5 16 9 16v-3.5c-1 0-1.8-.6-1.8-1.5S8 9.5 9 9.5zM19 7c-2.5 0-4 2-4 4.5S16.5 16 19 16v-3.5c-1 0-1.8-.6-1.8-1.5S18 9.5 19 9.5z"/>',
        'shield' => '<path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
    ];

    $path = $icons[$name] ?? $icons['spark'];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false">{!! $path !!}</svg>
