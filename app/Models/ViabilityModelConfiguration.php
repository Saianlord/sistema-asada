<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViabilityModelConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'technical_weight',
        'financial_weight',
        'operational_weight',
        'regulatory_weight',
        'viable_threshold',
        'conditional_threshold',
    ];

    private static ?self $active = null;

    public static function getActive(): self
    {
        if (self::$active === null) {
            self::$active = self::firstOrCreate([], [
                'technical_weight' => 25,
                'financial_weight' => 25,
                'operational_weight' => 25,
                'regulatory_weight' => 25,
                'viable_threshold' => 7.00,
                'conditional_threshold' => 4.00,
            ]);
        }
        return self::$active;
    }

    public static function clearCache(): void
    {
        self::$active = null;
    }
}
