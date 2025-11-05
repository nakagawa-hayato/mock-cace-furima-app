<?php
// app/Support/helpers.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

if (! function_exists('schema_has_columns')) {
    function schema_has_columns(string $table, array $columns): bool
    {
        try {
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    return false;
                }
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning("schema_has_columns error: {$e->getMessage()}", ['table' => $table, 'columns' => $columns]);
            return false;
        }
    }
}
