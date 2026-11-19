<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocRequest;
use App\Http\Requests\UpdateDocRequest;
use App\Models\Doc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class DocController extends Controller
{
    /**
     * Display the PDF documents list.
     */
    public function index(): View
    {
        try {
            $docs = Doc::query()->latest()->get();
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('error', 'Error al cargar la lista de documentos.');
            $docs = collect();
        }

        return view('docs.index', [
            'docs' => $docs,
        ]);
    }

    /**
     * Store a newly uploaded PDF document.
     */
    public function store(StoreDocRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedPath = null;

        try {
            $path = Storage::disk('public')->putFile('docs', $request->file('file'));

            if ($path === false) {
                throw new RuntimeException('No se pudo guardar el documento PDF en el disco público.');
            }

            $storedPath = $path;

            Doc::create([
                'title' => $validated['title'],
                'path' => $storedPath,
            ]);

            return redirect()
                ->route('docs.index')
                ->with('success', 'Documento PDF subido correctamente.');
        } catch (Throwable $exception) {
            if (is_string($storedPath) && $storedPath !== '' && Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }

            report($exception);

            return redirect()
                ->route('docs.index')
                ->with('error', 'Error al subir el documento PDF. Inténtalo de nuevo.');
        }
    }

    /**
     * Update the title and/or replace the stored PDF document.
     */
    public function update(UpdateDocRequest $request, Doc $doc): RedirectResponse
    {
        $validated = $request->validated();
        $newPath = null;
        $databaseUpdated = false;

        try {
            $oldPath = $doc->path;
            $data = [
                'title' => $validated['title'],
            ];

            if ($request->hasFile('file')) {
                $storedPath = Storage::disk('public')->putFile('docs', $request->file('file'));

                if ($storedPath === false) {
                    throw new RuntimeException('No se pudo guardar el nuevo documento PDF en el disco público.');
                }

                $newPath = $storedPath;
                $data['path'] = $newPath;
            }

            $doc->update($data);
            $databaseUpdated = true;

            if ($newPath !== null && $oldPath !== '' && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            return redirect()
                ->route('docs.index')
                ->with('success', 'Documento PDF actualizado correctamente.');
        } catch (Throwable $exception) {
            if (! $databaseUpdated && $newPath !== null && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            report($exception);

            return redirect()
                ->route('docs.index')
                ->with('error', 'Error al actualizar el documento PDF. Inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified PDF document from storage and database.
     */
    public function destroy(Doc $doc): RedirectResponse
    {
        try {
            $path = $doc->path;

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                $deleted = Storage::disk('public')->delete($path);

                if (! $deleted) {
                    throw new RuntimeException('No se pudo eliminar el archivo PDF del almacenamiento.');
                }
            }

            $doc->delete();

            return redirect()
                ->route('docs.index')
                ->with('success', 'Documento PDF eliminado correctamente.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('docs.index')
                ->with('error', 'Error al eliminar el documento PDF. Inténtalo de nuevo.');
        }
    }
}
