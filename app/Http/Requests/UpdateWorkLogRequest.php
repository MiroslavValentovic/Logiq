<?php

namespace App\Http\Requests;

use App\Services\MonthLockService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('work_log'));
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'work_date' => ['required', 'date'],
            'minutes' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $projectId = $this->input('project_id');
            $workDate = $this->input('work_date');
            if (! $projectId || ! $workDate) {
                return;
            }
            $ownerId = $this->route('work_log')->user_id;
            $project = \App\Models\Project::forUser($ownerId)->find($projectId);
            if (! $project) {
                $validator->errors()->add('project_id', __('The selected project is invalid.'));
                return;
            }
            $monthLock = app(MonthLockService::class);
            if ($monthLock->isDateLocked($this->route('work_log')->user_id, $workDate)) {
                $validator->errors()->add('work_date', __('This month is locked. You cannot edit work logs.'));
            }
        });
    }
}
