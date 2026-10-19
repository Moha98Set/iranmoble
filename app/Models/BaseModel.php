<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Every domain model extends this, so soft deletes and activity logging are
 * on by default rather than something each model has to remember.
 */
abstract class BaseModel extends Model
{
    use Auditable, SoftDeletes;
}
