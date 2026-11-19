<?php

use App\Models\Photo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

describe('Photo gallery index', function () {
    test('displays uploaded photos with titles, public storage URLs, and navigation links', function () {
        $photo = Photo::factory()->create([
            'title' => 'Paisaje alpino',
            'path' => 'photos/alpine.jpg',
        ]);

        $response = $this->get(route('photos.index'));

        $response->assertOk()
            ->assertSee('Galería de Imágenes')
            ->assertSee('Documentos PDF')
            ->assertSee(route('docs.index'))
            ->assertSee($photo->title)
            ->assertSee('/storage/photos/alpine.jpg');
    });

    test('escapes photo titles to prevent HTML injection in gallery markup', function () {
        Photo::factory()->create([
            'title' => '<script>alert("xss")</script>',
            'path' => 'photos/safe.jpg',
        ]);

        $response = $this->get(route('photos.index'));

        $response->assertOk()
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    });
});

describe('Uploading photos', function () {
    test('uploads a valid image to public photos directory, saves record, and flashes success', function () {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('montana.jpg', 800, 600);

        $response = $this->post(route('photos.store'), [
            'title' => 'Montaña nevada',
            'file' => $image,
        ]);

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen subida correctamente.');

        $photo = Photo::query()->first();

        expect($photo)->not->toBeNull();
        expect($photo->title)->toBe('Montaña nevada');
        expect($photo->path)->toStartWith('photos/');

        Storage::disk('public')->assertExists($photo->path);
    });

    test('rejects invalid formats, missing fields, and images larger than 5 MB', function () {
        Storage::fake('public');

        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";

        $this->post(route('photos.store'), [
            'title' => '',
            'file' => null,
        ])->assertSessionHasErrors([
            'title' => 'El título de la imagen es obligatorio.',
            'file' => 'Debes seleccionar una imagen para subir.',
        ]);

        $this->post(route('photos.store'), [
            'title' => 'Archivo PDF no válido',
            'file' => UploadedFile::fake()->createWithContent('documento.pdf', $pdfContent),
        ])->assertSessionHasErrors('file');

        $disguisedPath = tempnam(sys_get_temp_dir(), 'fake_jpg_');
        file_put_contents($disguisedPath, 'contenido de texto plano');

        try {
            $this->post(route('photos.store'), [
                'title' => 'Texto disfrazado de JPG',
                'file' => new UploadedFile($disguisedPath, 'falso.jpg', 'image/jpeg', null, true),
            ])->assertSessionHasErrors('file');
        } finally {
            @unlink($disguisedPath);
        }

        $this->post(route('photos.store'), [
            'title' => 'Imagen demasiado grande',
            'file' => UploadedFile::fake()->image('grande.jpg')->size(5121),
        ])->assertSessionHasErrors([
            'file' => 'La imagen no puede superar los 5 MB.',
        ]);

        expect(Photo::query()->count())->toBe(0);
        expect(Storage::disk('public')->allFiles('photos'))->toBeEmpty();
    });

    test('flashes an error message and leaves no orphan file when storing fails', function () {
        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('putFile')
            ->once()
            ->andReturn(false);

        $response = $this->post(route('photos.store'), [
            'title' => 'Fallo de disco',
            'file' => UploadedFile::fake()->image('error.png'),
        ]);

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('error', 'Error al subir la imagen. Inténtalo de nuevo.');

        expect(Photo::query()->count())->toBe(0);
    });
});

