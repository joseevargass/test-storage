@extends('layouts.app')

@section('title', 'CloudBox — Gestor de Archivos en la Nube')

@section('content')
    <section class="py-4 sm:py-8">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200/80">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                Almacenamiento público organizado
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                Gestiona tus imágenes y documentos PDF en un solo lugar
            </h1>
            <p class="mt-3 text-base text-slate-600 leading-relaxed">
                Sube, visualiza, reemplaza y elimina tus archivos con limpieza automática del almacenamiento para mantener tu espacio siempre ordenado y sin archivos huérfanos.
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Photos Section Card --}}
            <a
                href="{{ route('photos.index') }}"
                class="group relative flex flex-col justify-between rounded-3xl bg-white p-7 border border-slate-200/90 shadow-xs hover:shadow-xl hover:shadow-indigo-900/5 hover:border-indigo-300 transition-all duration-200"
            >
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $photosCount }} {{ $photosCount === 1 ? 'imagen' : 'imágenes' }}
                        </span>
                    </div>

                    <h2 class="mt-6 text-xl font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">
                        Galería de Imágenes
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Sube fotografías con título personalizado, previsualízalas directamente en la galería o a pantalla completa, reemplázalas cuando quieras o elimínalas de forma segura.
                    </p>
                </div>

                <div class="mt-7 pt-5 border-t border-slate-100 flex items-center justify-between text-sm font-semibold text-indigo-600">
                    <span>Ir a /photos · Máx. 5 MB</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>

            {{-- Docs Section Card --}}
            <a
                href="{{ route('docs.index') }}"
                class="group relative flex flex-col justify-between rounded-3xl bg-white p-7 border border-slate-200/90 shadow-xs hover:shadow-xl hover:shadow-rose-900/5 hover:border-rose-300 transition-all duration-200"
            >
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-rose-100 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $docsCount }} {{ $docsCount === 1 ? 'documento' : 'documentos' }}
                        </span>
                    </div>

                    <h2 class="mt-6 text-xl font-semibold text-slate-900 group-hover:text-rose-600 transition-colors">
                        Gestor de Documentos PDF
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Organiza todos tus archivos PDF en una lista limpia, ábrelos al instante en una nueva pestaña del navegador y actualiza títulos o versiones sin dejar archivos residuales.
                    </p>
                </div>

                <div class="mt-7 pt-5 border-t border-slate-100 flex items-center justify-between text-sm font-semibold text-rose-600">
                    <span>Ir a /docs · Máx. 10 MB</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>
        </div>
    </section>
@endsection
