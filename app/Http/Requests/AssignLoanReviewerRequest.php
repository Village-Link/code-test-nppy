<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignLoanReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->can('loans.assign-reviewer');
    }

    public function rules(): array
    {
        return [
            'assigned_reviewer_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
