# BẢN HƯỚNG DẪN DỰ ÁN CHO CLAUDE (CLAUDE PROJECT KNOWLEDGE)
> **Tài liệu dành cho Claude.ai / Claude Code**: Đọc tài liệu này để hiểu toàn bộ bối cảnh dự án, kiến trúc kỹ thuật, tiến độ hiện tại, quy tắc bảo mật và cách tiếp tục code mà không cần hỏi lại người dùng.

---

## 1. TỔNG QUAN DỰ ÁN (EXECUTIVE SUMMARY)

- **Tên dự án:** MEMS (Medical Equipment Management System) — Hệ thống Quản lý Trang thiết bị Y tế Bệnh viện.
- **Quy mô nghiệp vụ:** Dành cho bệnh viện ~200 giường bệnh, quản lý ~286 thiết bị y tế (dữ liệu theo Phụ lục 2 thực tế) phân bổ vào 16 khoa/phòng lâm sàng và 3 đơn vị hệ thống.
- **Giai đoạn hiện tại:** **Phase 1 (MVP)** — Chạy trên môi trường local (SQLite), tập trung vào quản lý danh mục, phân quyền nghiêm ngặt theo khoa, theo dõi 15 loại hồ sơ tài liệu pháp lý, lịch sử tình trạng thiết bị, nhập/xuất dữ liệu, kiểm kê QR code và audit log.
- **Các phase tương lai (Phase 2–4, TUYỆT ĐỐI CHƯA LÀM Ở PHASE 1):**
  - Không làm quy trình bảo trì / sửa chữa / thay thế linh kiện chuyên sâu.
  - Không làm kiểm định / hiệu chuẩn nâng cao.
  - Không làm kho vật tư tiêu hao, mua sắm đấu thầu, kế toán viện phí.
  - Không tích hợp IoT / AI dự đoán hỏng hóc.

---

## 2. TECH STACK & MÔI TRƯỜNG MÁY CHỦ LOCAL

| Thành phần | Phiên bản & Công nghệ | Ghi chú quan trọng |
|---|---|---|
| **OS** | Windows 10/11 x64 | Dùng PowerShell / CMD |
| **PHP** | **PHP 8.3.33** tại `C:\php8\php.exe` | Đầy đủ `pdo_sqlite`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo` |
| **Composer** | **Composer 2.11.2** tại `C:\php8\composer.bat` | Sử dụng `C:\php8\composer.bat` khi chạy lệnh Composer |
| **Framework** | **Laravel 13.x** | Đã cài `laravel/boost`, `spatie/laravel-permission 8.x` |
| **Frontend** | **Livewire 4 + Alpine.js + Tailwind CSS v4** | Hạn chế tối đa viết JS tùy biến; assets đã build tại `public/build` |
| **Database** | **SQLite** (`database/database.sqlite`) | Test chạy trên SQLite in-memory (`:memory:`) độc lập |
| **Excel** | `maatwebsite/excel 4.x` (PhpSpreadsheet) | Đã tối ưu pre-loading chống N+1 |
| **PDF** | `barryvdh/laravel-dompdf 3.x` | Dùng font `DejaVu Sans` cho tiếng Việt |
| **QR Code** | `simplesoftwareio/simple-qrcode 4.x` | Dùng cho nhãn kiểm kê thiết bị |

### Các lệnh môi trường chuẩn (dùng đường dẫn PHP đầy đủ):
```powershell
# Chạy migration & seed dữ liệu nền
C:\php8\php.exe artisan migrate:fresh --seed

# Chạy test suite (toàn bộ test phải PASS 100%)
C:\php8\php.exe vendor/phpunit/phpunit/phpunit
# hoặc: C:\php8\php.exe artisan test (hiện tại PASS 100% 196/196 tests)

