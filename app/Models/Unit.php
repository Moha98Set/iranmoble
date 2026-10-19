<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends BaseModel
{
    protected $fillable = [
        'building_id',
        'unit_number',
        'floor',
        'bedrooms',
        'capacity',
        'base_price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'floor' => 'integer',
            'bedrooms' => 'integer',
            'capacity' => 'integer',
            'base_price' => Money::class,
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * The way staff refer to a unit out loud:
     * "واحد ۴ ساختمان مریم شعبه پاسداران".
     */
    protected function fullTitle(): Attribute
    {
        return Attribute::get(function (): string {
            $parts = ["واحد {$this->unit_number}"];

            if ($building = $this->building) {
                $parts[] = "ساختمان {$building->name}";

                if ($branch = $building->branch) {
                    $parts[] = "شعبه {$branch->name}";
                }
            }

            return implode(' ', $parts);
        });
    }
}
