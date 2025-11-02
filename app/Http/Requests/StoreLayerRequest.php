<?php

/**
 * ACTO Maps - Store Layer Request
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Http\Requests;

use App\Http\Rules\ValidGeojsonFile;
use Illuminate\Foundation\Http\FormRequest;

class StoreLayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_layer');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'geojson_file' => ['required', 'file', 'mimes:json,geojson', 'max:10240', new ValidGeojsonFile()],
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
            'name.required' => 'O nome da camada é obrigatório.',
            'name.max' => 'O nome da camada não pode ter mais de 100 caracteres.',
            'geojson_file.required' => 'O arquivo GeoJSON é obrigatório.',
            'geojson_file.file' => 'O arquivo enviado não é válido.',
            'geojson_file.mimes' => 'O arquivo deve ser do tipo JSON ou GeoJSON.',
            'geojson_file.max' => 'O arquivo não pode ser maior que 10MB.',
        ];
    }
}

