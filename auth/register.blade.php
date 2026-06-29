<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear una cuenta · DentiFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
@php
    $roleData = [
        'admin' => ['Administrador', 'Cuenta para configuración y mantenimiento', '#7065f0', '#eeecff'],
        'doctor' => ['Doctor', 'Cuenta para agenda y expedientes clínicos', '#199b7d', '#e9f8f4'],
        'asistente' => ['Asistente', 'Cuenta para recepción y gestión de citas', '#d99512', '#fff5dc'],
    ][$role];
    $heroData = [
        'admin' => ['Administración del sistema', 'DentiFlow, bajo tu control.', 'Configura la aplicación, gestiona permisos y mantén cada módulo funcionando de forma segura.', [['24/7', 'Monitoreo'], ['100%', 'Control'], ['3', 'Perfiles']]],
        'doctor' => ['Espacio clínico', 'Tu práctica, enfocada en cada sonrisa.', 'Consulta tu agenda, expedientes y tratamientos para brindar una atención clara y personalizada.', [['7', 'Días'], ['100%', 'Clínico'], ['1', 'Agenda']]],
        'asistente' => ['Coordinación de recepción', 'Cada cita, en el lugar correcto.', 'Coordina la agenda, recibe pacientes y mantén la operación diaria de la clínica siempre en orden.', [['24/7', 'Organización'], ['100%', 'Coordinación'], ['1', 'Equipo']]],
    ][$role];
@endphp
<body class="min-h-screen bg-[#f7f7fb] font-['DM_Sans'] text-slate-900 antialiased">
<main class="flex min-h-screen">
    <section class="relative hidden w-[46%] overflow-hidden bg-[#17152d] lg:flex">
        <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-[#7065f0]/30 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-28 h-[30rem] w-[30rem] rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="relative z-10 flex w-full flex-col justify-between p-12 xl:p-16">
            <a href="{{ route('role.select') }}" class="flex items-center gap-3 text-white">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#7065f0]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7.3 3.7c1.8-.8 3 .3 4.7.3s2.9-1.1 4.7-.3c2.6 1.2 3 4.8 1.9 7.3-.9 2.1-1.4 3.4-1.6 6.6-.1 1.7-.9 3.4-2.1 3.4-1.7 0-1.2-4.8-2.9-4.8S10.8 21 9.1 21C7.9 21 7.1 19.3 7 17.6c-.2-3.2-.7-4.5-1.6-6.6C4.3 8.5 4.7 4.9 7.3 3.7Z"/></svg></span>
                <span><strong class="block text-lg">DentiFlow</strong><small class="text-white/40">Gestión clínica</small></span>
            </a>
            <div class="max-w-md">
                <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-violet-200">{{ $heroData[0] }}</span>
                <h1 class="mt-6 text-4xl font-bold leading-tight text-white xl:text-5xl">{{ $heroData[1] }}</h1>
                <p class="mt-5 leading-7 text-white/55">{{ $heroData[2] }}</p>
                <div class="mt-10 grid grid-cols-3 gap-3">
                    @foreach($heroData[3] as [$value, $label])
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><strong class="block text-xl text-white">{{ $value }}</strong><span class="text-[10px] text-white/40">{{ $label }}</span></div>
                    @endforeach
                </div>
            </div>
            <p class="text-xs text-white/25">© {{ date('Y') }} DentiFlow</p>
        </div>
    </section>

    <section class="flex flex-1 items-center justify-center px-5 py-8 sm:px-10">
        <div class="w-full max-w-md">
            <a href="{{ route('login', ['role' => $role]) }}" class="mb-7 inline-flex items-center gap-2 text-sm font-semibold text-slate-400 transition hover:text-slate-700">← Volver al inicio de sesión</a>
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl" style="background: {{ $roleData[3] }}; color: {{ $roleData[2] }}">
                    @if($role === 'admin')<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                    @elseif($role === 'doctor')<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M5 22v-3a7 7 0 0 1 14 0v3M9 7h6M12 4v6"/></svg>
                    @else<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2ZM7 2v4m10-4v4M3 9h18"/></svg>@endif
                </span>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nueva cuenta de</p><h2 class="text-xl font-bold">{{ $roleData[0] }}</h2></div>
            </div>
            <h1 class="mt-7 text-3xl font-bold tracking-tight">Crear una cuenta</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $roleData[1] }}.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-4">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Tu nombre" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Consultorio</label>
                    <input type="text" name="consultorio" value="{{ old('consultorio') }}" placeholder="Nombre del consultorio" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="nombre@clinica.com" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-sm font-semibold text-slate-700">Contraseña<input type="password" name="password" placeholder="••••••••" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100"></label>
                    <label class="block text-sm font-semibold text-slate-700">Confirmar<input type="password" name="password_confirmation" placeholder="••••••••" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100"></label>
                </div>
                <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-95" style="background: {{ $roleData[2] }}">Crear cuenta de {{ $roleData[0] }}</button>
            </form>
            <p class="mt-6 text-center text-xs text-slate-400">¿Ya tienes una cuenta? <a href="{{ route('login', ['role' => $role]) }}" class="font-bold text-[#7065f0]">Iniciar sesión</a></p>
        </div>
    </section>
</main>
</body>
</html>
