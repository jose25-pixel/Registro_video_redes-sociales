<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen bg-slate-100 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl lg:grid-cols-[1.05fr_0.95fr]">
                <div class="relative hidden overflow-hidden bg-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-red-600/80 blur-3xl"></div>
                    <div class="absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-blue-600/70 blur-3xl"></div>
                    <div class="relative">
                        <a href="/" class="flex items-center gap-3 text-lg font-semibold"><span class="grid h-10 w-10 place-items-center rounded-xl bg-red-500 text-xl">▶</span> Registro Video</a>
                        <div class="mt-24 max-w-sm"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-300">Biblioteca tecnica</p><h1 class="mt-4 text-4xl font-bold leading-tight">Tus videos, ordenados y listos para volver a encontrarlos.</h1><p class="mt-5 text-slate-300">Registra publicaciones, formatos, listas y referencias de tus equipos en un solo lugar.</p></div>
                    </div>
                    <div class="relative grid grid-cols-3 gap-3 text-center text-xs text-slate-300"><div class="rounded-lg border border-white/10 bg-white/5 p-3"><span class="mb-2 block h-2 rounded-full bg-red-500"></span>YouTube</div><div class="rounded-lg border border-white/10 bg-white/5 p-3"><span class="mb-2 block h-2 rounded-full bg-blue-500"></span>Facebook</div><div class="rounded-lg border border-white/10 bg-white/5 p-3"><span class="mb-2 block h-2 rounded-full bg-purple-500"></span>TikTok</div></div>
                </div>
                <div class="flex items-center justify-center p-6 sm:p-10">
                    <div class="w-full max-w-md">
                        <div class="mb-8 lg:hidden"><a href="/" class="flex items-center gap-3 text-lg font-bold"><span class="grid h-10 w-10 place-items-center rounded-xl bg-red-600 text-white">▶</span> Registro Video</a></div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
