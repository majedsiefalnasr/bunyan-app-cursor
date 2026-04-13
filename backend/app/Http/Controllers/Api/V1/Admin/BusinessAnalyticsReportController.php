<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ErrorCode;
use App\Exports\BusinessAnalyticsExport;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Api\V1\Admin\BusinessAnalyticsExportRequest;
use App\Http\Requests\Api\V1\Admin\BusinessAnalyticsReportRequest;
use App\Services\Analytics\BusinessAnalyticsReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BusinessAnalyticsReportController extends BaseController
{
    public function __construct(
        private readonly BusinessAnalyticsReportService $reports,
    ) {
    }

    public function types(): JsonResponse
    {
        $data = [
            'types' => $this->reports->reportTypesMetadata(),
        ];

        return $this->sendSuccess($data, __('تم جلب أنواع التقارير بنجاح', [], 'ar'), 200);
    }

    public function show(BusinessAnalyticsReportRequest $request, string $type): JsonResponse
    {
        $normalized = strtolower($type);
        if (! in_array($normalized, BusinessAnalyticsReportService::REPORT_TYPES, true)) {
            return $this->sendError(
                ErrorCode::VALIDATION_ERROR->value,
                __('نوع التقرير غير مدعوم', [], 'ar'),
                ['type' => [__('نوع التقرير غير مدعوم', [], 'ar')]],
                ErrorCode::VALIDATION_ERROR->httpStatus(),
            );
        }

        $filters = $request->validated();
        $userId = (int) $request->user()->id;
        $payload = $this->reports->generate($normalized, $filters, $userId);

        return $this->sendSuccess($payload, __('تم إنشاء التقرير بنجاح', [], 'ar'), 200);
    }

    public function export(BusinessAnalyticsExportRequest $request, string $type): BinaryFileResponse|JsonResponse|Response
    {
        $normalized = strtolower($type);
        if (! in_array($normalized, BusinessAnalyticsReportService::REPORT_TYPES, true)) {
            return $this->sendError(
                ErrorCode::VALIDATION_ERROR->value,
                __('نوع التقرير غير مدعوم', [], 'ar'),
                ['type' => [__('نوع التقرير غير مدعوم', [], 'ar')]],
                ErrorCode::VALIDATION_ERROR->httpStatus(),
            );
        }

        $filters = $request->validated();
        unset($filters['format']);
        $userId = (int) $request->user()->id;
        $format = $request->string('format')->toString();

        $payload = $this->reports->generate($normalized, $filters, $userId);
        $tabular = $this->reports->tabularFromRows($payload['rows']);

        Log::info('analytics.report.exported', [
            'action' => 'analytics.report.exported',
            'report_type' => $normalized,
            'user_id' => $userId,
            'format' => $format,
        ]);

        $filenameBase = Str::slug($normalized.'-'.now()->format('Y-m-d-His'));

        if ($format === 'xlsx') {
            $export = new BusinessAnalyticsExport($tabular['headers'], $tabular['matrix']);

            return Excel::download($export, $filenameBase.'.xlsx');
        }

        $pdf = Pdf::loadView('reports.analytics-table', [
            'title' => $normalized,
            'headers' => $tabular['headers'],
            'rows' => $tabular['matrix'],
            'summary' => $payload['summary'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filenameBase.'.pdf');
    }
}
