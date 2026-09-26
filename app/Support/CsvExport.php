<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Minimal CSV export helper (spec §45 "reports.export") — a native
 * streamDownload is enough here, no need to pull in a spreadsheet package
 * for plain tabular exports (spec §2 "jangan menambahkan package tanpa
 * alasan jelas").
 */
class CsvExport
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
