<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Log the incoming request data for debugging
        \Log::debug('ProfileUpdateRequest - Request data:', [
            'all' => $this->all(),
            'has_avatar_type' => $this->has('avatar_type'),
            'name_input' => $this->input('name')
        ]);

        // If we're only updating the avatar, name is not required
        $isAvatarOnlyUpdate = $this->has('avatar_type') && ($this->input('name') === null || $this->input('name') === '');

        \Log::debug('ProfileUpdateRequest - Is avatar only update: ' . ($isAvatarOnlyUpdate ? 'true' : 'false'));

        $rules = [
            'name' => $isAvatarOnlyUpdate ? ['sometimes', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'lowercase',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Only enforce uniqueness if shared credentials are not allowed
                    if (!config('auth.allow_shared_credentials')) {
                        $existingUser = User::whereHas('emails', function ($query) use ($value) {
                            $query->where('address', $value);
                        })->where('id', '!=', $this->user()->id)->first();

                        if ($existingUser) {
                            $fail('The email has already been taken.');
                        }
                    }
                },
            ],
            'avatar_type' => ['sometimes', 'string', 'in:initials,silhouette,gravatar,image'],
            'avatar_image' => [
                'sometimes',
                'required_if:avatar_type,image',
                'image',
                'max:2048', // 2MB max size
            ],
        ];

        \Log::debug('ProfileUpdateRequest - Validation rules:', $rules);

        return $rules;
    }
}
