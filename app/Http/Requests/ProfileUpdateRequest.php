<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'sometimes' means: only validate if the field is present in the request.
            // The profile page has separate forms (avatar, personal info, social links)
            // so name/email may be absent from non-personal-info submissions.
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'email'          => [
                'sometimes', 'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone'          => ['nullable', 'string', 'max:30'],
            'country'        => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'bio'            => ['nullable', 'string', 'max:1000'],
            'grade_level'    => ['nullable', 'string', 'max:30'],
            'website'        => ['nullable', 'url', 'max:255'],
            'linkedin_url'   => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:100'],
            'qualification'  => ['nullable', 'string', 'max:255'],
            'avatar'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
