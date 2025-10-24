<?php

namespace Inovector\MixpostApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'token_name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string'],
            'expires_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'revoke_existing' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'password.required' => 'Password is required',
            'token_name.required' => 'Token name is required',
            'token_name.max' => 'Token name must not exceed 255 characters',
            'expires_at.date_format' => 'Expiration date must be in format: Y-m-d H:i:s',
        ];
    }
}
