<?php

/**
 * ACTO Maps - Update Layer Request
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_layer');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
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
        ];
    }
}

