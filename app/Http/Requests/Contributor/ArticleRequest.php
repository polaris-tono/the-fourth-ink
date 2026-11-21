<?php

namespace App\Http\Requests\Contributor;

use App\Enums\ArticleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    // Who may do what is decided by ArticlePolicy in the controller.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = array_map(fn ($t) => $t->value, ArticleType::contributorTypes());
        $submitting = $this->input('action') === 'submit';

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'type' => ['required', Rule::in($types)],
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', $submitting ? 'min:100' : 'min:1', 'max:50000'],
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'cover_alt' => ['nullable', 'string', 'max:200'],
            'remove_cover' => ['nullable', 'boolean'],
            'action' => ['required', Rule::in(['save_draft', 'submit'])],
        ];
    }

    public function messages(): array
    {
        return [
            'body.min' => 'To submit for review the article needs at least 100 characters. You can still save it as a draft.',
        ];
    }
}