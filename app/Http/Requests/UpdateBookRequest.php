<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => [
                'required',
                'string',
                'regex:/^[0-9]{13}$/',
                'unique:books,isbn,'.$this->route('book')->id,
            ],
            'published_date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'genres' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.required' => 'ISBNを入力してください。',
            'isbn.regex' => 'ISBNは13桁の半角数字で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',
            'published_date.before_or_equal' => '出版日は今日またはそれ以前の日付を入力してください。',
            'image_url.url' => '画像URLは正しいURL形式で入力してください。',
            'genres.required' => 'ジャンルは少なくとも1つ選択してください。',
        ];
    }
}
