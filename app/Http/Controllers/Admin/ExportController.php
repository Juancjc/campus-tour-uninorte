<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\AdminAuditLog;
use App\Services\ReportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(ReportFilterRequest $request, ReportService $reports, string $type): StreamedResponse
    {
        [$headers, $rows] = $this->data($reports, $request, $type);
        $this->audit($request, $type, 'csv');

        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($stream, array_values($row), ';');
            }
            fclose($stream);
        }, "campus-tour-{$type}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function xlsx(ReportFilterRequest $request, ReportService $reports, string $type): StreamedResponse
    {
        [$headers, $rows] = $this->data($reports, $request, $type);
        $this->audit($request, $type, 'xlsx');

        return response()->streamDownload(function () use ($headers, $rows): void {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray(array_map('array_values', $rows), null, 'A2');
            $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, "campus-tour-{$type}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array{0: array<int, string>, 1: array<int, array<string, mixed>>} */
    private function data(ReportService $reports, ReportFilterRequest $request, string $type): array
    {
        $allowed = ['schools', 'professions', 'games'];
        abort_unless(in_array($type, $allowed, true), 404);
        $rows = $reports->reports($request->validated())[$type]->values()->all();
        $headers = match ($type) {
            'schools' => ['Instituição', 'Alunos'],
            'professions' => ['Profissão', 'Categoria', 'Alunos'],
            default => ['Jogo', 'Partidas', 'Conclusões', 'Abandonos', 'Média de pontos', 'Média de tempo (ms)'],
        };

        return [$headers, $rows];
    }

    private function audit(ReportFilterRequest $request, string $type, string $format): void
    {
        AdminAuditLog::query()->create([
            'admin_id' => $request->user()->id,
            'action' => 'exported_report',
            'entity' => $type,
            'ip_address' => $request->ip(),
            'metadata' => ['format' => $format, 'filters' => $request->validated()],
        ]);
    }
}
