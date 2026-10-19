<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Logs every create / update / delete with the old and new value of each
 * changed attribute. Applied through BaseModel, so any new model picks it
 * up without being touched.
 */
trait Auditable
{
    use LogsActivity;

    /**
     * Attributes that must never reach the activity log.
     *
     * @var list<string>
     */
    protected array $auditExcept = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept($this->auditExcept)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('model')
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName);
    }
}
