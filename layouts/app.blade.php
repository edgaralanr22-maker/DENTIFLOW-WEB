<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DentiFlow') · DentiFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#f5f6fa] font-['DM_Sans'] text-slate-900 antialiased">
@php
    $currentRole = session('access_role', 'asistente');
    $roleLabels = ['admin' => 'Administrador técnico', 'doctor' => 'Doctor', 'asistente' => 'Asistente'];
    $allNavigation = [
        ['route' => 'inicio', 'match' => 'inicio', 'label' => $currentRole === 'admin' ? 'Estado del sistema' : 'Resumen', 'icon' => 'grid', 'roles' => ['admin', 'doctor', 'asistente']],
        ['route' => 'calendario', 'match' => 'calendario', 'label' => 'Calendario', 'icon' => 'calendar'],
        ['route' => 'citas', 'match' => 'citas*', 'label' => 'Citas', 'icon' => 'clock'],
        ['route' => 'pacientes', 'match' => 'pacientes', 'label' => 'Pacientes', 'icon' => 'users'],
        ['route' => 'pacientes.tratamientos', 'match' => 'pacientes.tratamientos*', 'label' => 'Tratamientos', 'icon' => 'sparkles'],
        ['route' => 'inventario', 'match' => 'inventario*', 'label' => 'Inventario', 'icon' => 'box'],
        ['route' => 'reportes', 'match' => 'reportes*', 'label' => 'Reportes', 'icon' => 'chart', 'roles' => ['doctor']],
        ['route' => 'admin.settings', 'match' => 'admin.*', 'label' => 'Configuración', 'icon' => 'settings', 'roles' => ['admin']],
    ];
    $roleNavigation = [
        'admin' => ['inicio', 'admin.settings'],
        'doctor' => ['inicio', 'calendario', 'citas', 'pacientes', 'pacientes.tratamientos', 'reportes'],
        'asistente' => ['inicio', 'calendario', 'citas', 'pacientes', 'inventario'],
    ];
    $navigation = collect($allNavigation)->filter(fn ($item) => in_array($item['route'], $roleNavigation[$currentRole] ?? []));
@endphp
<div class="min-h-screen lg:flex">
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-[268px] -translate-x-full flex-col bg-[#17152d] text-white shadow-2xl transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#7065f0] shadow-lg shadow-indigo-950/30">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7.3 3.7c1.8-.8 3 .3 4.7.3s2.9-1.1 4.7-.3c2.6 1.2 3 4.8 1.9 7.3-.9 2.1-1.4 3.4-1.6 6.6-.1 1.7-.9 3.4-2.1 3.4-1.7 0-1.2-4.8-2.9-4.8S10.8 21 9.1 21C7.9 21 7.1 19.3 7 17.6c-.2-3.2-.7-4.5-1.6-6.6C4.3 8.5 4.7 4.9 7.3 3.7Z"/></svg>
            </div>
            <div><p class="text-lg font-bold tracking-tight">DentiFlow</p><p class="text-[10px] font-semibold uppercase tracking-[.2em] text-white/40">Gestión clínica</p></div>
            <button id="sidebar-close" class="ml-auto rounded-lg p-2 text-white/50 hover:bg-white/10 lg:hidden" aria-label="Cerrar menú">✕</button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[.18em] text-white/35">Administración</p>
            <nav class="space-y-1">
                @foreach($navigation as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-[#7065f0] text-white shadow-lg shadow-indigo-950/20' : 'text-[#a9a7bb] hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-[19px] w-[19px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            @switch($item['icon'])
                                @case('grid') <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/> @break
                                @case('calendar') <path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2ZM7 2v4m10-4v4M3 9h18"/> @break
                                @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
                                @case('users') <path d="M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6m2 17v-2a4 4 0 0 0-3-3.8"/> @break
                                @case('sparkles') <path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2L12 3Zm6 10 .8 2.2L21 16l-2.2.8L18 19l-.8-2.2L15 16l2.2-.8L18 13ZM5 14l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3Z"/> @break
                                @case('badge') <circle cx="12" cy="8" r="4"/><path d="M5 22v-3a7 7 0 0 1 14 0v3M9 8h6M12 5v6"/> @break
                                @case('box') <path d="m4 7 8-4 8 4-8 4-8-4Zm0 0v10l8 4 8-4V7M12 11v10"/> @break
                                @case('chart') <path d="M4 20V10m6 10V4m6 16v-7m5 7H2"/> @break
                                @case('settings') <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/> @break
                            @endswitch
                        </svg>
                        {{ $item['label'] }}
                        @if($active)<span class="ml-auto h-1.5 w-1.5 rounded-full bg-white"></span>@endif
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="border-t border-white/10 p-4">
            <a href="{{ route('perfil') }}" class="mb-2 flex items-center gap-3 rounded-xl p-2.5 hover:bg-white/10">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-indigo-600 text-xs font-bold">AD</div>
                <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $roleLabels[$currentRole] ?? 'Usuario' }}</p><p class="truncate text-[11px] text-white/40">Clínica DentiFlow</p></div><span class="text-white/30">›</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-xs font-medium text-white/45 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5m5 5H3m11-9h6v18h-6"/></svg>Cerrar sesión
                </button>
            </form>
        </div>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 flex h-20 items-center border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-7">
            <button id="sidebar-open" class="mr-3 rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden" aria-label="Abrir menú"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <div id="global-search" class="relative hidden max-w-md flex-1 sm:block">
                <div class="flex items-center gap-3 rounded-xl bg-slate-100 px-4 py-2.5 text-sm transition focus-within:bg-white focus-within:ring-2 focus-within:ring-[#7065f0]/20">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                    <input id="global-search-input" type="search" autocomplete="off" placeholder="Buscar una sección…" class="min-w-0 flex-1 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400">
                    <kbd class="rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[10px] text-slate-400">⌘ K</kbd>
                </div>
                <div id="global-search-results" class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                    <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Ir a una sección</p>
                    @foreach($navigation as $item)
                        <a href="{{ route($item['route']) }}" data-search-item data-search-text="{{ \Illuminate\Support\Str::lower($item['label'].' '.$item['route']) }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-indigo-50 hover:text-[#7065f0]"><span>{{ $item['label'] }}</span><span class="text-slate-300">↗</span></a>
                    @endforeach
                    <a href="{{ route('perfil') }}" data-search-item data-search-text="perfil cuenta usuario configuración personal" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-indigo-50 hover:text-[#7065f0]"><span>Mi perfil</span><span class="text-slate-300">↗</span></a>
                    <p id="global-search-empty" class="hidden px-3 py-5 text-center text-xs text-slate-400">No se encontró esa sección.</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="hidden text-right sm:block"><span class="block text-xs font-semibold text-slate-700">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</span><span class="block text-[10px] text-slate-400">Panel administrativo</span></span>
                <button class="relative ml-2 rounded-xl border border-slate-200 bg-white p-2.5 text-slate-500 hover:bg-slate-50" aria-label="Notificaciones"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></svg><span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span></button>
                <a href="{{ route('perfil') }}" class="ml-1 flex h-10 w-10 items-center justify-center rounded-xl bg-[#eceafe] text-xs font-bold text-[#5c52d6]">AD</a>
            </div>
        </header>
        <main class="p-4 sm:p-7 xl:p-9"><div class="mx-auto max-w-[1500px]">@yield('content')</div></main>
    </div>
</div>
</body>
</html>
