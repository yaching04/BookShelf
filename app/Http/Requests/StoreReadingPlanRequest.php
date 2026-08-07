<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id'     => ['required', 'exists:books,id'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required'           => '書籍を選択してください。',
            'book_id.exists'             => '選択された書籍が存在しません。',
            'target_date.required'       => '期日を入力してください。',
            'target_date.date'           => '期日は正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $exists = $this->user()
                ->readingPlans()
                ->where('book_id', $this->input('book_id'))
                ->where('status', ReadingPlanStatus::Planned)
                ->exists();

            if ($exists) {
                $validator->errors()->add('book_id', 'この書籍には既に予定中の計画があります。');
            }
        });
    }
}
