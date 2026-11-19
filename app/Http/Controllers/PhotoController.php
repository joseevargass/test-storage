<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PhotoController extends Controller
{
    /**
     * Display the image gallery.
     */
    public function index(): View
    {
        try {
            $photos = Photo::query()->latest()->get();
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('error', 'Error al cargar la galería de imágenes.');
            $photos = collect();
        }

        return view('photos.index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Store a newly uploaded image.
     */
    public function store(StorePhotoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedPath = null;

        try {
            $path = Storage::disk('public')->putFile('photos', $request->file('file'));

            if ($path === false) {
                throw new RuntimeException('No se pudo guardar la imagen en el disco público.');
            }

            $storedPath = $path;

            Photo::create([
                'title' => $validated['title'],
                'path' => $storedPath,
            ]);

            return redirect()
                ->route('photos.index')
                ->with('success', 'Imagen subida correctamente.');
        } catch (Throwable $exception) {
            if (is_string($storedPath) && $storedPath !== '' && Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }

            report($exception);

            return redirect()
                ->route('photos.index')
                ->with('error', 'Error al subir la imagen. Inténtalo de nuevo.');
        }
    }

    /**
     * Update the title and/or replace the stored image.
     */
    public function update(UpdatePhotoRequest $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validated();
        $newPath = null;
        $databaseUpdated = false;

        try {
            $oldPath = $photo->path;
            $data = [
                'title' => $validated['title'],
            ];

            if ($request->hasFile('file')) {
                $storedPath = Storage::disk('public')->putFile('photos', $request->file('file'));

                if ($storedPath === false) {
                    throw new RuntimeException('No se pudo guardar la nueva imagen en el disco público.');
                }

                $newPath = $storedPath;
                $data['path'] = $newPath;
            }

            $photo->update($data);
            $databaseUpdated = true;

            if ($newPath !== null && $oldPath !== '' && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            return redirect()
                ->route('photos.index')
                ->with('success', 'Imagen actualizada correctamente.');
        } catch (Throwable $exception) {
            if (! $databaseUpdated && $newPath !== null && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            report($exception);

            return redirect()
                ->route('photos.index')
                ->with('error', 'Error al actualizar la imagen. Inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified image from storage and database.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        try {
            $path = $photo->path;

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                $deleted = Storage::disk('public')->delete($path);

                if (! $deleted) {
                    throw new RuntimeException('No se pudo eliminar el archivo de imagen del almacenamiento.');
                }
            }

            $photo->delete();

            return redirect()
                ->route('photos.index')
                ->with('success', 'Imagen eliminada correctamente.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('photos.index')
                ->with('error', 'Error al eliminar la imagen. Inténtalo de nuevo.');
        }
    }
}
