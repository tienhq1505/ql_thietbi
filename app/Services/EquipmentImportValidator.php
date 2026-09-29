<?php

namespace App\Services;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Supplier;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EquipmentImportValidator
{
    public function validateRows(array $rawRows): array
    {
        // 1. Pre-load reference maps for performance (O(1) lookups, 0 N+1 queries)
        $models = EquipmentModel::all()->keyBy(fn ($m) => mb_strtolower($m->code));
        $departments = Department::all()->keyBy(fn ($d) => mb_strtolower($d->code));
        $locations = Location::all()->keyBy(fn ($l) => mb_strtolower($l->code));
        $suppliers = Supplier::all()->keyBy(fn ($s) => mb_strtolower($s->code));

        $existingCodes = Equipment::withTrashed()
            ->whereNotNull('code')
            ->pluck('code')
            ->map(fn ($c) => mb_strtolower($c))
            ->flip()
            ->toArray();

        $existingSerials = Equipment::withTrashed()
            ->whereNotNull('serial_number')
            ->pluck('serial_number')
            ->map(fn ($s) => mb_strtolower($s))
            ->flip()
            ->toArray();

        // Track codes/serials within the current file to catch duplicates in same upload
        $seenFileCodes = [];
        $seenFileSerials = [];

        $validatedRows = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $row) {
            $rowNum = $index + 2; // Data rows start at row 2 in Excel
            $errors = [];

            // Extract & normalize fields from row (supporting variations in header names)
            $code = trim((string) ($row['ma_thiet_bi'] ?? $row['code'] ?? ''));
            $name = trim((string) ($row['ten_thiet_bi'] ?? $row['name'] ?? ''));
            $modelCode = trim((string) ($row['ma_model'] ?? $row['model_code'] ?? ''));
            $deptCode = trim((string) ($row['ma_khoa_phong'] ?? $row['ma_khoa'] ?? $row['department_code'] ?? ''));
            $locationCode = trim((string) ($row['ma_vi_tri'] ?? $row['ma_vi_tri_lap_dat'] ?? $row['vi_tri'] ?? $row['location_code'] ?? ''));
            $serial = trim((string) ($row['so_serial'] ?? $row['serial_number'] ?? ''));
            $manufactureYearRaw = $row['nam_san_xuat'] ?? $row['manufacture_year'] ?? null;
            $originCountry = trim((string) ($row['xuat_xu'] ?? $row['origin_country'] ?? ''));
            $importDateRaw = $row['ngay_nhap'] ?? $row['import_date'] ?? null;
            $usageDateRaw = $row['ngay_su_dung'] ?? $row['usage_start_date'] ?? null;
            $priceRaw = $row['gia_mua'] ?? $row['don_gia'] ?? $row['purchase_price'] ?? null;
            $fundingSource = trim((string) ($row['nguon_kinh_phi'] ?? $row['funding_source'] ?? ''));
            $supplierCode = trim((string) ($row['ma_nha_cung_cap'] ?? $row['supplier_code'] ?? ''));
            $warrantyDateRaw = $row['han_bao_hanh'] ?? $row['warranty_expiration_date'] ?? null;
            $managerName = trim((string) ($row['nguoi_phu_trach'] ?? $row['manager_name'] ?? ''));
            $statusRaw = trim((string) ($row['trang_thai'] ?? $row['status'] ?? ''));
            $conditionRaw = trim((string) ($row['tinh_trang'] ?? $row['condition'] ?? ''));
            $ownershipTypeRaw = trim((string) ($row['loai_so_huu'] ?? $row['ownership_type'] ?? ''));
            $originModelText = trim((string) ($row['nguyen_van_model'] ?? $row['origin_model_text'] ?? ''));
            $sourceRef = trim((string) ($row['ma_dieu_chuyen_goc'] ?? $row['source_ref'] ?? ''));
            $note = trim((string) ($row['ghi_chu'] ?? $row['note'] ?? ''));

            // --- Validation 1: Mã thiết bị (Code) ---
            if (empty($code)) {
                $errors[] = 'Mã thiết bị không được để trống.';
            } else {
                $codeLower = mb_strtolower($code);
                if (isset($existingCodes[$codeLower])) {
                    $errors[] = "Mã thiết bị '{$code}' đã tồn tại trong hệ thống (kể cả đã xóa).";
                } elseif (isset($seenFileCodes[$codeLower])) {
                    $errors[] = "Mã thiết bị '{$code}' bị trùng lặp nhiều lần trong file.";
                } else {
                    $seenFileCodes[$codeLower] = true;
                }
            }

            // --- Validation 2: Tên thiết bị (Name) ---
            if (empty($name)) {
                $errors[] = 'Tên thiết bị không được để trống.';
            }

            // --- Validation 3: Mã model ---
            $modelObj = null;
            if (empty($modelCode)) {
                $errors[] = 'Mã model không được để trống.';
            } else {
                $modelLower = mb_strtolower($modelCode);
                if (! isset($models[$modelLower])) {
                    $errors[] = "Mã model '{$modelCode}' không tồn tại trong danh mục hệ thống.";
                } else {
                    $modelObj = $models[$modelLower];
                }
            }

            // --- Validation 4: Mã khoa phòng ---
            $deptObj = null;
            if (empty($deptCode)) {
                $errors[] = 'Mã khoa phòng không được để trống.';
            } else {
                $deptLower = mb_strtolower($deptCode);
                if (! isset($departments[$deptLower])) {
                    $errors[] = "Mã khoa phòng '{$deptCode}' không tồn tại trong danh mục hệ thống.";
                } else {
                    $deptObj = $departments[$deptLower];
                }
            }

            // --- Validation 5: Mã vị trí lắp đặt (Optional, nhưng nếu có phải thuộc khoa đã chọn) ---
            $locationObj = null;
            if (! empty($locationCode)) {
                $locLower = mb_strtolower($locationCode);
                if (! isset($locations[$locLower])) {
                    $errors[] = "Mã vị trí '{$locationCode}' không tồn tại trong danh mục hệ thống.";
                } else {
                    $locationObj = $locations[$locLower];
                    if ($deptObj && (int) $locationObj->department_id !== (int) $deptObj->id) {
                        $errors[] = "Vị trí '{$locationCode}' không thuộc khoa phòng '{$deptCode}'.";
                    }
                }
            }

            // --- Validation 6: Số serial (Không bắt buộc) ---
            $serialVal = null;
            $serialPending = false;
            if (empty($serial)) {
                $serialPending = true;
            } else {
                $serialVal = $serial;
                $serialLower = mb_strtolower($serial);
                if (isset($existingSerials[$serialLower])) {
                    $errors[] = "Số serial '{$serial}' đã tồn tại trong hệ thống (kể cả đã xóa).";
                } elseif (isset($seenFileSerials[$serialLower])) {
                    $errors[] = "Số serial '{$serial}' bị trùng lặp nhiều lần trong file.";
                } else {
                    $seenFileSerials[$serialLower] = true;
                }
            }

            // --- Validation 7: Nhà cung cấp (Optional) ---
            $supplierObj = null;
            if (! empty($supplierCode)) {
                $supplierLower = mb_strtolower($supplierCode);
                if (! isset($suppliers[$supplierLower])) {
                    $errors[] = "Mã nhà cung cấp '{$supplierCode}' không tồn tại trong danh mục hệ thống.";
                } else {
                    $supplierObj = $suppliers[$supplierLower];
                }
            }

            // --- Validation 8: Date parsing (Import date, Usage date, Warranty date) ---
            $importDate = $this->parseDate($importDateRaw, 'Ngày nhập', $errors);
            $usageDate = $this->parseDate($usageDateRaw, 'Ngày sử dụng', $errors);
            $warrantyDate = $this->parseDate($warrantyDateRaw, 'Hạn bảo hành', $errors);

            // --- Validation 9: Năm sản xuất & Giá mua ---
            $manufactureYear = null;
            if ($manufactureYearRaw !== null && $manufactureYearRaw !== '') {
                if (! is_numeric($manufactureYearRaw) || (int) $manufactureYearRaw < 1900 || (int) $manufactureYearRaw > ((int) date('Y') + 1)) {
                    $errors[] = "Năm sản xuất '{$manufactureYearRaw}' không hợp lệ.";
                } else {
                    $manufactureYear = (int) $manufactureYearRaw;
                }
            }

            $purchasePrice = 0.0;
            if ($priceRaw !== null && $priceRaw !== '') {
                $purchasePrice = $this->parseVietnamesePrice((string) $priceRaw, $errors);
            }

            // --- Validation 10: Status, Condition, Ownership Type ---
            $status = $this->parseStatus($statusRaw, $errors);
            $condition = $this->parseCondition($conditionRaw, $errors);
            $ownershipType = $this->parseOwnershipType($ownershipTypeRaw, $errors);

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $validatedRows[] = [
                'row' => $rowNum,
                'status' => $isValid ? 'valid' : 'error',
                'errors' => $errors,
                'skip' => ! $isValid,
                'display' => [
                    'code' => $code,
                    'name' => $name,
                    'model_code' => $modelCode,
                    'dept_code' => $deptCode,
                    'location_code' => $locationCode,
                    'serial_number' => $serialVal ?? 'Chưa có serial',
                    'supplier_code' => $supplierCode,
                    'import_date' => $importDate,
                    'status_label' => EquipmentStatusEnum::tryFrom($status)?->label() ?? $status,
                    'condition_label' => EquipmentConditionEnum::tryFrom($condition)?->label() ?? $condition,
                    'ownership_type_label' => OwnershipTypeEnum::tryFrom($ownershipType)?->label() ?? $ownershipType,
                ],
                'data' => [
                    'code' => $code,
                    'name' => $name,
                    'equipment_model_id' => $modelObj?->id,
                    'category_id' => $modelObj?->category_id,
                    'manufacturer_id' => $modelObj?->manufacturer_id,
                    'department_id' => $deptObj?->id,
                    'location_id' => $locationObj?->id,
                    'supplier_id' => $supplierObj?->id,
                    'serial_number' => $serialVal,
                    'serial_pending' => $serialPending,
                    'manufacture_year' => $manufactureYear,
                    'origin_country' => $originCountry ?: null,
                    'import_date' => $importDate,
                    'usage_start_date' => $usageDate,
                    'purchase_price' => $purchasePrice,
                    'funding_source' => $fundingSource ?: null,
                    'warranty_expiration_date' => $warrantyDate,
                    'manager_name' => $managerName ?: null,
                    'status' => $status,
                    'condition' => $condition,
                    'ownership_type' => $ownershipType,
                    'origin_model_text' => $originModelText ?: null,
                    'source_ref' => $sourceRef ?: null,
                    'note' => $note ?: null,
                ],
            ];
        }

