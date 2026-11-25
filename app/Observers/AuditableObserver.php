<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    /**
     * Fields that must never be recorded in audit logs.
     */
    protected array $blacklistedFields = [
        'password',
        'remember_token',
        'password_confirmation',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $newValues = $this->filterAttributes($model->getAttributes());

        $this->recordLog($model, 'created', null, $newValues);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        // Bỏ department_id khỏi observer ghi cho User để tránh ghi trùng (UserIndex ghi tường minh kèm tên khoa)
        if ($model instanceof User) {
            unset($changes['department_id']);
        }

        $filteredChanges = $this->filterAttributes($changes);

        // If no meaningful attributes changed, skip logging
        if (empty($filteredChanges)) {
            return;
        }

        $oldValues = [];
        foreach (array_keys($filteredChanges) as $key) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $oldValues = $this->filterAttributes($oldValues);

        $this->recordLog($model, 'updated', $oldValues, $filteredChanges);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $oldValues = $this->filterAttributes($model->getAttributes());

        $this->recordLog($model, 'deleted', $oldValues, null);
    }

    /**
     * Filter out blacklisted and sensitive fields.
     */
    protected function filterAttributes(array $attributes): array
    {
        foreach ($this->blacklistedFields as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Record an audit log entry.
     */
    protected function recordLog(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        // Prevent auditing the AuditLog model itself
        if ($model instanceof AuditLog) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log error or let it pass gracefully in unhandled environments
            report($e);
        }
    }
}