describe('Updating and replacing photos', function () {
    test('updates only the title without requiring a new image file', function () {
        Storage::fake('public');

        $originalPath = UploadedFile::fake()->image('original.png')->store('photos', 'public');
        $photo = Photo::factory()->create([
            'title' => 'Título original',
            'path' => $originalPath,
        ]);

        $response = $this->put(route('photos.update', $photo), [
            'title' => 'Título actualizado',
        ]);

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen actualizada correctamente.');

        $photo->refresh();

        expect($photo->title)->toBe('Título actualizado');
        expect($photo->path)->toBe($originalPath);

        Storage::disk('public')->assertExists($originalPath);
    });

    test('replaces the image file, updates the database path, and deletes the old physical file', function () {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('vieja.jpg')->store('photos', 'public');
        $photo = Photo::factory()->create([
            'title' => 'Foto antigua',
            'path' => $oldPath,
        ]);

        $newImage = UploadedFile::fake()->image('nueva.png', 640, 480);

        $response = $this->put(route('photos.update', $photo), [
            'title' => 'Foto renovada',
            'file' => $newImage,
        ]);

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen actualizada correctamente.');

        $photo->refresh();

        expect($photo->title)->toBe('Foto renovada');
        expect($photo->path)->not->toBe($oldPath);
        expect($photo->path)->toStartWith('photos/');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($photo->path);
    });

    test('replaces the image without error even when the previous physical file is already missing', function () {
        Storage::fake('public');

        $photo = Photo::factory()->create([
            'title' => 'Foto huérfana',
            'path' => 'photos/inexistente.jpg',
        ]);

        $response = $this->put(route('photos.update', $photo), [
            'title' => 'Foto recuperada',
            'file' => UploadedFile::fake()->image('recuperada.webp'),
        ]);

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen actualizada correctamente.');

        $photo->refresh();

        Storage::disk('public')->assertExists($photo->path);
    });

    test('keeps the previous file and cleans up the new file if database update fails during replacement', function () {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('segura.jpg')->store('photos', 'public');
        $photo = Photo::factory()->create([
            'title' => 'Foto protegida',
            'path' => $oldPath,
        ]);

        Photo::updating(function (): void {
            throw new RuntimeException('Fallo simulado de base de datos');
        });

        try {
            $response = $this->put(route('photos.update', $photo), [
                'title' => 'Intento fallido',
                'file' => UploadedFile::fake()->image('nueva.jpg'),
            ]);
        } finally {
            Photo::flushEventListeners();
        }

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('error', 'Error al actualizar la imagen. Inténtalo de nuevo.');

        $photo->refresh();

        expect($photo->path)->toBe($oldPath);
        Storage::disk('public')->assertExists($oldPath);
        expect(Storage::disk('public')->allFiles('photos'))->toBe([$oldPath]);
    });

    test('rejects invalid replacement files while keeping the original file intact', function () {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('actual.jpg')->store('photos', 'public');
        $photo = Photo::factory()->create([
            'title' => 'Imagen válida',
            'path' => $oldPath,
        ]);

        $response = $this->put(route('photos.update', $photo), [
            'title' => 'Imagen con archivo malo',
            'file' => UploadedFile::fake()->createWithContent('malo.txt', 'no soy una imagen'),
        ]);

        $response->assertSessionHasErrors('file');

        $photo->refresh();

        expect($photo->title)->toBe('Imagen válida');
        expect($photo->path)->toBe($oldPath);
        Storage::disk('public')->assertExists($oldPath);
    });
});

describe('Deleting photos', function () {
    test('deletes the physical image file and database record', function () {
        Storage::fake('public');

        $storedPath = UploadedFile::fake()->image('borrar.jpg')->store('photos', 'public');
        $photo = Photo::factory()->create([
            'title' => 'Para eliminar',
            'path' => $storedPath,
        ]);

        $response = $this->delete(route('photos.destroy', $photo));

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen eliminada correctamente.');

        expect(Photo::query()->find($photo->id))->toBeNull();
        Storage::disk('public')->assertMissing($storedPath);
    });

    test('deletes the database record safely when the physical image file is already missing', function () {
        Storage::fake('public');

        $photo = Photo::factory()->create([
            'title' => 'Archivo ausente',
            'path' => 'photos/ya-no-existe.png',
        ]);

        $response = $this->delete(route('photos.destroy', $photo));

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('success', 'Imagen eliminada correctamente.');

        expect(Photo::query()->find($photo->id))->toBeNull();
    });

    test('flashes an error message and preserves the database record if file deletion fails', function () {
        $photo = Photo::factory()->create([
            'title' => 'Archivo bloqueado',
            'path' => 'photos/bloqueado.jpg',
        ]);

        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('exists')
            ->once()
            ->with('photos/bloqueado.jpg')
            ->andReturn(true);
        Storage::shouldReceive('delete')
            ->once()
            ->with('photos/bloqueado.jpg')
            ->andReturn(false);

        $response = $this->delete(route('photos.destroy', $photo));

        $response->assertRedirect(route('photos.index'))
            ->assertSessionHas('error', 'Error al eliminar la imagen. Inténtalo de nuevo.');

        expect(Photo::query()->find($photo->id))->not->toBeNull();
    });
});
