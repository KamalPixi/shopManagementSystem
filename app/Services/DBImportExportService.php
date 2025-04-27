<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;


final class DBImportExportService
{
    protected $tables = ['users', 'brands', 'categories', 'suppliers', 'warranties', 'customers', 'products', 'product_purchases', 'sales', 'sale_items', 'warranty_sends'];

    public function exportToCSV() {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all_data.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            foreach ($this->tables as $table) {
                // Separator
                fputcsv($handle, ["-- table: $table"]);

                // Get column names
                $columns = DB::getSchemaBuilder()->getColumnListing($table);
                fputcsv($handle, $columns);

                // Stream data using cursor
                foreach (DB::table($table)->cursor() as $row) {
                    fputcsv($handle, (array) $row);
                }

                // Extra blank line between tables (optional)
                fputcsv($handle, []);
            }

            fclose($handle);
        };

        return Response::streamDownload($callback, 'shop_db_backup.csv', $headers);
    }

    public function importFromCSV($filePath)
    {
        $currentTable = null;
        $columns = [];
        $rows = [];
    
        // Define the skip rules at the top
        $skipRules = [
            'users' => ['name' => ['admin']],
            'brands' => ['name' => ['default']],
            'categories' => ['name' => ['default']],
            'warranties' => ['name' => ['6 Months', '1 Year', '2 Years']],
        ];
    
        LazyCollection::make(function () use ($filePath) {
            $handle = fopen(Storage::path($filePath), 'r');
    
            while (($line = fgetcsv($handle)) !== false) {
                yield $line;
            }
    
            fclose($handle);
        })
        ->each(function ($row) use (&$currentTable, &$columns, &$rows, $skipRules) {
            if (empty(array_filter($row))) {
                return; // skip empty lines
            }
    
            if (str_starts_with($row[0], '-- table:')) {
                // Insert/update previous table before moving to next
                if ($currentTable && !empty($rows)) {
                    $this->bulkUpsert($currentTable, $rows);
                    $rows = [];
                }
    
                $currentTable = trim(str_replace('-- table:', '', $row[0]));
                $columns = [];
            } elseif (!$columns) {
                $columns = $row;
            } else {
                if (count($row) !== count($columns)) {
                    return; // mismatch, skip
                }
    
                $data = array_combine($columns, $row);

                // Convert empty strings to null
                $data = array_map(function ($value) {
                    return $value === '' ? null : $value;
                }, $data);
                
    
                // ✅ Apply skip rules
                if (isset($skipRules[$currentTable])) {
                    foreach ($skipRules[$currentTable] as $field => $skipValues) {
                        if (isset($data[$field]) && in_array(strtolower($data[$field]), array_map('strtolower', $skipValues))) {
                            \Log::info('Skipped row due to skip rule.', [
                                'table' => $currentTable,
                                'field' => $field,
                                'value' => $data[$field],
                            ]);
                            return;
                        }
                    }
                }
    
                $rows[] = $data;
    
                if (count($rows) >= 500) {
                    $this->bulkUpsert($currentTable, $rows);
                    $rows = [];
                }
            }
        });
    
        // Insert/update remaining rows after loop
        if ($currentTable && !empty($rows)) {
            $this->bulkUpsert($currentTable, $rows);
        }
    }
    
    /**
     * Insert or update records in bulk.
     */
    protected function bulkUpsert($table, $rows)
    {
        if (empty($rows)) return;
    
        // Try to detect primary key if you want
        $uniqueBy = 'id'; // you can improve this later
    
        if (isset($rows[0][$uniqueBy])) {
            // If ID exists, use upsert
            DB::table($table)->upsert(
                $rows,
                [$uniqueBy],    // unique key(s)
                array_keys($rows[0]) // columns to update
            );
        } else {
            // Else normal insert
            DB::table($table)->insert($rows);
        }
    }
    
}
