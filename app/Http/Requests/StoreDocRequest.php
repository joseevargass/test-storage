<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('title'))) {
            $this->merge([
                'title' => trim($this->input('title')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf',
                'mimes:pdf',
                'extensions:pdf',
                'max:10240',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título del documento es obligatorio.',
            'title.string' => 'El título debe ser un texto válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'file.required' => 'Debes seleccionar un documento PDF para subir.',
            'file.file' => 'El archivo seleccionado no es válido.',
            'file.uploaded' => 'No se pudo cargar el documento PDF. Comprueba que no supera el tamaño máximo permitido.',
            'file.mimetypes' => 'Formato no permitido. Únicamente se admiten archivos PDF.',
            'file.mimes' => 'Formato no permitido. Únicamente se admiten archivos PDF.',
            'file.extensions' => 'Extensión no permitida. Únicamente se admiten archivos con extensión .pdf.',
            'file.max' => 'El documento PDF no puede superar los 10 MB.',
        ];
    }
}
