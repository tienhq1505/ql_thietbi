<?php

namespace App\Livewire\Reports;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Exports\DocumentReportExport;
use App\Models\Department;
use App\Models\EquipmentDocument;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class DocumentReportIndex extends Component
{
    use AuthorizesRequests;

    public ?int $department_id = null;

    public function mount()
    {
        $user = auth()->user();
        if (! $user || ! ($user->hasAnyRole(['admin', 'equipment_dept', 'directorate', 'using_dept']) || $user->can('equipment.view'))) {
            abort(403, 'Bạn không có quyền xem báo cáo tổng hợp hồ sơ thiết bị.');
        }

        // Scope to user's department if using_dept
        if ($user->hasRole('using_dept') && $user->department_id) {
            $this->department_id = $user->department_id;
        }
    }

    public function exportExcel()
    {
        $user = auth()->user();
        $scopedDeptId = ($user->hasRole('using_dept') && $user->department_id) ? $user->department_id : $this->department_id;

        return Excel::download(
            new DocumentReportExport($scopedDeptId),
            'bao-cao-tong-hop-ho-so-'.date('Y-m-d').'.xlsx'
        );
    }

    public function render()
    {
        $user = auth()->user();
        $isKhoaScope = $user->hasRole('using_dept') && $user->department_id;

        $query = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->orderBy('name');

        if ($isKhoaScope) {
            $query->where('id', $user->department_id);
        } elseif ($this->department_id) {
            $query->where('id', $this->department_id);
        }

        $departments = $query->get();

        $docTypes = [
            DocumentTypeEnum::LY_LICH,
            DocumentTypeEnum::BIEN_BAN_BAN_GIAO,
            DocumentTypeEnum::HO_SO_LUU_HANH,
            DocumentTypeEnum::CO,
            DocumentTypeEnum::CQ,
            DocumentTypeEnum::TO_KHAI_HAI_QUAN,
            DocumentTypeEnum::TAI_LIEU_KY_THUAT_HDSD,
            DocumentTypeEnum::HO_SO_CONG_BO_DIEU_KIEN_MUA_BAN,
            DocumentTypeEnum::NHAT_KY_SU_DUNG,
            DocumentTypeEnum::QUY_TRINH_VAN_HANH,
            DocumentTypeEnum::HO_SO_BAO_DUONG,
        ];

        $reportData = [];
        $totalEqCount = 0;
        $totalDocCounts = [];
        foreach ($docTypes as $dt) {
            $totalDocCounts[$dt->value] = 0;
        }

        foreach ($departments as $dept) {
            $eqCount = $dept->equipment()->count();
            $totalEqCount += $eqCount;
            $eqIds = $dept->equipment()->pluck('id')->toArray();

            $rowDocs = [];
            foreach ($docTypes as $dt) {
                $hasCount = 0;
                if (! empty($eqIds)) {
                    $hasCount = EquipmentDocument::whereIn('equipment_id', $eqIds)
                        ->where('document_type', $dt->value)
                        ->where('status', DocumentStatusEnum::CO->value)
                        ->count();
                }
                $totalDocCounts[$dt->value] += $hasCount;
                $pct = $eqCount > 0 ? round(($hasCount / $eqCount) * 100, 1) : 0;
                $rowDocs[$dt->value] = [
                    'has' => $hasCount,
                    'total' => $eqCount,
                    'pct' => $pct,
                ];
            }

            $reportData[] = [
                'department' => $dept,
                'eq_count' => $eqCount,
                'docs' => $rowDocs,
            ];
        }

        $allDepartmentsList = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->orderBy('name')->get();

        return view('livewire.reports.document-report-index', [
            'reportData' => $reportData,
            'docTypes' => $docTypes,
            'totalEqCount' => $totalEqCount,
            'totalDocCounts' => $totalDocCounts,
            'allDepartmentsList' => $allDepartmentsList,
            'isKhoaScope' => $isKhoaScope,
        ])->layout('components.layouts.app', ['title' => 'Báo cáo Tổng hợp Hồ sơ Thiết bị - MEMS']);
    }
}
