<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->can('loans.view-all');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_merge(config('loan.statuses'), ['active']))],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'min_amount' => ['nullable', 'numeric', 'min:0', 'max:'.config('loan.amount.max')],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'max:'.config('loan.amount.max'), 'gte:min_amount'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
        ];
    }
}
