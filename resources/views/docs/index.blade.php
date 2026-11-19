@extends('layouts.app')

@section('title', 'Documentos PDF — CloudBox')

@section('content')
    @php
        $isCreateError = old('form_context') === 'create';
        $isUpdateError = old('form_context') === 'update';
        $editingDoc = $isUpdateError ? $docs->firstWhere('id', (int) old('editing_id')) : null;
    @endphp

    <div class="flex flex-col gap-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                        Gestor de Documentos PDF
                    </h1>
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200/80">
                        {{ $docs->count() }} {{ $docs->count() === 1 ? 'documento' : 'documentos' }}
                    </span>
                </div>
                <p class="mt-1.5 text-sm text-slate-600">
                    Pulsa sobre cualquier documento para abrirlo en una pestaña nueva del navegador, o gestiona sus versiones de forma segura.
                </p>
            </div>
        </div>

        {{-- Upload PDF Card --}}
        <section class="rounded-3xl bg-white border border-slate-200/90 shadow-xs p-6 sm:p-7">
            <div class="flex items-center gap-3 mb-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Subir nuevo documento PDF</h2>
                    <p class="text-xs text-slate-500">Formato permitido: únicamente archivos PDF (.pdf) · Tamaño máximo: 10 MB</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('docs.store') }}"
                enctype="multipart/form-data"
                class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start"
            >
                @csrf
                <input type="hidden" name="form_context" value="create">

                <div class="lg:col-span-5">
                    <label for="doc-create-title" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Título del documento <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="doc-create-title"
                        type="text"
                        name="title"
                        value="{{ $isCreateError ? old('title') : '' }}"
                        required
                        maxlength="255"
                        placeholder="Ej. Manual de usuario 2026"
                        class="w-full rounded-xl border {{ $isCreateError && $errors->has('title') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-rose-500 focus:ring-rose-500/20' }} bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition-all"
                    >
                    @if ($isCreateError && $errors->has('title'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-5">
                    <label for="doc-create-file" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Archivo PDF <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="doc-create-file"
                        type="file"
                        name="file"
                        accept="application/pdf,.pdf"
                        required
                        class="block w-full rounded-xl border {{ $isCreateError && $errors->has('file') ? 'border-rose-400' : 'border-slate-300' }} bg-slate-50/70 text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 file:cursor-pointer cursor-pointer focus:outline-none"
                    >
                    @if ($isCreateError && $errors->has('file'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('file') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-2 lg:pt-7">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-rose-600/20 hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-500/25 transition-all cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Subir PDF</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Ordered Documents List --}}
        <section>
            @if ($docs->isEmpty())
                <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white/70 px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">Todavía no hay documentos PDF</h3>
                    <p class="mt-1.5 text-sm text-slate-500 max-w-md mx-auto">
                        Sube tu primer archivo PDF utilizando el formulario superior para consultarlo desde el navegador en cualquier momento.
                    </p>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl bg-white border border-slate-200/90 shadow-xs">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h2 class="text-sm font-semibold text-slate-800">Documentos disponibles</h2>
                        <span class="text-xs text-slate-500">Ordenados por fecha más reciente</span>
                    </div>

                    <ol class="divide-y divide-slate-100">
                        @foreach ($docs as $doc)
                            <li class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-6 py-4 hover:bg-slate-50/80 transition-colors">
                                {{-- Clickable Document Row (opens PDF in new tab) --}}
                                <a
                                    href="{{ $doc->url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-4 min-w-0 flex-1 focus:outline-none"
                                >
                                    <span class="hidden sm:inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-500">
                                        {{ $loop->iteration }}
                                    </span>

                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-200/80 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                        <span class="text-[11px] font-bold tracking-wider">PDF</span>
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-900 group-hover:text-rose-600 truncate transition-colors">
                                                {{ $doc->title }}
                                            </span>
                                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 group-hover:text-rose-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Actualizado el {{ $doc->updated_at->format('d/m/Y H:i') }} · Pulsa para abrir en nueva pestaña
                                        </p>
                                    </div>
                                </a>

                                {{-- Differentiated Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-2 shrink-0 pl-0 sm:pl-11 md:pl-0">
                                    <a
                                        href="{{ $doc->url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                        <span>Abrir PDF</span>
                                    </a>

                                    <button
                                        type="button"
                                        data-open-edit-doc
                                        data-action="{{ route('docs.update', $doc) }}"
                                        data-id="{{ $doc->id }}"
                                        data-title="{{ $doc->title }}"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 ring-1 ring-amber-200/80 hover:bg-amber-100 transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                        <span>Editar / Reemplazar</span>
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route('docs.destroy', $doc) }}"
                                        data-confirm-delete
                                        data-item-title="{{ $doc->title }}"
                                        data-item-type="documento PDF"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 ring-1 ring-rose-200/80 hover:bg-rose-600 hover:text-white transition-colors cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </section>
    </div>

    {{-- Edit / Replace Doc Modal --}}
    <div
        id="doc-edit-modal"
        class="fixed inset-0 z-50 {{ $editingDoc ? 'flex' : 'hidden' }} items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="doc-edit-modal-title"
    >
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 id="doc-edit-modal-title" class="text-base font-semibold text-slate-900">
                        Editar o reemplazar documento PDF
                    </h3>
                    <p class="text-xs text-slate-500">Actualiza el título o sube un nuevo PDF para sustituir el archivo actual.</p>
                </div>
                <button
                    type="button"
                    data-close-modal="doc-edit-modal"
                    aria-label="Cerrar ventana"
                    class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form
                id="doc-edit-form"
                method="POST"
                action="{{ $editingDoc ? route('docs.update', $editingDoc) : '#' }}"
                enctype="multipart/form-data"
                class="p-6 flex flex-col gap-5"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="update">
                <input type="hidden" name="editing_id" id="doc-edit-id" value="{{ $editingDoc ? $editingDoc->id : '' }}">

                <div>
                    <label for="doc-edit-title" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Título del documento <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="doc-edit-title"
                        type="text"
                        name="title"
                        value="{{ $editingDoc ? old('title', $editingDoc->title) : '' }}"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border {{ $isUpdateError && $errors->has('title') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-rose-500 focus:ring-rose-500/20' }} bg-white px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 transition-all"
                    >
                    @if ($isUpdateError && $errors->has('title'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div>
                    <label for="doc-edit-file" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Reemplazar archivo PDF <span class="text-xs font-normal text-slate-400">(opcional)</span>
                    </label>
                    <input
                        id="doc-edit-file"
                        type="file"
                        name="file"
                        accept="application/pdf,.pdf"
                        class="block w-full rounded-xl border {{ $isUpdateError && $errors->has('file') ? 'border-rose-400' : 'border-slate-300' }} bg-slate-50/70 text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 file:cursor-pointer cursor-pointer focus:outline-none"
                    >
                    <p class="mt-1.5 text-xs text-slate-500">
                        Deja este campo vacío si solo deseas cambiar el título. Si subes otro PDF, el anterior se borrará automáticamente. Máximo 10 MB.
                    </p>
                    @if ($isUpdateError && $errors->has('file'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('file') }}</p>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        data-close-modal="doc-edit-modal"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-600/20 transition-colors cursor-pointer"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
