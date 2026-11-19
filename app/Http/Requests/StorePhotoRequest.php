<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
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
                'image',
                'mimes:jpg,jpeg,png,gif,webp,bmp,avif',
                'extensions:jpg,jpeg,png,gif,webp,bmp,avif',
                'max:5120',
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
            'title.required' => 'El título de la imagen es obligatorio.',
            'title.string' => 'El título debe ser un texto válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'file.required' => 'Debes seleccionar una imagen para subir.',
            'file.file' => 'El archivo seleccionado no es válido.',
            'file.uploaded' => 'No se pudo cargar la imagen. Comprueba que no supera el tamaño máximo permitido.',
            'file.image' => 'El archivo debe ser una imagen válida.',
            'file.mimes' => 'Formato no permitido. Solo se admiten imágenes JPG, JPEG, PNG, GIF, WEBP, BMP o AVIF.',
            'file.extensions' => 'Extensión no permitida. Solo se admiten imágenes JPG, JPEG, PNG, GIF, WEBP, BMP o AVIF.',
            'file.max' => 'La imagen no puede superar los 5 MB.',
        ];
    }
}
