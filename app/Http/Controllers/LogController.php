<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function registerLog($action, $routine)
    {
        Log::create([
            'action' => $action,
            'user_id' => Auth::user()->id,
            'routine' => $routine,
        ]);
    }
}
