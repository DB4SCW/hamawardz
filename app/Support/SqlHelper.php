<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SqlHelper
{
    public static function dateOnly(string $column, ?string $alias = null): string
    {
        //get driver name    
        $driver = DB::getDriverName();

        //get db-specific date-only expression
        $expr = match ($driver) {
            'mysql', 'pgsql' => "DATE($column)",
            'sqlite' => "strftime('%Y-%m-%d', $column)",
            'sqlsrv' => "CAST($column AS DATE)",
            default => "DATE($column)",
        };

        //return expression with alias if needed
        return $alias ? "$expr as $alias" : $expr;
    }
}