# Khởi chạy server demo
C:\php8\php.exe artisan serve
```

---

## 3. MA TRẬN PHÂN QUYỀN (5 VAI TRÒ BẮT BUỘC)

Hệ thống dùng `spatie/laravel-permission` kết hợp middleware `CheckDepartmentScope` và `EquipmentPolicy`. Mật khẩu mặc định cho toàn bộ tài khoản demo là: `password`.

| Vai trò (Role) | Email tài khoản | Scope dữ liệu | Quyền hạn nghiệp vụ |
|---|---|---|---|
| **Quản trị hệ thống** (`admin`) | `admin@mems.local` | Toàn viện | Toàn quyền cấu hình, xem/sửa/soft-delete thiết bị, import/export, quản lý người dùng & phân quyền (`/users`). |
| **Phòng Vật tư-TBYT** (`equipment_dept`) | `vattu@mems.local` | Toàn viện | Quản lý danh mục, tạo/sửa thiết bị, import/export, đề nghị xóa (`delete_requested_at`), quản lý hồ sơ. **Không được xóa thật (soft-delete).** |
| **Kỹ thuật viên** (`technician`) | `kythuat@mems.local` | Toàn viện | Xem toàn bộ thiết bị, **chỉ cập nhật tình trạng kỹ thuật** (ghi log lịch sử), tham gia kiểm kê (dự kiến — Module 6 chưa làm; quyền `inventory.manage` đã seed). |
| **Khoa sử dụng** (`using_dept`) | `khoa@mems.local` (Khoa Nội) | **Chỉ thiết bị khoa mình** | Xem danh sách thiết bị khoa mình, chỉ được **đề nghị xóa** thiết bị khoa mình, **không có quyền sửa** (`EquipmentPolicy::update` chỉ cho admin / equipment_dept). **Cố tình truy cập URL thiết bị khoa khác phải trả về 403 Forbidden.** |
| **Ban Giám đốc** (`directorate`) | `giamdoc@mems.local` | Toàn viện | **Read-only**: Xem dashboard, xem hồ sơ, xuất báo cáo PDF/Excel toàn viện. Không có nút sửa/xóa/đổi trạng thái. |

---

## 4. CƠ CẤU DỮ LIỆU & SCHEMA DATABASE

### A. Phân định khái niệm cốt lõi:
- **`EquipmentModel`**: Khái niệm dòng máy/model (ví dụ: *Philips IntelliVue MX450*, *Siemens Multix Impact*).
- **`Equipment` (Asset)**: Từng chiếc máy vật lý cụ thể, có số Serial Number, mã quản lý riêng (`TB-xxx-yy`), gắn vào một Khoa và Vị trí cụ thể.
- **`EquipmentDocument`**: Lưu vết hồ sơ giấy tờ pháp lý của từng máy (15 loại tài liệu, theo dõi trạng thái `Có` / `Không` / `Không áp dụng` / `Chưa rà soát`).
- **`EquipmentConditionHistory`**: Bắt buộc ghi log mỗi khi thay đổi tình trạng kỹ thuật (ai đổi, từ gì sang gì, lý do, thời gian). Không bao giờ cập nhật cột `condition` mà không lưu log lịch sử.

### B. Các Enum chuẩn trong Code:
1. **`EquipmentConditionEnum`** (Tình trạng kỹ thuật):
   - `dang_hoat_dong` (Đang hoạt động)
   - `can_theo_doi` (Cần theo dõi)
   - `can_bao_tri` (Cần bảo trì)
   - `hong` (Hỏng)
   - `ngung_su_dung` (Ngừng sử dụng)
2. **`EquipmentStatusEnum`** (Trạng thái quản lý):
   - `dang_su_dung` (Đang sử dụng)
   - `du_phong` (Dự phòng)
   - `thanh_ly` (Đề nghị thanh lý)
   - `da_thanh_ly` (Đã thanh lý)
3. **`OwnershipTypeEnum`** (Hình thức sở hữu):
   - `mua_sam` (Mua sắm ngân sách/bệnh viện)
   - `may_dat` (Máy đặt từ nhà cung cấp hóa chất/vật tư)
   - `muon` (Mượn)
   - `tai_tro` (Tài trợ/Viện trợ)
4. **`DocumentTypeEnum`** (15 loại hồ sơ):
   - `hop_dong_hoa_don`, `bien_ban_ban_giao`, `ho_so_luu_hanh`, `co`, `cq`, `to_khai_hai_quan`, `tai_lieu_ky_thuat_hdsd`, `ho_so_cong_bo_dieu_kien_mua_ban`, `ly_lich`, `nhat_ky_su_dung`, `quy_trinh_van_hanh`, `ho_so_bao_duong`, `ho_so_dieu_chuyen_thanh_ly`, `kiem_dinh`, `hieu_chuan`.
5. **`DocumentStatusEnum`**:
   - `co` (Có)
   - `khong` (Không)
   - `khong_ap_dung` (Không áp dụng — ví dụ máy đặt không có hợp đồng mua sắm)
   - `chua_ra_soat` (Chưa rà soát)

---

## 5. TIẾN ĐỘ THỰC HIỆN & CÔNG VIỆC CẦN TIẾP TỤC

### Đã hoàn thành (100% Pass 177/177 Tests):
- [x] **Module 1**: Đăng nhập, phân quyền 5 roles, department scope.
- [x] **Module 2**: Danh mục Master Data (Category, Manufacturer, Model, Supplier, Location) + CRUD Equipment.
- [x] **Module 3**: Nhập/Xuất Excel 2 bước (Preview validate lỗi trước khi lưu) + Lịch sử `import_batches` + Xuất PDF/Excel/CSV.
- [x] **Mở rộng Nghiệp vụ Bệnh viện thực tế**:
  - Command `equipment:import-pl2` (Dry-run & Commit dữ liệu thật từ Phụ lục 2).
  - Quản lý 15 loại hồ sơ giấy tờ kèm máy (`EquipmentDocument`).
  - Giao diện gán khoa hàng loạt cho thiết bị chưa phân khoa (`/equipment/unassigned`).
  - Báo cáo đối soát KQRS (`equipment:reconcile-kqrs`).
  - Báo cáo tổng hợp tình trạng hồ sơ toàn viện (`/reports/documents`).
- [x] **Module 7: Audit Log & Bảo Mật Hệ Thống**:
  - Bảng `audit_logs` cơ chế **append-only** (Model Exception + SQLite Triggers chặn UPDATE/DELETE).
  - Observer `AuditableObserver` tự động ghi nhận `created`, `updated`, `deleted` cho Equipment, Document, Condition, User, Department, Master Data. Tuyệt đối không lưu `password`, `remember_token`.
  - Giao diện Livewire `/audit-logs` (lọc theo người, hành động, đối tượng, khoảng ngày, phân trang, modal so sánh thay đổi).
  - Artisan command `db:backup` (sao lưu SQLite kèm timestamp, giữ N bản mới nhất).
  - Đã mở menu Audit Log trên sidebar cho vai trò có quyền (`admin`, `equipment_dept`, `directorate`).
- [x] **Module 6a: Kỳ kiểm kê và Quét mã**:
  - Migration mới `inventory_sessions` và `inventory_items` với enums `InventorySessionStatusEnum` & `InventoryItemResultEnum`.
  - Tạo kỳ kiểm kê (khoa hoặc toàn viện), sinh snapshot thiết bị tự động (loại trừ thiết bị `da_thanh_ly`).
  - Quét mã QR qua Camera (thư viện `html5-qrcode` tích hợp Vite, debounce 3s, cleanup khi rời trang) kèm ô nhập mã thủ công.
  - Xử lý quét: Khớp (`khop`), Thừa (`thua`), Báo hỏng (`hong`), Quét trùng (báo cảnh báo, không nhân bản dòng), Mã sai hoặc thiết bị ngoài phạm vi khoa (báo lỗi bảo mật không lộ thông tin).
  - Tách `EquipmentConditionService` dùng chung. User có quyền `updateCondition` mới đổi tình trạng thiết bị; user `using_dept` chỉ đánh dấu hỏng trên biên bản kiểm kê, không đổi tình trạng máy; máy đã hỏng không ghi log trùng.
  - Nút "Chốt kỳ": chuyển toàn bộ mục chưa kiểm thành `thieu`, khóa kỳ kiểm kê cả phía server-side (chặn scan/sửa với mã 403).
  - Đăng ký `InventorySession` & `InventoryItem` vào `AuditableObserver`.
  - Đã mở menu **Kiểm kê & QR Code** (`/inventory`) trên sidebar.
- [x] **Module 6b: In nhãn QR Code thiết bị**:
  - Controller `EquipmentLabelController` hỗ trợ: in nhãn đơn lẻ (`/equipment/{equipment}/label`), in nhãn theo kỳ kiểm kê (`/inventory/{session}/labels`), in nhãn theo khoa (`/departments/{department}/equipment-labels`).
  - Nội dung mã QR chỉ là mã quản lý thiết bị (`code`), xuất SVG vector (loại bỏ thẻ `<?xml` thừa).
  - Giao diện in A4 dạng lưới nhãn, layout `components.layouts.print`, CSS `@media print` ẩn hoàn toàn toolbar.
  - Phân quyền nghiêm ngặt theo `EquipmentPolicy::view`: BGĐ in đơn lẻ/khoa 200 nhưng in theo kỳ kiểm kê 403; Khoa sử dụng chỉ in thiết bị khoa mình (403 khoa khác & kỳ toàn viện); User không khoa 403; loại bỏ thiết bị `da_thanh_ly` và item thừa khoa khác.
  - Đã tích hợp nút "In nhãn QR" tại trang chi tiết thiết bị, trang kỳ kiểm kê và trang danh sách thiết bị khi lọc theo khoa.
- [x] **Module 6c: Báo cáo đối soát và biên bản kiểm kê**:
  - Giao diện Livewire báo cáo đối soát (`/inventory/{session}/report`): Thống kê đối soát 6 chỉ số (Tổng, Khớp, Thiếu, Thừa, Hỏng, Chưa kiểm), tiến độ %, bảng chi tiết phân trang 20 dòng, bộ lọc kết quả (tất cả, chênh lệch, từng loại kết quả, chưa kiểm) và tìm kiếm (mã, tên, serial).
  - Xuất biên bản kiểm kê PDF (`/inventory/{session}/export-pdf`) bằng `barryvdh/laravel-dompdf`, font `DejaVu Sans` hiển thị tiếng Việt có dấu chuẩn; gồm tiêu đề, đơn vị (lấy từ `config('app.hospital_name')`), thông tin đợt kiểm kê, bảng tổng hợp số liệu đối soát, bảng chi tiết danh sách thiết bị chênh lệch (thiếu, thừa, hỏng) và 4 vị trí ký xác nhận.
  - Xuất Excel danh sách chênh lệch (`/inventory/{session}/export-excel`) qua `InventoryDiscrepancyExport` bằng `maatwebsite/excel`.
  - Service dùng chung: `App\Services\InventoryReportService` gom toàn bộ logic phân quyền, tính toán số liệu đối soát và áp dụng department scope dùng chung cho Controller, Livewire và Export.
  - Quy định trạng thái: Kỳ đang thực hiện (`in_progress`) có thông báo và watermark "DỰ THẢO - CHƯA CHỐT" / "Chưa chốt"; kỳ đã chốt (`completed`) là biên bản chính thức.
  - Phân quyền và bảo mật: Luật kiểm kê chuẩn (BGĐ 403; khoa sử dụng chỉ xem kỳ khoa mình, 403 kỳ khác/toàn viện; user không khoa 403); lọc theo `EquipmentPolicy::view` trên từng thiết bị để không làm rò rỉ máy khoa khác (đặc biệt item thừa).
  - Nút điều hướng "Báo Cáo Đối Soát" tích hợp trực tiếp tại trang chi tiết kỳ kiểm kê (`InventoryShow`).
- [x] **Module 6d: Quét QR bằng camera điện thoại mở thẳng thẻ thông tin máy (`/q/{code}`)**:
  - Nhãn in mới sinh mã QR chứa đường dẫn URL đầy đủ dạng `rtrim(config('app.url'), '/') . '/q/' . rawurlencode($equipment->code)`. Kích thước QR khi in tối thiểu khoảng 25mm (100px) giúp camera điện thoại dễ quét nhanh.
  - Route `GET /q/{code}` (`equipment.scan`, middleware `auth`, `CheckDepartmentScope`), khai báo duy nhất trong `routes/web.php`.
  - Sử dụng layout di động riêng `components.layouts.mobile` tinh gọn (thanh trên cùng, menu popup tới Dashboard & Đăng xuất, hoàn toàn không có sidebar `<aside>` chiếm diện tích), thẻ thiết bị nằm ngay đầu trang.
  - Nhãn in QR có thêm dòng chữ ngắn dạng `/q/{code}` bên dưới mã thiết bị với cỡ chữ 8.5px không làm tràn nhãn.
  - Bắt buộc đăng nhập mới xem được thông tin máy (redirect intended, không lộ tên/mã/thông tin máy trên trang login hay lỗi).
  - Phân quyền nghiêm ngặt theo `EquipmentPolicy::view`: Khoa sử dụng chỉ xem máy khoa mình, khoa khác 403; Ban Giám đốc xem được; mã không tồn tại 404 thân thiện; hỗ trợ mã có ký tự đặc biệt và khoảng trắng.
  - Giao diện "Thẻ thiết bị" tối ưu responsive cho điện thoại di động: sắp xếp theo thứ tự (a) Tên, mã, tình trạng nổi bật, khoa, vị trí; (b) Hãng, model, serial, năm sản xuất, hạn bảo hành, nhà cung cấp; (c) Lịch sử tình trạng 5 dòng gần nhất; (d) Hồ sơ pháp lý thu gọn dạng accordion; (e) Link xem trang hồ sơ đầy đủ.
  - Tuyệt đối **không hiển thị giá mua (`purchase_price`) và nguồn kinh phí (`funding_source`)** trên thẻ di động.
  - Nút "Đổi Tình Trạng Thiết Bị" to, dễ bấm cho người có quyền `updateCondition` (KTV và Admin; Khoa sử dụng và Ban Giám đốc chỉ xem, không có nút). Cập nhật qua `EquipmentConditionService` lưu lịch sử và cập nhật ngay trên giao diện.
  - Màn quét kiểm kê (`InventoryShow` + `app.js`): nhận diện song song cả 2 dạng payload: đường dẫn URL `.../q/{code}` tự động tách mã `{code}` đã decode, và mã thô truyền thống của nhãn cũ.
  - Ô "Ghi nhớ đăng nhập" được chọn sẵn mặc định; hỗ trợ cấu hình `SESSION_LIFETIME` trong `.env`.
- [x] **Module 8: Quản lý Người dùng & Đổi mật khẩu (`/users`, `/profile/password`)**:
  - Quản trị viên quản lý toàn diện danh sách người dùng (`/users`), tìm kiếm theo tên/email, lọc theo vai trò, khoa phòng, trạng thái (đang hoạt động / đã khóa), phân trang 15 dòng.
  - Tạo & sửa người dùng: phân 5 vai trò hệ thống, ràng buộc vai trò `using_dept` bắt buộc chọn khoa, các vai trò khác tùy chọn.
  - Khóa / mở khóa tài khoản: người dùng bị khóa không thể đăng nhập và phiên hiện tại bị vô hiệu ngay lập tức qua middleware `CheckDepartmentScope`.
  - Đặt lại mật khẩu (admin nhập mật khẩu mới tối thiểu 8 ký tự, không hiển thị lại sau khi lưu).
  - Xóa mềm (`SoftDeletes`): không dùng forceDelete để bảo toàn quan hệ toàn vẹn cho bảng `audit_logs` (append-only).
  - Chặn tự hại: admin không thể tự khóa, tự xóa, tự gỡ vai trò admin của chính mình; bảo vệ không thể khóa/xóa/hạ quyền admin cuối cùng của hệ thống.
  - Trang đổi mật khẩu cá nhân (`/profile/password`): cho phép mọi người dùng đã đăng nhập tự đổi mật khẩu (xác thực mật khẩu cũ, mật khẩu mới tối thiểu 8 ký tự và xác nhận).
  - Đã gỡ `@if(false)` hiển thị menu **Quản lý Người dùng** trên sidebar cho tài khoản có quyền `users.manage`.
  - **Module 8b bổ sung**: Kiểm tra `$user->can('users.manage')` ở đầu từng hành động (`createUser`, `updateUser`, `resetPassword`, `toggleStatus`, `deleteUser`, `openEditModal`, `openResetModal`); Ghi Audit Log tường minh khi tạo user (kèm role + khoa), đổi vai trò (cũ → mới), đổi khoa; `isLastAdmin` xác định chuẩn xác: chỉ coi admin cuối cùng khi đối tượng là admin đang hoạt động (`status = active`) và là admin hoạt động duy nhất (cho phép xóa admin đã khóa khi còn admin hoạt động khác).
- [x] **Gói sửa lỗi & Hoàn thiện toàn diện hệ thống (Items A–I)**:
  - **A**: Khắc phục 500 trang `/equipment/{id}/edit` do biến `$conditions`, loại bỏ khối chọn tình trạng khỏi form sửa; `EquipmentEdit::save()` dùng `Rule::unique('equipment', 'code')->ignore($this->equipment->id)` và serial_number; loại bỏ `setRouteResolver`.
  - **B**: Thêm kiểu trả về `query(): Builder` cho `EquipmentExport`; chống triệt để Formula Injection (CSV / Excel formula injection) bằng cách thoát các giá trị chuỗi bắt đầu bằng `=`, `+`, `-`, `@` (thêm dấu nháy đơn `'`) trong `EquipmentExport`, `DocumentReportExport`, `InventoryDiscrepancyExport`.
  - **C**: `ImportPl2EquipmentCommand`: không dùng `updateOrCreate` ghi đè toàn bộ dữ liệu (chỉ cập nhật nhưng giữ khoa, tình trạng, trạng thái, serial và các trạng thái hồ sơ đã rà soát); `parseDocStatus` xét "không áp dụng", "không", "chưa" trước "có"; dry-run không ghi CSDL; tạo user `system-importer` với mật khẩu ngẫu nhiên an toàn.
  - **D**: `EquipmentImport`: bọc ghi dữ liệu trong `DB::transaction`; khi lỗi đặt lô về trạng thái `failed`; bước xác nhận kiểm tra lại cả mã lẫn serial trùng; kiểm tra lịch chặt chẽ (31/02/2025 báo lỗi).
  - **E**: Khắc phục lỗi 500 khi tạo mới danh mục/hãng/model/khoa/vị trí/nhà cung cấp và email người dùng có mã trùng với bản ghi đã xóa mềm (loại bỏ `whereNull('deleted_at')` trong unique validation để báo lỗi validation thân thiện).
  - **F**: Ghi Audit Log khi quản trị viên đặt lại mật khẩu người dùng (không lưu mật khẩu); đổi khoa ở `EquipmentEdit` ghi nhận lịch sử vào `EquipmentDepartmentHistory`.
  - **G**: Cấu hình `APP_LOCALE=vi` và `APP_FALLBACK_LOCALE=vi` trong `.env` và `.env.example`, bổ sung kiểm thử thông báo validate tiếng Việt chuẩn.
  - **H**: Chặn sửa và xóa khoa hệ thống `CHUA-PHAN-KHOA` trong `MasterDataIndex`; `EquipmentShow::saveDocument` kiểm tra `editingDocType` thuộc `DocumentTypeEnum` (báo lỗi validate, không ném `ValueError`).
  - **I**: Bổ sung Smoke Test toàn diện (`SmokeTest.php`): quét toàn bộ các route trang web với từng vai trò trong 5 vai trò hệ thống, khẳng định không có route nào bị lỗi 500 Server Error (200, 302, 403 tùy theo phân quyền).

