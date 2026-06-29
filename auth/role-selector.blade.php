<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecciona tu acceso · DentiFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#f7f7fb] font-['DM_Sans'] text-slate-900 antialiased">
<main class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-12">
    <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-[#7065f0]/10 blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-[#42bca0]/10 blur-3xl"></div>

    <div class="relative w-full max-w-5xl">
        <header class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#7065f0] text-white shadow-xl shadow-indigo-200">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7.3 3.7c1.8-.8 3 .3 4.7.3s2.9-1.1 4.7-.3c2.6 1.2 3 4.8 1.9 7.3-.9 2.1-1.4 3.4-1.6 6.6-.1 1.7-.9 3.4-2.1 3.4-1.7 0-1.2-4.8-2.9-4.8S10.8 21 9.1 21C7.9 21 7.1 19.3 7 17.6c-.2-3.2-.7-4.5-1.6-6.6C4.3 8.5 4.7 4.9 7.3 3.7Z"/></svg>
            </div>
            <p class="mt-4 text-sm font-bold tracking-tight text-[#7065f0]">DentiFlow</p>
            <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-4xl">¿Cómo deseas ingresar?</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-500">Selecciona tu perfil para continuar al acceso correspondiente de la clínica.</p>
        </header>

        <section class="mt-10 grid gap-5 md:grid-cols-3">
            <a href="{{ route('login', ['role' => 'admin']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-[0_10px_40px_rgba(40,40,80,.06)] transition duration-300 hover:-translate-y-1 hover:border-[#7065f0]/40 hover:shadow-xl hover:shadow-indigo-100">
                <span class="absolute right-5 top-5 text-xl text-slate-200 transition group-hover:translate-x-1 group-hover:text-[#7065f0]">→</span>
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eeecff] text-[#7065f0]">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                </span>
                <h2 class="mt-6 text-xl font-bold">Administrador</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Supervisión técnica, configuración, módulos y mantenimiento de la aplicación.</p>
                <span class="mt-7 inline-flex rounded-full bg-[#eeecff] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#7065f0]">Gestión técnica</span>
            </a>

            <a href="{{ route('login', ['role' => 'doctor']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-[0_10px_40px_rgba(40,40,80,.06)] transition duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-100">
                <span class="absolute right-5 top-5 text-xl text-slate-200 transition group-hover:translate-x-1 group-hover:text-emerald-500">→</span>
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M5 22v-3a7 7 0 0 1 14 0v3M9 7h6M12 4v6"/></svg>
                </span>
                <h2 class="mt-6 text-xl font-bold">Doctor</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Consulta tu agenda, expedientes clínicos y tratamientos de tus pacientes.</p>
                <span class="mt-7 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Área clínica</span>
            </a>

            <a href="{{ route('login', ['role' => 'asistente']) }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-[0_10px_40px_rgba(40,40,80,.06)] transition duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-100">
                <span class="absolute right-5 top-5 text-xl text-slate-200 transition group-hover:translate-x-1 group-hover:text-amber-500">→</span>
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2ZM7 2v4m10-4v4M3 9h18M8 14h3m2 0h3m-8 3h3"/></svg>
                </span>
                <h2 class="mt-6 text-xl font-bold">Asistente</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Gestiona citas, recepción de pacientes, calendario e inventario diario.</p>
                <span class="mt-7 inline-flex rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-600">Recepción</span>
            </a>
        </section>

        <footer class="mt-9 text-center text-xs text-slate-400">© {{ date('Y') }} DentiFlow · Acceso seguro para el personal</footer>
    </div>
</main>
</body>
</html>
