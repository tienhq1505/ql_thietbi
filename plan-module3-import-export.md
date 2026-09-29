# Kế hoạch Triển khai Module 3 — Nhập/Xuất Dữ liệu (MEMS)

Tiếp nối Module 1 (Đăng nhập & Phân quyền) và Module 2 (Danh mục & Quản lý Thiết bị) đã hoàn thành. Module 3 tập trung vào: Import Excel 2 bước (preview trước khi lưu), Export Excel/CSV/PDF, và theo dõi lịch sử import (`import_batches`).

---

## Quyết định thiết kế đã chốt (để AI coding không tự suy đoán)

> [!IMPORTANT]
> 1. **Định dạng ngày trong file Excel: `dd/mm/yyyy`** (chuẩn Việt Nam), ví dụ `15/03/2024`.
>    - Hỗ trợ cả 2 kiểu ô trong Excel: Nếu ô lưu kiểu Date nhị phân (núm số `is_numeric($val)` của PhpSpreadsheet), dùng `\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)`. Nếu ô lưu kiểu Text, parse bằng `Carbon::createFromFormat('d/m/Y', trim($val))`. Sai định dạng → báo lỗi dòng đó.
> 2. **Cột danh mục trong Excel dùng CODE (Mã), không dùng ID số** (vì người dùng thực tế không biết ID nội bộ). Ví dụ cột "Mã model" ghi `PHIL-MX450`, hệ thống tự tra `equipment_models.code` để lấy `id`.
> 3. **Không tự động tạo mới danh mục khi import** (không tự tạo hãng/model/khoa mới nếu mã không tồn tại). Nếu mã model/khoa/NCC trong file không khớp bản ghi nào trong hệ thống → dòng đó bị đánh dấu lỗi "Mã X không tồn tại trong danh mục", người dùng phải tạo danh mục trước hoặc sửa lại file.
> 4. **`category_id` và `manufacturer_id` trên bảng `equipment` được tự động lấy từ `equipment_model_id`** khi import (không đọc trực tiếp từ 2 cột riêng trong Excel) — tránh trường hợp người nhập điền sai lệch giữa model và hãng/loại.
> 5. **Kiểm tra trùng mã/serial tính cả bản ghi đã soft-delete** (`Equipment::withTrashed()`) — vì mã thiết bị vật lý không được tái sử dụng dù thiết bị cũ đã "xóa" trên hệ thống.
> 6. **Dữ liệu preview giữ tạm trong Livewire component (biến public), chưa ghi DB cho đến khi bấm "Xác nhận Import"** — không tạo bản ghi tạm/trạng thái "pending" trong bảng `equipment`.
> 7. **Giới hạn số lượng dòng Import:** Tối đa **500 dòng/file** cho mỗi lần tải lên để đảm bảo hiệu năng Livewire và không làm tràn bộ nhớ server.
> 8. **Pre-loading Data trong Validator:** Pre-load danh mục (models, departments, suppliers, existing codes/serials) vào RAM trong `EquipmentImportValidator` trước khi loop kiểm tra để tránh N+1 Queries.
> 9. **Phân trang hiển thị Preview:** Hiển thị 20 dòng/trang trong bảng Preview Livewire để giao diện mượt mà.

---

## Danh sách Cấu chuẩn Cột File Excel Import

