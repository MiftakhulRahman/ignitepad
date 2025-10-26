<?php

namespace App\Http\Requests;

use App\Models\User; // <-- 1. TAMBAHKAN IMPORT USER
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // <-- 2. TAMBAHKAN IMPORT RULE

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
'name' => ['required', 'string', 'max:255'],
            
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],
            
            'nim' => [
                'required', 'string', 'max:20', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'batch_year' => ['nullable', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            
            // --- TAMBAHKAN VALIDASI INI ---
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Maks 2MB
            // -----------------------------
        ];
    }
}