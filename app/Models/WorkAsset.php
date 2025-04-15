<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkAsset extends Model
{
    protected $fillable = [
        'street',
        'grand_total',
        'damage_share_total',
        'is_completed',
    ];

    protected $casts = [
        'grand_total' => 'float',
        'damage_share_total' => 'float',
        'is_completed' => 'bool',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(WorkAssetDetail::class, 'work_asset_id');
    }

    public function recalculateTotal(): void
    {
        if ($this->is_completed === true) {
            return;
        }

        $damageShareTotal = 0;

        $total = $this->details->sum(function ($detail) use (&$damageShareTotal) {
            $result = self::calculateGrandTotal($detail);
            $damageShareTotal += $result['damage_share'];
            return $result['total'];
        });

        $this->update([
            'grand_total' => round($total, 2),
            'damage_share_total' => round($damageShareTotal, 2),
        ]);
    }

    private static function calculateGrandTotal(WorkAssetDetail $workAssetDetail): array
    {
        $workedTime = is_numeric($workAssetDetail->time_spend) ? (float)$workAssetDetail->time_spend : 0;
        $equipment = getEquipmentById($workAssetDetail->equipment_id);

        $total = $workAssetDetail->fuel_total_price +
            $workAssetDetail->item_total_price +
            $workAssetDetail->product_price_total +
            $workAssetDetail->person_salary_total;

        $damageShare = 0;

        if ($equipment) {
            if ($equipment->type === 'rent') {
                $total += ($equipment->price / 8) * min($workedTime, 8);
            } else {
                $totalDamage = (float) optional($equipment->damages())->sum('total_price');
                $totalTimeUsed = (float) optional($equipment->workAssetDetails())->sum('time_spend');

                if ($totalTimeUsed > 0 && $workedTime > 0) {
                    $shareRatio = $workedTime / $totalTimeUsed;
                    $damageShare = $totalDamage * $shareRatio;
                    $total += $damageShare;
                }
            }
        }

        return [
            'total' => round($total, 2),
            'damage_share' => round($damageShare, 2),
        ];
    }
}
