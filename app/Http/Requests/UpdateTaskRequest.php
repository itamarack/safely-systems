<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'due_date'          => ['required', 'date'],
            'user_id'           => ['required', 'exists:users,id'],
            'priority'          => ['required', new Enum(TaskPriority::class)],
            'status'            => ['required', new Enum(TaskStatus::class)],
            'corrective_action' => ['required_if:status,non_compliant', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'corrective_action.required_if' => 'A corrective action note is required when marking a task as non-compliant.',
        ];
    }
}
