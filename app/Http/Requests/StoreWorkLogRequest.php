<?php

namespace App\Http\Requests;

use App\Services\MonthLockService;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\WorkLog::class);
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
            // Pri pridávaní cez admin pre iného používateľa overíme projekt pre cieľového používateľa
            $targetUser = $this->route('user');
            $ownerId = $targetUser instanceof \App\Models\User ? $targetUser->id : $this->user()->id;
            $project = \App\Models\Project::forUser($ownerId)->find($projectId);
            if (! $project) {
                $validator->errors()->add('project_id', __('The selected project is invalid.'));
                return;
            }
            $monthLock = app(MonthLockService::class);
            if ($monthLock->isDateLocked($ownerId, $workDate)) {
                $validator->errors()->add('work_date', __('This month is locked. You cannot add work logs.'));
            }
        });
    }
}
