@if (session('success') || session('error') || $errors->any())
    <div class="mb-6 flex flex-col gap-3">
        @if (session('success'))
            <div
                role="status"
                aria-live="polite"
                data-alert
                data-auto-dismiss="7000"
                class="flex items-start justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-emerald-900 shadow-xs transition-all duration-300"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <div class="text-sm font-medium leading-6">
                        {{ session('success') }}
                    </div>
                </div>
                <button
                    type="button"
                    data-dismiss-alert
                    aria-label="Cerrar mensaje"
                    class="rounded-lg p-1 text-emerald-700/70 hover:bg-emerald-100 hover:text-emerald-900 transition-colors cursor-pointer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                role="alert"
                aria-live="assertive"
                data-alert
                class="flex items-start justify-between gap-4 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3.5 text-rose-900 shadow-xs transition-all duration-300"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div class="text-sm font-medium leading-6">
                        {{ session('error') }}
                    </div>
                </div>
                <button
                    type="button"
                    data-dismiss-alert
                    aria-label="Cerrar error"
                    class="rounded-lg p-1 text-rose-700/70 hover:bg-rose-100 hover:text-rose-900 transition-colors cursor-pointer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div
                role="alert"
                aria-live="assertive"
                data-alert
                class="flex items-start justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50/95 px-4 py-3.5 text-amber-950 shadow-xs transition-all duration-300"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div class="text-sm">
                        <p class="font-semibold leading-6">Revisa los siguientes errores de validación:</p>
                        <ul class="mt-1 list-disc list-inside space-y-0.5 text-amber-900">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button
                    type="button"
                    data-dismiss-alert
                    aria-label="Cerrar errores de validación"
                    class="rounded-lg p-1 text-amber-700/70 hover:bg-amber-100 hover:text-amber-950 transition-colors cursor-pointer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endif
