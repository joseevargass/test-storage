<?php

use App\Models\Doc;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

function fakePdfUpload(string $name = 'documento.pdf', ?int $sizeInKilobytes = null): UploadedFile
{
    $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";

    if ($sizeInKilobytes !== null) {
        $targetBytes = $sizeInKilobytes * 1024;
        if (strlen($pdfContent) < $targetBytes) {
            $pdfContent .= str_repeat(' ', $targetBytes - strlen($pdfContent));
        }
    }

    return UploadedFile::fake()->createWithContent($name, $pdfContent);
}

describe('Documents list index', function () {
    test('displays uploaded PDF documents with titles, new-tab links, and navigation links', function () {
        $doc = Doc::factory()->create([
            'title' => 'Contrato anual 2026',
            'path' => 'docs/contrato.pdf',
        ]);

        $response = $this->get(route('docs.index'));

        $response->assertOk()
            ->assertSee('Gestor de Documentos PDF')
            ->assertSee('Galería de Imágenes')
            ->assertSee(route('photos.index'))
            ->assertSee($doc->title)
            ->assertSee('/storage/docs/contrato.pdf')
            ->assertSee('target="_blank"', false);
    });

    test('escapes document titles to prevent HTML injection', function () {
        Doc::factory()->create([
            'title' => '<img src=x onerror=alert(1)>',
            'path' => 'docs/seguro.pdf',
        ]);

        $response = $this->get(route('docs.index'));

        $response->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    });
});

