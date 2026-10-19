<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Local subclass so the activity log gets a Filament resource and Shield
 * permissions of its own. Deliberately not extending BaseModel: the log is
 * append-only, so it has no soft deletes and must not log its own writes.
 */
class Activity extends SpatieActivity
{
    //
}