| STT | Tên Cột Excel (Heading) | Key trong Code (Snake Case) | Bắt buộc | Mô tả & Qui tắc Kiểm tra |
|---|---|---|---|---|
| 1 | `Mã thiết bị` | `ma_thiet_bi` | **Có** | Chuỗi duy nhất, không trùng DB (kể cả soft deleted) & không trùng trong cùng file. |
| 2 | `Tên thiết bị` | `ten_thiet_bi` | **Có** | Tên thiết bị y tế. |
| 3 | `Mã model` | `ma_model` | **Có** | Mã model thiết bị. Phải tồn tại trong `equipment_models.code`. Tự động gán `category_id` & `manufacturer_id`. |
| 4 | `Mã khoa phòng` | `ma_khoa_phong` | **Có** | Mã khoa phòng quản lý. Phải tồn tại trong `departments.code`. |
| 5 | `Mã vị trí` | `ma_vi_tri` | Không | Nếu điền, phải tồn tại trong `locations.code` và bắt buộc thuộc đúng khoa phòng đã chọn (`location.department_id == department.id`). |
| 6 | `Số serial` | `so_serial` | **Có** | Serial number thiết bị. Không trùng DB (kể cả soft deleted) & không trùng trong cùng file. |
| 7 | `Năm sản xuất` | `nam_san_xuat` | Không | Số nguyên 4 chữ số (VD: 2022). |
| 8 | `Xuất xứ` | `xuat_xu` | Không | Quốc gia sản xuất (VD: Mỹ, Đức, Nhật). |
| 9 | `Ngày nhập` | `ngay_nhap` | Không | Định dạng `dd/mm/yyyy` hoặc Excel Date. Bất kỳ định dạng chuỗi nào khác (như Y-m-d) đều báo lỗi. |
| 10 | `Ngày sử dụng` | `ngay_su_dung` | Không | Định dạng `dd/mm/yyyy` hoặc Excel Date. |
| 11 | `Giá mua` | `gia_mua` | Không | Số thực >= 0. |
| 12 | `Nguồn kinh phí` | `nguon_kinh_phi` | Không | Chuỗi (VD: Ngân sách nhà nước, Dữ án ODA...). |
| 13 | `Mã nhà cung cấp` | `ma_nha_cung_cap` | Không | Nếu điền, phải tồn tại trong `suppliers.code`. |
| 14 | `Hạn bảo hành` | `han_bao_hanh` | Không | Định dạng `dd/mm/yyyy` hoặc Excel Date. |
| 15 | `Người phụ trách` | `nguoi_phu_trach` | Không | Lưu tên tự do vào `manager_name`. |
| 16 | `Trạng thái` | `trang_thai` | Không | Để trống -> Mặc định `dang_su_dung`. Nếu nhập sai chính tả / không khớp enum -> Báo lỗi dòng đó. |
| 17 | `Tình trạng` | `tinh_trang` | Không | Để trống -> Mặc định `dang_hoat_dong`. Nếu nhập sai chính tả / không khớp enum -> Báo lỗi dòng đó. |
| 18 | `Ghi chú` | `ghi_chu` | Không | Chuỗi tự do. |


---

## 1. Database & Migrations

### [NEW] `create_import_batches_table`
```php
Schema::create('import_batches', function (Blueprint $table) {
    $table->id();
    $table->string('file_name');               // Tên file gốc người dùng upload
    $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
    $table->integer('total_rows')->default(0);
    $table->integer('success_rows')->default(0);
    $table->integer('failed_rows')->default(0);
    $table->json('error_details')->nullable();  // [{row: 5, errors: [...]}, ...]
    $table->string('status')->default('completed'); // 'completed' | 'completed_with_errors'
    $table->timestamps();                      // Đầy đủ created_at và updated_at
});
```

### [NEW] Migration bổ sung: `add_foreign_key_import_batch_id_to_equipment_table`
```php
Schema::table('equipment', function (Blueprint $table) {
    $table->foreign('import_batch_id')->references('id')->on('import_batches')->nullOnDelete();
});
```

### [MODIFY] `app/Policies/EquipmentPolicy.php`
Bổ sung 2 policy methods:
```php
public function import(User $user): bool
{
    return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.import');
}

public function export(User $user): bool
{
    return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->hasRole('directorate') || $user->can('equipment.export');
}
```

---

## 2. Backend Logic

### [NEW] `app/Services/EquipmentImportValidator.php`
Class thuần PHP (không phụ thuộc Livewire/HTTP), nhận mảng dòng từ Excel:
- Pre-load danh mục (Models, Departments, Suppliers, existing Equipment Codes/Serials).
- Validate từng dòng: bắt lỗi thiếu trường bắt buộc, sai định dạng date, mã không tồn tại, trùng code/serial trong DB hoặc trong chính file upload.
- Trả về mảng: `['rows' => [...], 'valid_count' => X, 'error_count' => Y]`.

### [NEW] `app/Imports/EquipmentSheetImport.php`
Class dùng `maatwebsite/excel`, implement `ToArray` + `WithHeadingRow`.

---

## 3. Livewire Component — Import 2 bước

### [NEW] `app/Livewire/Equipment/EquipmentImport.php`
- `uploadAndPreview()`: Validate file Excel (xlsx/xls, max 5MB, max 500 rows). Chạy `EquipmentImportValidator` và lưu vào `$previewRows`.
- `downloadSample()`: Tải file `sample_import.xlsx`.
- `confirmImport()`:
  - DB Transaction:
    1. Tạo `ImportBatch`
    2. Duyệt các dòng `status === 'valid'` và không skip -> `Equipment::create([...], 'import_batch_id' => $batch->id)`
    3. Cập nhật `total_rows`, `success_rows`, `failed_rows`, `error_details`, `status` cho `ImportBatch`
    4. Hiển thị thông báo hoàn tất.

### [NEW] `app/Livewire/ImportBatches/ImportBatchIndex.php`
Danh sách lịch sử import — Admin & Phòng Vật tư: tên file, người thực hiện, thời gian, kết quả, modal xem chi tiết lỗi JSON.

---

## 4. Export Excel / CSV / PDF