describe('Uploading PDF documents', function () {
    test('uploads a valid PDF to public docs directory, saves record, and flashes success', function () {
        Storage::fake('public');

        $pdf = fakePdfUpload('manual.pdf');

        $response = $this->post(route('docs.store'), [
            'title' => 'Manual técnico',
            'file' => $pdf,
        ]);

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF subido correctamente.');

        $doc = Doc::query()->first();

        expect($doc)->not->toBeNull();
        expect($doc->title)->toBe('Manual técnico');
        expect($doc->path)->toStartWith('docs/');
        expect($doc->path)->toEndWith('.pdf');

        Storage::disk('public')->assertExists($doc->path);
    });

    test('rejects non-PDF files, disguised PDFs, missing fields, and files larger than 10 MB', function () {
        Storage::fake('public');

        $this->post(route('docs.store'), [
            'title' => '',
            'file' => null,
        ])->assertSessionHasErrors([
            'title' => 'El título del documento es obligatorio.',
            'file' => 'Debes seleccionar un documento PDF para subir.',
        ]);

        $this->post(route('docs.store'), [
            'title' => 'Imagen en sección de PDF',
            'file' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertSessionHasErrors('file');

        $disguisedPath = tempnam(sys_get_temp_dir(), 'fake_pdf_');
        file_put_contents($disguisedPath, 'esto no es un archivo PDF real');

        try {
            $this->post(route('docs.store'), [
                'title' => 'Texto renombrado a PDF',
                'file' => new UploadedFile($disguisedPath, 'falso.pdf', 'application/pdf', null, true),
            ])->assertSessionHasErrors('file');
        } finally {
            @unlink($disguisedPath);
        }

        $this->post(route('docs.store'), [
            'title' => 'PDF demasiado pesado',
            'file' => fakePdfUpload('enorme.pdf', 10241),
        ])->assertSessionHasErrors([
            'file' => 'El documento PDF no puede superar los 10 MB.',
        ]);

        expect(Doc::query()->count())->toBe(0);
        expect(Storage::disk('public')->allFiles('docs'))->toBeEmpty();
    });

    test('flashes an error message when storing a PDF fails', function () {
        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('putFile')
            ->once()
            ->andReturn(false);

        $response = $this->post(route('docs.store'), [
            'title' => 'Fallo al guardar PDF',
            'file' => fakePdfUpload('error.pdf'),
        ]);

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('error', 'Error al subir el documento PDF. Inténtalo de nuevo.');

        expect(Doc::query()->count())->toBe(0);
    });
});

describe('Updating and replacing PDF documents', function () {
    test('updates only the title without uploading a new PDF file', function () {
        Storage::fake('public');

        $originalPath = fakePdfUpload('original.pdf')->store('docs', 'public');
        $doc = Doc::factory()->create([
            'title' => 'Informe borrador',
            'path' => $originalPath,
        ]);

        $response = $this->put(route('docs.update', $doc), [
            'title' => 'Informe definitivo',
        ]);

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF actualizado correctamente.');

        $doc->refresh();

        expect($doc->title)->toBe('Informe definitivo');
        expect($doc->path)->toBe($originalPath);
        Storage::disk('public')->assertExists($originalPath);
    });

    test('replaces the PDF file, updates the database path, and removes the old PDF from storage', function () {
        Storage::fake('public');

        $oldPath = fakePdfUpload('v1.pdf')->store('docs', 'public');
        $doc = Doc::factory()->create([
            'title' => 'Guía v1',
            'path' => $oldPath,
        ]);

        $response = $this->put(route('docs.update', $doc), [
            'title' => 'Guía v2',
            'file' => fakePdfUpload('v2.pdf'),
        ]);

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF actualizado correctamente.');

        $doc->refresh();

        expect($doc->title)->toBe('Guía v2');
        expect($doc->path)->not->toBe($oldPath);
        expect($doc->path)->toStartWith('docs/');

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($doc->path);
    });

    test('replaces the PDF safely when the previous physical file does not exist', function () {
        Storage::fake('public');

        $doc = Doc::factory()->create([
            'title' => 'PDF perdido',
            'path' => 'docs/no-existe.pdf',
        ]);

        $response = $this->put(route('docs.update', $doc), [
            'title' => 'PDF restaurado',
            'file' => fakePdfUpload('restaurado.pdf'),
        ]);

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF actualizado correctamente.');

        $doc->refresh();

        Storage::disk('public')->assertExists($doc->path);
    });

    test('preserves the old PDF and removes the newly uploaded PDF if database update fails', function () {
        Storage::fake('public');

        $oldPath = fakePdfUpload('protegido.pdf')->store('docs', 'public');
        $doc = Doc::factory()->create([
            'title' => 'Documento protegido',
            'path' => $oldPath,
        ]);

        Doc::updating(function (): void {
            throw new RuntimeException('Fallo simulado de base de datos');
        });

        try {
            $response = $this->put(route('docs.update', $doc), [
                'title' => 'Cambio fallido',
                'file' => fakePdfUpload('nuevo.pdf'),
            ]);
        } finally {
            Doc::flushEventListeners();
        }

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('error', 'Error al actualizar el documento PDF. Inténtalo de nuevo.');

        $doc->refresh();

        expect($doc->path)->toBe($oldPath);
        Storage::disk('public')->assertExists($oldPath);
        expect(Storage::disk('public')->allFiles('docs'))->toBe([$oldPath]);
    });

    test('rejects non-PDF files during replacement while preserving the original PDF', function () {
        Storage::fake('public');

        $oldPath = fakePdfUpload('valido.pdf')->store('docs', 'public');
        $doc = Doc::factory()->create([
            'title' => 'PDF válido',
            'path' => $oldPath,
        ]);

        $response = $this->put(route('docs.update', $doc), [
            'title' => 'Reemplazo inválido',
            'file' => UploadedFile::fake()->image('imagen.png'),
        ]);

        $response->assertSessionHasErrors('file');

        $doc->refresh();

        expect($doc->title)->toBe('PDF válido');
        expect($doc->path)->toBe($oldPath);
        Storage::disk('public')->assertExists($oldPath);
    });
});

describe('Deleting PDF documents', function () {
    test('deletes the physical PDF file and database record', function () {
        Storage::fake('public');

        $storedPath = fakePdfUpload('eliminar.pdf')->store('docs', 'public');
        $doc = Doc::factory()->create([
            'title' => 'PDF para eliminar',
            'path' => $storedPath,
        ]);

        $response = $this->delete(route('docs.destroy', $doc));

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF eliminado correctamente.');

        expect(Doc::query()->find($doc->id))->toBeNull();
        Storage::disk('public')->assertMissing($storedPath);
    });

    test('deletes the database record safely when the physical PDF file is already missing', function () {
        Storage::fake('public');

        $doc = Doc::factory()->create([
            'title' => 'PDF ausente',
            'path' => 'docs/ausente.pdf',
        ]);

        $response = $this->delete(route('docs.destroy', $doc));

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('success', 'Documento PDF eliminado correctamente.');

        expect(Doc::query()->find($doc->id))->toBeNull();
    });

    test('flashes an error message and preserves the database record if PDF deletion fails', function () {
        $doc = Doc::factory()->create([
            'title' => 'PDF bloqueado',
            'path' => 'docs/bloqueado.pdf',
        ]);

        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('exists')
            ->once()
            ->with('docs/bloqueado.pdf')
            ->andReturn(true);
        Storage::shouldReceive('delete')
            ->once()
            ->with('docs/bloqueado.pdf')
            ->andReturn(false);

        $response = $this->delete(route('docs.destroy', $doc));

        $response->assertRedirect(route('docs.index'))
            ->assertSessionHas('error', 'Error al eliminar el documento PDF. Inténtalo de nuevo.');

        expect(Doc::query()->find($doc->id))->not->toBeNull();
    });
});
