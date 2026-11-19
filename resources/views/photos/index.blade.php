@extends('layouts.app')

@section('title', 'Galería de Imágenes — CloudBox')

@section('content')
    @php
        $isCreateError = old('form_context') === 'create';
        $isUpdateError = old('form_context') === 'update';
        $editingPhoto = $isUpdateError ? $photos->firstWhere('id', (int) old('editing_id')) : null;
    @endphp

    <div class="flex flex-col gap-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                        Galería de Imágenes
                    </h1>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200/80">
                        {{ $photos->count() }} {{ $photos->count() === 1 ? 'imagen' : 'imágenes' }}
                    </span>
                </div>
                <p class="mt-1.5 text-sm text-slate-600">
                    Sube, visualiza dentro de la aplicación, reemplaza o elimina tus imágenes con limpieza automática del disco.
                </p>
            </div>
        </div>

        {{-- Upload Image Card --}}
        <section class="rounded-3xl bg-white border border-slate-200/90 shadow-xs p-6 sm:p-7">
            <div class="flex items-center gap-3 mb-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Subir nueva imagen</h2>
                    <p class="text-xs text-slate-500">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP, BMP o AVIF · Tamaño máximo: 5 MB</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('photos.store') }}"
                enctype="multipart/form-data"
                class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start"
            >
                @csrf
                <input type="hidden" name="form_context" value="create">

                <div class="lg:col-span-5">
                    <label for="photo-create-title" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Título de la imagen <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="photo-create-title"
                        type="text"
                        name="title"
                        value="{{ $isCreateError ? old('title') : '' }}"
                        required
                        maxlength="255"
                        placeholder="Ej. Atardecer en la costa"
                        class="w-full rounded-xl border {{ $isCreateError && $errors->has('title') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20' }} bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition-all"
                    >
                    @if ($isCreateError && $errors->has('title'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-5">
                    <label for="photo-create-file" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Archivo de imagen <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input
                            id="photo-create-file"
                            type="file"
                            name="file"
                            accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/avif"
                            required
                            data-image-preview-target="photo-create-preview"
                            class="block w-full rounded-xl border {{ $isCreateError && $errors->has('file') ? 'border-rose-400' : 'border-slate-300' }} bg-slate-50/70 text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer cursor-pointer focus:outline-none"
                        >
                        <img
                            id="photo-create-preview"
                            src=""
                            alt="Vista previa seleccionada"
                            class="hidden h-11 w-11 shrink-0 rounded-xl object-cover border border-slate-200 shadow-xs"
                        >
                    </div>
                    @if ($isCreateError && $errors->has('file'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('file') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-2 lg:pt-7">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/25 transition-all cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Subir</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Gallery Grid --}}
        <section>
            @if ($photos->isEmpty())
                <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white/70 px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">Tu galería está vacía</h3>
                    <p class="mt-1.5 text-sm text-slate-500 max-w-md mx-auto">
                        Utiliza el formulario superior para subir tu primera imagen. Aparecerá aquí con vista previa instantánea.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($photos as $photo)
                        <article class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:shadow-lg hover:shadow-slate-900/5 hover:border-slate-300 transition-all duration-200">
                            {{-- Image Preview Trigger --}}
                            <button
                                type="button"
                                data-open-lightbox
                                data-src="{{ $photo->url }}"
                                data-title="{{ $photo->title }}"
                                class="relative aspect-4/3 w-full overflow-hidden bg-slate-100 focus:outline-none cursor-pointer text-left"
                                aria-label="Ver imagen {{ $photo->title }}"
                            >
                                <img
                                    src="{{ $photo->url }}"
                                    alt="{{ $photo->title }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                >
                                <span class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/25 transition-colors flex items-center justify-center">
                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3.5 py-1.5 text-xs font-semibold text-slate-900 shadow-md">
                                        <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        Ampliar
                                    </span>
                                </span>
                            </button>

                            {{-- Card Body --}}
                            <div class="flex flex-1 flex-col justify-between p-4 gap-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900 truncate" title="{{ $photo->title }}">
                                        {{ $photo->title }}
                                    </h3>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Actualizada: {{ $photo->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                {{-- Differentiated Action Buttons --}}
                                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                    <button
                                        type="button"
                                        data-open-lightbox
                                        data-src="{{ $photo->url }}"
                                        data-title="{{ $photo->title }}"
                                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-2.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <span>Ver</span>
                                    </button>

                                    <button
                                        type="button"
                                        data-open-edit-photo
                                        data-action="{{ route('photos.update', $photo) }}"
                                        data-id="{{ $photo->id }}"
                                        data-title="{{ $photo->title }}"
                                        data-src="{{ $photo->url }}"
                                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-50 px-2.5 py-2 text-xs font-semibold text-amber-800 ring-1 ring-amber-200/80 hover:bg-amber-100 transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                        <span>Reemplazar</span>
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route('photos.destroy', $photo) }}"
                                        data-confirm-delete
                                        data-item-title="{{ $photo->title }}"
                                        data-item-type="imagen"
                                        class="shrink-0"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            title="Eliminar imagen"
                                            aria-label="Eliminar {{ $photo->title }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-rose-50 p-2 text-rose-600 ring-1 ring-rose-200/80 hover:bg-rose-600 hover:text-white transition-colors cursor-pointer"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- In-App Lightbox Viewer Modal --}}
    <div
        id="photo-lightbox-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lightbox-title"
    >
        <div class="relative w-full max-w-4xl overflow-hidden rounded-3xl bg-slate-900 shadow-2xl border border-white/10">
            <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-white/10">
                <h3 id="lightbox-title" class="text-sm sm:text-base font-semibold text-white truncate"></h3>
                <div class="flex items-center gap-2 shrink-0">
                    <a
                        id="lightbox-original-link"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-white/20 transition-colors"
                    >
                        <span>Abrir original</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                    <button
                        type="button"
                        data-close-modal="photo-lightbox-modal"
                        aria-label="Cerrar visor"
                        class="rounded-xl bg-white/10 p-1.5 text-slate-200 hover:bg-white/20 transition-colors cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-center bg-slate-950 p-4 sm:p-6">
                <img
                    id="lightbox-image"
                    src=""
                    alt=""
                    class="max-h-[75vh] w-auto max-w-full rounded-xl object-contain"
                >
            </div>
        </div>
    </div>

    {{-- Edit / Replace Photo Modal --}}
    <div
        id="photo-edit-modal"
        class="fixed inset-0 z-50 {{ $editingPhoto ? 'flex' : 'hidden' }} items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="photo-edit-modal-title"
    >
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 id="photo-edit-modal-title" class="text-base font-semibold text-slate-900">
                        Editar o reemplazar imagen
                    </h3>
                    <p class="text-xs text-slate-500">Modifica el título o sube una nueva imagen para sustituir la actual.</p>
                </div>
                <button
                    type="button"
                    data-close-modal="photo-edit-modal"
                    aria-label="Cerrar ventana"
                    class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form
                id="photo-edit-form"
                method="POST"
                action="{{ $editingPhoto ? route('photos.update', $editingPhoto) : '#' }}"
                enctype="multipart/form-data"
                class="p-6 flex flex-col gap-5"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="update">
                <input type="hidden" name="editing_id" id="photo-edit-id" value="{{ $editingPhoto ? $editingPhoto->id : '' }}">

                <div class="flex items-center gap-4 rounded-2xl bg-slate-50 p-3 border border-slate-200/80">
                    <img
                        id="photo-edit-preview"
                        src="{{ $editingPhoto ? $editingPhoto->url : '' }}"
                        alt="Vista previa actual"
                        class="h-16 w-20 rounded-xl object-cover border border-slate-200 bg-white shrink-0"
                    >
                    <div class="text-xs text-slate-600 leading-relaxed">
                        <p class="font-semibold text-slate-800">Imagen actual</p>
                        <p>Si seleccionas un nuevo archivo, el anterior se eliminará automáticamente del almacenamiento.</p>
                    </div>
                </div>

                <div>
                    <label for="photo-edit-title" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Título <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="photo-edit-title"
                        type="text"
                        name="title"
                        value="{{ $editingPhoto ? old('title', $editingPhoto->title) : '' }}"
                        required
                        maxlength="255"
                        class="w-full rounded-xl border {{ $isUpdateError && $errors->has('title') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20' }} bg-white px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 transition-all"
                    >
                    @if ($isUpdateError && $errors->has('title'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div>
                    <label for="photo-edit-file" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Reemplazar imagen <span class="text-xs font-normal text-slate-400">(opcional)</span>
                    </label>
                    <input
                        id="photo-edit-file"
                        type="file"
                        name="file"
                        accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/avif"
                        data-image-preview-target="photo-edit-preview"
                        class="block w-full rounded-xl border {{ $isUpdateError && $errors->has('file') ? 'border-rose-400' : 'border-slate-300' }} bg-slate-50/70 text-sm text-slate-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 file:cursor-pointer cursor-pointer focus:outline-none"
                    >
                    <p class="mt-1.5 text-xs text-slate-500">
                        Deja este campo vacío si solo deseas actualizar el título. Máximo 5 MB.
                    </p>
                    @if ($isUpdateError && $errors->has('file'))
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('file') }}</p>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        data-close-modal="photo-edit-modal"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors cursor-pointer"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
