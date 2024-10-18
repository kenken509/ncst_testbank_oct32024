<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function show()
    {
        $logs = ActivityLog::with('user')->latest()->get();
        
        return inertia('Dashboard/Logs/LogsShow',[
            'logs' => $logs,
        ]);
    }
}
