@php
$icons = [
    'dashboard' => '◆',
    'maquinas' => '⚙',
    'tecnicos' => '👤',
    'ordens' => '📋',
    'historico' => '⏱',
    'usuarios' => '👥',
    'acesso' => '🔐',
];

$icon = $icons[$name] ?? '•';
@endphp

<span class="icon" style="display: inline-flex; align-items: center; justify-content: center; font-size: 18px; {{ $style ?? '' }}">{{ $icon }}</span>
