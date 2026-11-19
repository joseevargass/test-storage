<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'CloudBox — Gestor de Archivos')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased flex flex-col selection:bg-indigo-100 selection:text-indigo-900">
        {{-- Top Navigation Bar --}}
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4 py-3.5">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group focus:outline-none">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-sky-500 text-white shadow-sm shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 0 0 4 4h9a5 5 0 1 0-.1-9.999 5.002 5.002 0 1 0-9.78 2.096A4.001 4.001 0 0 0 3 15Z" />
                            </svg>
                        </span>
                        <span class="flex flex-col">
                            <span class="text-base font-semibold tracking-tight text-slate-900 leading-tight">CloudBox</span>
                            <span class="text-xs text-slate-500 leading-tight">Gestor de archivos</span>
                        </span>
                    </a>

                    <nav aria-label="Navegación principal" class="flex items-center gap-1.5 sm:gap-2">
                        <a
                            href="{{ route('photos.index') }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('photos.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span>Galería de Imágenes</span>
                        </a>

                        <a
                            href="{{ route('docs.index') }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('docs.*') ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Documentos PDF</span>
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <x-flash-messages />
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-slate-200/80 bg-white mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <p>CloudBox · Gestor de imágenes y documentos PDF con Laravel Storage</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('photos.index') }}" class="hover:text-indigo-600 transition-colors">Imágenes</a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('docs.index') }}" class="hover:text-rose-600 transition-colors">Documentos</a>
                </div>
            </div>
        </footer>

        {{-- Shared Delete Confirmation Modal --}}
        <div
            id="delete-confirm-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-rose-50 text-rose-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="delete-modal-title" class="text-base font-semibold text-slate-900">
                            Confirmar eliminación
                        </h3>
                        <p id="delete-modal-message" class="mt-1.5 text-sm text-slate-600 leading-relaxed">
                            ¿Estás seguro de que deseas eliminar este archivo? Esta acción también lo borrará permanentemente del almacenamiento.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        data-close-modal="delete-confirm-modal"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        id="delete-modal-confirm-btn"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-600/20 transition-colors cursor-pointer"
                    >
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </body>
</html>
