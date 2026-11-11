<?php

namespace App\Actions;

use App\Models\Child;
use App\Models\DailyReport;
use App\Models\User;

class PublishDailyReport
{
    // Crea o actualiza el reporte del día y lo publica.
    // Una sola fila por niño y día (unique en la BD).
    public function handle(User $educator, Child $child, array $data): DailyReport
    {
        $report = DailyReport::where('child_id', $child->id)
            ->whereDate('report_date', today())
            ->first() ?? new DailyReport([
                'child_id' => $child->id,
                'report_date' => today(),
            ]);

        $report->fill($data);
        $report->educator_id = $educator->id;
        $report->published_at = now();
        $report->save();

        return $report;
    }
}