### [MODIFY] `app/Livewire/Equipment/EquipmentIndex.php`
- Tách method private `buildFilteredQuery()` bảo tồn đầy đủ logic phân quyền khoa phòng (`using_dept`).
- Thêm action methods:
  - `exportExcel()`: `Excel::download(new EquipmentExport($this->buildFilteredQuery()), 'danh-sach-thiet-bi.xlsx')`
  - `exportCsv()`: `Excel::download(new EquipmentExport($this->buildFilteredQuery()), 'danh-sach-thiet-bi.csv', \Maatwebsite\Excel\Excel::CSV, ['Content-Type' => 'text/csv; charset=UTF-8'])`
  - `exportPdf()`: Giới hạn tối đa **200 thiết bị** để tránh crash DomPDF memory. Trả về stream PDF.

### [NEW] `app/Exports/EquipmentExport.php`
`FromQuery`, `WithHeadings`, `WithMapping`, `WithCustomCsvSettings` (thêm UTF-8 BOM).

### [NEW] PDF Templates (`resources/views/pdf/`)
- `equipment_list.blade.php`: Cấu hình font `DejaVu Sans` hiển thị tiếng Việt chuẩn.
- `equipment_profile.blade.php`: Hồ sơ 1 thiết bị, có nút xuất trên `EquipmentShow`.

---

## 5. File Excel mẫu & UI Navigation

### [NEW] Artisan Command: `app/Console/Commands/GenerateSampleImportFile.php`
- Lấy mã thật từ DB seeder để sinh file `storage/app/samples/sample_import.xlsx`.
- 6 dòng hợp lệ + 4 dòng cố tình tạo lỗi để test preview (thiếu mã, trùng serial, sai ngày, sai mã model).

### [MODIFY] `resources/views/components/layouts/app.blade.php` (Sidebar Menu)
- Thêm mục "Nhập Excel Thiết bị" (`/equipment/import`) bọc bởi `@can('equipment.import')`.
- Thêm mục "Lịch sử Import" (`/import-batches`) bọc bởi `@can('equipment.import')`.

### [MODIFY] `resources/views/livewire/equipment/index.blade.php` (Toolbar)
- Thêm nút "Nhập Excel" dẫn tới route `equipment.import`.
- Thêm Dropdown xuất dữ liệu: "Xuất Excel", "Xuất CSV", "Xuất PDF".

---

## 6. Routes

```php
Route::middleware(['auth', 'permission:equipment.import'])->group(function () {
    Route::get('/equipment/import', EquipmentImport::class)->name('equipment.import');
    Route::get('/import-batches', ImportBatchIndex::class)->name('import-batches.index');
});
```

---

## 7. Cấu trúc file bổ sung

```
app/
├── Console/Commands/GenerateSampleImportFile.php
├── Services/EquipmentImportValidator.php
├── Imports/EquipmentSheetImport.php
├── Exports/EquipmentExport.php
├── Livewire/
│   ├── Equipment/EquipmentImport.php
│   └── ImportBatches/ImportBatchIndex.php
├── Models/ImportBatch.php
database/migrations/
├── xxxx_create_import_batches_table.php
├── xxxx_add_foreign_key_import_batch_id_to_equipment_table.php
resources/views/
├── livewire/equipment/import.blade.php
├── livewire/import-batches/index.blade.php
├── pdf/equipment_list.blade.php
├── pdf/equipment_profile.blade.php
storage/app/samples/sample_import.xlsx
tests/
├── Unit/EquipmentImportValidatorTest.php
├── Feature/EquipmentImportTest.php
```

---

## 8. Test Plan

### Unit Test — `EquipmentImportValidatorTest.php`
Test riêng validator: dòng hợp lệ, thiếu mã, trùng serial trong file/DB, sai định dạng ngày (text & excel date), mã model không tồn tại.

### Feature Test — `EquipmentImportTest.php`
1. Admin/Vật tư import file hợp lệ -> thành công 100%, ghi `import_batches`.
2. Import file mẫu có lỗi -> preview hiển thị đúng số dòng lỗi, confirm chỉ lưu dòng hợp lệ.
3. Kỹ thuật viên / Khoa sử dụng truy cập `/equipment/import` -> 403.
4. Export Excel/CSV/PDF hoạt động và tôn trọng bộ lọc phân quyền khoa.

---

## 9. Definition of Done
- [x] Đã cập nhật file kế hoạch với toàn bộ điều chỉnh chi tiết.
- [ ] Artisan command sinh file mẫu thành công.
- [ ] Migrations & Models hoàn thành (`ImportBatch` + FK).
- [ ] Policy & Permissions hỗ trợ `import` và `export`.
- [ ] Import 2 bước & Export Excel/CSV/PDF hoạt động ổn định.
- [ ] Toàn bộ Unit Test và Feature Test pass.
