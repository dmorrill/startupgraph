<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanySearchRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'category' => ['nullable', 'string', Rule::in(array_keys(Company::CATEGORIES))],
            'sort' => 'nullable|string|in:name,founded_date,city,country,category,funding_rounds_sum_amount,latest_funding_date',
            'direction' => 'nullable|string|in:asc,desc',
            'funded_after' => 'nullable|date|before_or_equal:today',
            'funded_before' => 'nullable|date|before_or_equal:today|after:funded_after',
            'funded_recent' => 'nullable|string|in:3m,6m,1y,2y',
            'per_page' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category.in' => 'The selected category is invalid.',
            'sort.in' => 'The sort field must be one of: name, founded_date, city, country, category, funding_rounds_sum_amount, latest_funding_date.',
            'direction.in' => 'The direction must be either asc or desc.',
            'funded_before.after' => 'The "funded before" date must be after the "funded after" date.',
            'per_page.max' => 'The maximum items per page is 100.',
        ];
    }
}
