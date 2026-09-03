<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro Video</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-white">
    <div class="relative isolate min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_85%_15%,rgba(14,165,233,0.28),transparent_30%),radial-gradient(circle_at_10%_90%,rgba(220,38,38,0.24),transparent_32%)]"></div>
        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-6 lg:px-8">
            <header class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 font-semibold text-white"><span class="grid h-10 w-10 place-items-center rounded-md bg-red-600 text-lg shadow-lg shadow-red-950/40">RV</span><span>Registro Video</span></a>
                <nav class="flex items-center gap-3 text-sm font-medium">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-white px-4 py-2 text-slate-950 hover:bg-slate-100">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-slate-200 hover:text-white">Iniciar sesion</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md border border-slate-500 px-4 py-2 text-white hover:border-white hover:bg-white/10">Crear cuenta</a>
                        @endif
                    @endauth
                </nav>
            </header>

            <main class="grid flex-1 items-center gap-12 py-16 lg:grid-cols-[1.05fr_0.95fr] lg:py-24">
                <section>
                    <p class="text-sm font-semibold uppercase tracking-wider text-sky-300">Biblioteca tecnica de contenido</p>
                    <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-tight sm:text-5xl">Registra, encuentra y reutiliza tus videos.</h1>
                    <p class="mt-5 max-w-xl text-lg leading-8 text-slate-300">Guarda los datos de cada video, conserva un historial técnico y encuentra referencias para crear nuevo contenido sin perder información.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('videos.create') }}" class="rounded-md bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-red-950/40 hover:bg-red-500">Nuevo video</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-md bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-red-950/40 hover:bg-red-500">Comenzar a registrar</a>
                        @endauth
                    </div>
                </section>

                <section class="border border-white/15 bg-slate-900/80 p-6 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
                    <div class="border-b border-slate-700 pb-5"><p class="text-sm text-slate-400">Funciones del sistema</p><p class="mt-1 font-semibold">Tu historial técnico organizado</p></div>
                    <div class="space-y-5 py-6">
                        <div class="border-l-2 border-sky-400 pl-4"><p class="text-sm text-slate-400">Registro de videos</p><p class="mt-1 font-medium">Título, enlace, modelo, marca, diagnóstico y publicaciones.</p></div>
                        <div class="border-l-2 border-red-400 pl-4"><p class="text-sm text-slate-400">Historial consultable</p><p class="mt-1 font-medium">Busca tus videos por título, modelo, marca o tipo de aparato.</p></div>
                        <div class="border-l-2 border-emerald-400 pl-4"><p class="text-sm text-slate-400">Recomendaciones</p><p class="mt-1 font-medium">Consulta enlaces relacionados por tema, modelo, marca y aparato.</p></div>
                    </div>
                    <div class="border-t border-slate-700 pt-5 text-sm text-slate-300">Incluye listas de reproducción y tarjetas de línea de tiempo para cada video.</div>
                </section>
            </main>

            <footer class="text-sm text-slate-500">&copy; 2026 Registro Video. Desarrollado por Cruz Zabaleta, desarrollador de Entornos Web.</footer>
        </div>
    </div>
</body>
</html>