<?php

namespace App\Http\Requests\playlist;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // احراز هویت از route middleware هندل شده
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public'   => ['boolean'],
            'song_ids'    => ['nullable', 'array'],
            'song_ids.*'  => ['integer', 'exists:songs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            // فیلد نام پلی‌لیست
            'name.required' => 'نام پلی‌لیست الزامی است.',
            'name.string'   => 'نام پلی‌لیست باید یک متن باشد.',
            'name.max'      => 'نام پلی‌لیست نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            // توضیحات
            'description.string' => 'توضیحات باید یک متن باشد.',
            'description.max'    => 'توضیحات نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',

            // عمومی یا خصوصی بودن
            'is_public.boolean' => 'وضعیت عمومی/خصوصی باید صحیح باشد.',

            // آرایه آهنگ‌ها
            'song_ids.array'     => 'لیست آهنگ‌ها باید یک آرایه باشد.',
            'song_ids.*.integer' => 'شناسه هر آهنگ باید عدد باشد.',
            'song_ids.*.exists'  => 'یکی از آهنگ‌های انتخاب‌شده معتبر نیست.',
        ];
    }
}