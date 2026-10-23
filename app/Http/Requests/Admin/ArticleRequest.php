<?php

namespace App\Http\Requests\Admin;

use App\Enums\ArticleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    // The role middleware and ArticlePolicy::manage decide who may do this.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $publishing = $this->input('action') === 'publish_now';

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'type' => ['required', Rule::enum(ArticleType::class)],
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', $publishing ? 'min:100' : 'min:1', 'max:50000'],
            'action' => ['nullable', Rule::in(['save', 'publish_now'])],
        ];
    }

    public function messages(): array
    {
        return [
            'body.min' => 'To publish, the article needs at least 100 characters. You can still save it as a draft.',
        ];
    }
}