        return [
            'rows' => $validatedRows,
            'total_count' => count($rawRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
        ];
    }

    /**
     * Parse giá theo định dạng Việt Nam:
     * - Dấu chấm ngăn nghìn, dấu phẩy thập phân (VD: 1.500.000 hoặc 1.500,50)
     * - Dấu phẩy ngăn nghìn, dấu chấm thập phân (format quốc tế, VD: 1,500,000 hoặc 1,500.50)
     * - Thuần số không có phân cách (VD: 1500000)
     * Báo lỗi rõ khi mơ hồ: vừa có chấm vừa có phẩy không xác định được format.
     */
    private function parseVietnamesePrice(string $raw, array &$errors): float
    {
        $s = trim($raw);
        if ($s === '' || $s === '0') {
            return 0.0;
        }

        // Loại bỏ khoảng trắng và ký hiệu tiền tệ
        $s = preg_replace('/(?:\s|\xc2\xa0|[$€£₫]|vnđ|vnd|đ)/iu', '', $s);

        $hasComma = str_contains($s, ',');
        $hasDot = str_contains($s, '.');

        if (! $hasComma && ! $hasDot) {
            // Thuần số
            if (! is_numeric($s) || (float) $s < 0) {
                $errors[] = "Giá mua '{$raw}' không hợp lệ.";

                return 0.0;
            }

            return (float) $s;
        }

        // Chỉ có chấm: có thể là ngăn nghìn VN (1.500.000) hoặc thập phân quốc tế (1.5)
        if ($hasDot && ! $hasComma) {
            $dotCount = substr_count($s, '.');
            if ($dotCount > 1) {
                // Nhiều dấu chấm ⇒ ngăn nghìn VN
                $normalized = str_replace('.', '', $s);
            } else {
                // Một dấu chấm: kiểm tra phần sau chấm
                $afterDot = substr($s, strrpos($s, '.') + 1);
                if (strlen($afterDot) === 3) {
                    // Có thể là ngăn nghìn (1.500) hoặc thập phân 3 chữ số (1.500 = 1.5?)
                    // Ưu tiên: nếu số to (>= 1000 khi bỏ dấu chấm) thì là ngăn nghìn
                    $asWhole = str_replace('.', '', $s);
                    if ((float) $asWhole >= 1000) {
                        $normalized = $asWhole;
                    } else {
                        // Giữ nguyên là số thập phân
                        $normalized = $s;
                    }
                } else {
                    // Thập phân bình thường
                    $normalized = $s;
                }
            }
            if (! is_numeric($normalized) || (float) $normalized < 0) {
                $errors[] = "Giá mua '{$raw}' không hợp lệ.";

                return 0.0;
            }

            return (float) $normalized;
        }

        // Chỉ có phẩy: có thể là ngăn nghìn quốc tế (1,500,000) hoặc thập phân VN (1,5)
        if ($hasComma && ! $hasDot) {
            $commaCount = substr_count($s, ',');
            if ($commaCount > 1) {
                // Nhiều dấu phẩy ⇒ ngăn nghìn quốc tế
                $normalized = str_replace(',', '', $s);
            } else {
                // Một dấu phẩy: kiểm tra phần sau phẩy
                $afterComma = substr($s, strrpos($s, ',') + 1);
                if (strlen($afterComma) === 3) {
                    $asWhole = str_replace(',', '', $s);
                    if ((float) $asWhole >= 1000) {
                        $normalized = $asWhole;
                    } else {
                        $normalized = str_replace(',', '.', $s); // Thập phân
                    }
                } else {
                    // Thập phân VN: 1,5 ⇒ 1.5
                    $normalized = str_replace(',', '.', $s);
                }
            }
            if (! is_numeric($normalized) || (float) $normalized < 0) {
                $errors[] = "Giá mua '{$raw}' không hợp lệ.";

                return 0.0;
            }

            return (float) $normalized;
        }

        // Có cả chấm và phẩy: xác định dựa trên vị trí cuối cùng
        $lastCommaPos = strrpos($s, ',');
        $lastDotPos = strrpos($s, '.');

        if ($lastCommaPos > $lastDotPos) {
            // Phẩy ở sau cùng ⇒ VN format: chấm ngăn nghìn, phẩy thập phân
            $normalized = str_replace('.', '', $s);          // bỏ ngăn nghìn
            $normalized = str_replace(',', '.', $normalized); // phẩy thành dấu chấm
        } else {
            // Chấm ở sau cùng ⇒ quốc tế: phẩy ngăn nghìn, chấm thập phân
            $normalized = str_replace(',', '', $s); // bỏ ngăn nghìn
        }

        if (! is_numeric($normalized) || (float) $normalized < 0) {
            $errors[] = "Giá mua '{$raw}' không hợp lệ.";

            return 0.0;
        }

        return (float) $normalized;
    }

