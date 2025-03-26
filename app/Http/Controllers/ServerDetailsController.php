<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServerDetailsResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;

class ServerDetailsController extends Controller
{
    public function __invoke()
    {
        $memoryUsage = memory_get_usage();
        $memoryUsageInMB = number_format($memoryUsage / (1024 * 1024), 2);

        $data = [
            'dbStatus' => ! empty(DB::connection()->getPdo()) ? 'Active' : 'Inactive',
            'memoryUse' => "$memoryUsageInMB MB",
            'lastRunCron' => Carbon::today()->setTime(0, 0, 0),
        ];

        return response()->json(new ServerDetailsResource($data));
    }
}
