<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** CSV-download (puntkomma, UTF-8 met BOM zodat Excel het direct goed opent). */
class Csv
{
    public static function download(string $naam, array $kop, iterable $rijen): StreamedResponse
    {
        return response()->streamDownload(function () use ($kop, $rijen) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $kop, ';');
            foreach ($rijen as $r) {
                fputcsv($out, array_map(fn ($v) => is_bool($v) ? ($v ? 'ja' : 'nee') : (is_float($v) ? number_format($v, 2, ',', '') : (string) ($v ?? '')), $r), ';');
            }
            fclose($out);
        }, $naam.'-'.now()->format('Ymd-Hi').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