    private function parseDate($value, string $fieldName, array &$errors): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);

                return $dt->format('Y-m-d');
            }

            $str = trim((string) $value);
            if (empty($str)) {
                return null;
            }

            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $str)) {
                return Carbon::createFromFormat('d/m/Y', $str)->format('Y-m-d');
            }

            $errors[] = "{$fieldName} '{$str}' không đúng định dạng chuẩn (dd/mm/yyyy).";

            return null;
        } catch (\Throwable $e) {
            $errors[] = "{$fieldName} '{$value}' không thể đọc ngày tháng.";

            return null;
        }
    }

    private function parseStatus(string $raw, array &$errors): string
    {
        if (empty($raw)) {
            return EquipmentStatusEnum::DANG_SU_DUNG->value;
        }

        $clean = mb_strtolower(trim($raw));

        foreach (EquipmentStatusEnum::cases() as $case) {
            if ($case->value === $clean || mb_strtolower($case->label()) === $clean) {
                return $case->value;
            }
        }

        $validOptions = implode(', ', array_map(fn ($c) => "'{$c->label()}'", EquipmentStatusEnum::cases()));
        $errors[] = "Trạng thái '{$raw}' không hợp lệ. Các giá trị được chấp nhận: {$validOptions}.";

        return EquipmentStatusEnum::DANG_SU_DUNG->value;
    }

    private function parseCondition(string $raw, array &$errors): string
    {
        if (empty($raw)) {
            return EquipmentConditionEnum::DANG_HOAT_DONG->value;
        }

        $clean = mb_strtolower(trim($raw));

        foreach (EquipmentConditionEnum::cases() as $case) {
            if ($case->value === $clean || mb_strtolower($case->label()) === $clean) {
                return $case->value;
            }
        }

        $validOptions = implode(', ', array_map(fn ($c) => "'{$c->label()}'", EquipmentConditionEnum::cases()));
        $errors[] = "Tình trạng '{$raw}' không hợp lệ. Các giá trị được chấp nhận: {$validOptions}.";

        return EquipmentConditionEnum::DANG_HOAT_DONG->value;
    }

    private function parseOwnershipType(string $raw, array &$errors): string
    {
        if (empty($raw)) {
            return OwnershipTypeEnum::MUA_SAM->value;
        }

        $clean = mb_strtolower(trim($raw));

        foreach (OwnershipTypeEnum::cases() as $case) {
            if ($case->value === $clean || mb_strtolower($case->label()) === $clean) {
                return $case->value;
            }
        }

        $validOptions = implode(', ', array_map(fn ($c) => "'{$c->label()}'", OwnershipTypeEnum::cases()));
        $errors[] = "Loại sở hữu '{$raw}' không hợp lệ. Các giá trị được chấp nhận: {$validOptions}.";

        return OwnershipTypeEnum::MUA_SAM->value;
    }
}
