<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('customer');
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:'.config('loan.amount.min'),
                'max:'.config('loan.amount.max'),
            ],
            'term_months' => [
                'required',
                'integer',
                'min:'.config('loan.term_months.min'),
                'max:'.config('loan.term_months.max'),
            ],
            'purpose' => ['required', 'string', 'max:255'],
            'supporting_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
