<?php

namespace App\Http\Controllers;

use App\Exports\InventoryDiscrepancyExport;
use App\Models\InventorySession;
use App\Services\InventoryReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReportController
{
    public function __construct(
        protected InventoryReportService $reportService
    ) {}

    /**
     * Export Inventory Report as PDF.
     */
    public function exportPdf(Request $request, InventorySession $session)
    {
        $user = $request->user();
        $this->reportService->authorizeAccess($user, $session);

        $session->loadMissing(['department', 'creator']);

        $stats = $this->reportService->calculateStats($session, $user);
        $discrepancyItems = $this->reportService->getDiscrepancyItems($session, $user);

        $pdf = Pdf::loadView('pdf.inventory_report', [
            'session' => $session,
            'stats' => $stats,
            'discrepancyItems' => $discrepancyItems,
        ])->setPaper('a4', 'portrait');

        $slug = Str::slug($session->name);

        return $pdf->download("bien-ban-kiem-ke-{$slug}.pdf");
    }

    /**
     * Export Inventory Discrepancies as Excel.
     */
    public function exportExcel(Request $request, InventorySession $session)
    {
        $user = $request->user();
        $this->reportService->authorizeAccess($user, $session);

        $slug = Str::slug($session->name);

        return Excel::download(
            new InventoryDiscrepancyExport($session, $user),
            "danh-sach-chenh-lech-{$slug}.xlsx"
        );
    }
}