---

### Lưu ý về trạng thái các menu trên thanh điều hướng:
Toàn bộ các phân hệ chính của Phase 1 đã hoàn thiện và hoạt động đầy đủ trên thanh điều hướng cho các vai trò có quyền:
- **Thiết bị Y tế** (`/equipment`)
- **Quản lý Danh mục** (`/categories`)
- **Báo cáo Hồ sơ** (`/reports/documents`)
- **Kiểm kê & QR Code** (`/inventory`)
- **Nhật ký Hệ thống (Audit Log)** (`/audit-logs`)
- **Quản lý Người dùng** (`/users`)
- **Đổi mật khẩu cá nhân** (`/profile/password`)

---

## 6. NGUYÊN TẮC BẮT BUỘC KHI VIẾT CODE CHO CLAUDE

1. **Tuân thủ quy ước Laravel & PSR-12:**
   - Đặt tên chuẩn: Eloquent Model (`PascalCase`), bảng và cột (`snake_case`), routes theo RESTful.
   - Dùng Form Request hoặc Livewire validation rules, không validate trực tiếp trong controller.
2. **Không sửa Migration cũ:**
   - Mọi thay đổi cấu trúc bảng bắt buộc phải tạo file **Migration mới** (ví dụ: `php artisan make:migration ...`).
3. **Bảo mật dữ liệu thật (`data-real/`):**
   - Thư mục `data-real/` chứa dữ liệu nội bộ bệnh viện. **TUYỆT ĐỐI KHÔNG** chép tên bác sĩ, số điện thoại, thông tin định danh cá nhân vào seeder, test, log hoặc git commit.
4. **Kiểm thử thực tế (No Guesswork):**
   - Sau khi viết code, luôn chạy:
     ```powershell
     C:\php8\php.exe artisan migrate:fresh --seed
     C:\php8\php.exe vendor/phpunit/phpunit/phpunit
     ```
   - Tất cả các test phải PASS. Viết Feature Test bổ sung cho mọi tính năng mới tạo.
