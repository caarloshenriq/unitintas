<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionMiddleware
{
    private string $pivotTable   = 'permission_user';
    private string $permsTable   = 'permissions';
    private string $permsNameCol = 'permission_name';

    public function handle(Request $request, Closure $next, ...$args)
    {
        $user = $request->user();
        if (! $user) abort(403);

        if (count($args) === 1) {
            $args = preg_split('/[|,]/', $args[0]) ?: [];
        }

        $ids = $names = [];
        foreach ($args as $arg) {
            $arg = trim((string) $arg);
            if ($arg === '') continue;
            if (ctype_digit($arg)) $ids[] = (int) $arg; else $names[] = $arg;
        }

        $q = DB::table("{$this->pivotTable} as pu")
            ->join("{$this->permsTable} as p", 'p.id', '=', 'pu.permission_id')
            ->where('pu.user_id', $user->id);

        if ($ids)   $q->whereIn('pu.permission_id', $ids);
        if ($names) $q->orWhereIn("p.{$this->permsNameCol}", $names);

        if (! $q->exists()) abort(403, 'Você não tem permissão para acessar este recurso.');

        return $next($request);
    }
}
