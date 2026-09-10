<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($this->routeIs('admin.loans.approve')) {
            return $user->can('loans.approve');
        }

        return $user->can('loans.reject');
    }

    public function rules(): array
    {
        $decisionNotesRule = 'nullable';

        if ($this->routeIs('admin.loans.reject')) {
            $decisionNotesRule = 'required';
        }

        return [
            'decision_notes' => [
                $decisionNotesRule,
                'string',
                'max:1000',
            ],
        ];
    }
}
