<?php

namespace App\Livewire\Equipment;

use App\Imports\EquipmentSheetImport;
use App\Models\Equipment;
use App\Models\ImportBatch;
use App\Services\EquipmentImportValidator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentImport extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public $file;

    public string $step = 'upload'; // upload | preview | done

    // previewRows KHÔNG lưu trong public property (tránh giả mạo qua Livewire wire)
    // Được lưu server-side trong Cache với key riêng theo session
    public array $summary = [
        'total' => 0,
        'valid' => 0,
        'error' => 0,
        'skipped' => 0,
    ];

    public ?int $createdBatchId = null;

    public int $page = 1;

    public int $perPage = 20;

    /** Lấy cache key duy nhất cho session hiện tại */
    private function previewCacheKey(): string
    {
        return 'import_preview_'.session()->getId().'_'.auth()->id();
    }

    /** Đọc previewRows từ server-side cache */
    private function getPreviewRows(): array
    {
        return Cache::get($this->previewCacheKey(), []);
    }

    /** Lưu previewRows vào server-side cache (TTL 2 giờ) */
    private function setPreviewRows(array $rows): void
    {
        Cache::put($this->previewCacheKey(), $rows, now()->addHours(2));
    }

    /** Xóa previewRows khỏi cache */
    private function clearPreviewRows(): void
    {
        Cache::forget($this->previewCacheKey());
    }

    public function mount()
    {
        $this->authorize('import', Equipment::class);
    }

    public function downloadSample()
    {
        $this->authorize('import', Equipment::class);

        $samplePath = storage_path('app/samples/sample_import.xlsx');
        if (! File::exists($samplePath)) {
            Artisan::call('equipment:generate-sample-import');
        }

        return response()->download($samplePath, 'file_mau_nhap_thiet_bi.xlsx');
    }

    public function uploadAndPreview(EquipmentImportValidator $validator)
    {
        $this->authorize('import', Equipment::class);

        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'file.required' => 'Vui lòng chọn file Excel để tải lên.',
            'file.mimes' => 'File tải lên phải có định dạng .xlsx hoặc .xls.',
            'file.max' => 'Dung lượng file tối đa là 5MB.',
        ]);

        $import = new EquipmentSheetImport;
        Excel::import($import, $this->file->getRealPath());

        $rawRows = $import->rows;

        if (empty($rawRows)) {
            $this->addError('file', 'File Excel không có dữ liệu hoặc sai tiêu đề cột.');

            return;
        }

        if (count($rawRows) > 500) {
            $this->addError('file', 'File vượt quá số dòng tối đa cho phép (500 dòng). Vui lòng chia nhỏ file.');

            return;
        }

        $result = $validator->validateRows($rawRows);

        $this->setPreviewRows($result['rows']);
        $this->summary = [
            'total' => $result['total_count'],
            'valid' => $result['valid_count'],
            'error' => $result['error_count'],
            'skipped' => 0,
        ];

        $this->step = 'preview';
        $this->page = 1;
    }

    public function toggleSkipRow(int $index)
    {
        $rows = $this->getPreviewRows();
        if (isset($rows[$index])) {
            $rows[$index]['skip'] = ! $rows[$index]['skip'];
            $this->setPreviewRows($rows);
            $this->recalculateSummary();
        }
    }

    public function resetUpload()
    {
        $this->clearPreviewRows();
        $this->reset(['file', 'summary', 'createdBatchId']);
        $this->step = 'upload';
        $this->page = 1;
    }

    public function confirmImport(EquipmentImportValidator $validator)
    {
        $this->authorize('import', Equipment::class);

        // Re-validate server-side (không tin previewRows từ client)
        $previewRows = $this->getPreviewRows();

        if (empty($previewRows)) {
            session()->flash('error', 'Phiên nhập đã hết hạn hoặc không có dữ liệu. Vui lòng tải lại file.');
            $this->step = 'upload';

            return;
        }

        $validToImport = array_filter($previewRows, fn ($r) => $r['status'] === 'valid' && ! $r['skip']);

        if (empty($validToImport)) {
            session()->flash('error', 'Không có dòng hợp lệ nào được chọn để nhập.');

            return;
        }

        // Re-validate từng dòng sẽ import (đảm bảo dữ liệu vẫn hợp lệ tại thời điểm commit)
        $codesInBatch = array_filter(array_map(fn ($r) => $r['data']['code'] ?? null, $validToImport));
        $conflictCodes = Equipment::withTrashed()
            ->whereIn('code', $codesInBatch)
            ->pluck('code')
            ->all();

        $serialsInBatch = array_filter(array_map(fn ($r) => $r['data']['serial_number'] ?? null, $validToImport));
        $conflictSerials = ! empty($serialsInBatch)
            ? Equipment::withTrashed()
                ->whereIn('serial_number', $serialsInBatch)
                ->pluck('serial_number')
                ->all()
            : [];

        if (! empty($conflictCodes) || ! empty($conflictSerials)) {
            $msg = [];
            if (! empty($conflictCodes)) {
                $msg[] = 'Mã thiết bị đã bị trùng: '.implode(', ', $conflictCodes);
            }
            if (! empty($conflictSerials)) {
                $msg[] = 'Số serial đã bị trùng: '.implode(', ', $conflictSerials);
            }
            session()->flash('error', implode('. ', $msg).'. Vui lòng tải lại file.');
            $this->clearPreviewRows();
            $this->step = 'upload';

            return;
        }

        $batchRecord = ImportBatch::create([
            'file_name' => $this->file ? $this->file->getClientOriginalName() : 'danh-sach-thiet-bi.xlsx',
            'imported_by' => auth()->id(),
            'total_rows' => count($previewRows),
            'success_rows' => 0,
            'failed_rows' => 0,
            'error_details' => [],
            'status' => 'processing',
        ]);

        $successCount = 0;
        $failedCount = 0;
        $errorDetails = [];

        // Ghi log các dòng bỏ qua / lỗi
        foreach ($previewRows as $row) {
            if ($row['status'] !== 'valid' || $row['skip']) {
                $failedCount++;
                $errorDetails[] = [
                    'row' => $row['row'],
                    'code' => $row['display']['code'] ?? 'N/A',
                    'errors' => $row['skip'] && $row['status'] === 'valid'
                        ? ['Bị người dùng bỏ qua thủ công']
                        : $row['errors'],
                ];
            }
        }

        try {
            DB::transaction(function () use ($batchRecord, $validToImport, &$successCount) {
                foreach ($validToImport as $row) {
                    Equipment::create(array_merge($row['data'], [
                        'import_batch_id' => $batchRecord->id,
                    ]));
                    $successCount++;
                }
            });

            $batchRecord->update([
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'error_details' => $errorDetails,
                'status' => $failedCount > 0 ? 'completed_with_errors' : 'completed',
            ]);
        } catch (\Throwable $e) {
            $batchRecord->update([
                'success_rows' => 0,
                'failed_rows' => count($previewRows),
                'error_details' => array_merge($errorDetails, [
                    ['error' => $e->getMessage()],
                ]),
                'status' => 'failed',
            ]);

            $this->clearPreviewRows();
            $this->step = 'upload';
            session()->flash('error', 'Có lỗi xảy ra trong quá trình nhập dữ liệu: '.$e->getMessage());

            return;
        }

        $this->clearPreviewRows();
        $this->createdBatchId = $batchRecord->id;
        $this->step = 'done';
        session()->flash('message', "Nhập thành công {$successCount} thiết bị vào hệ thống.");
    }

    private function recalculateSummary()
    {
        $valid = 0;
        $error = 0;
        $skipped = 0;

        foreach ($this->getPreviewRows() as $r) {
            if ($r['skip']) {
                $skipped++;
            } elseif ($r['status'] === 'valid') {
                $valid++;
            } else {
                $error++;
            }
        }

        $this->summary['valid'] = $valid;
        $this->summary['error'] = $error;
        $this->summary['skipped'] = $skipped;
    }

    public function render()
    {
        $allRows = $this->getPreviewRows();
        $totalPreview = count($allRows);
        $totalPages = (int) ceil($totalPreview / $this->perPage);
        $this->page = max(1, min($this->page, max(1, $totalPages)));

        $offset = ($this->page - 1) * $this->perPage;
        $paginatedRows = array_slice($allRows, $offset, $this->perPage, true);

        return view('livewire.equipment.import', [
            'paginatedRows' => $paginatedRows,
            'totalPages' => $totalPages,
            'totalPreview' => $totalPreview,
        ])->layout('components.layouts.app', ['title' => 'Nhập Excel Thiết bị - MEMS']);
    }
}
