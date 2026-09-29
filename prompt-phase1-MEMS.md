# PROMPT: Triển khai Phase 1 — Hệ thống Quản lý Danh mục & Kiểm kê Trang thiết bị Y tế (MEMS)

## VAI TRÒ
Bạn là một senior full-stack developer, có kinh nghiệm xây dựng hệ thống quản lý tài sản/thiết bị (EAM/CMMS) bằng Laravel. Hãy đóng vai trò kiến trúc sư kiêm lập trình viên chính, triển khai từng module theo đúng thứ tự ưu tiên bên dưới, viết code sạch, có migration, seeder, validation và test cơ bản.

## BỐI CẢNH DỰ ÁN
Xây dựng hệ thống quản lý trang thiết bị y tế cho một bệnh viện quy mô ~200 giường, khoảng 1.000 thiết bị. Đội phát triển chỉ có 1 người (dùng AI hỗ trợ code). Đây là **Phase 1 (MVP)**, hiện đang chạy ở **môi trường local để test/demo**, chưa deploy thật, chưa có dữ liệu thật. Phase 1 tập trung vào: danh mục thiết bị, phân quyền, nhập/xuất dữ liệu, tra cứu, tình trạng thiết bị, kiểm kê bằng QR code, audit log. **Không làm** bảo trì/sửa chữa, kiểm định/hiệu chuẩn, điều chuyển, dashboard nâng cao, mua sắm/kho vật tư, kế toán, AI/IoT (để dành Phase 2–4).

## MÔI TRƯỜNG PHÁT TRIỂN & CHẠY LOCAL (đọc trước khi bắt đầu)
- **Cách làm việc đã chốt: cài môi trường trước, sau đó AI chạy lệnh trực tiếp trên máy (không viết code "mù" rồi giao lại để người dùng tự cài và tự chạy).** Trước khi viết bất kỳ file nghiệp vụ nào, phải xác nhận `php -v` (>= 8.2), `composer -V`, `node -v`/`npm -v` chạy được trong terminal. Nếu thiếu, dừng lại và hướng dẫn cài đặt cụ thể theo hệ điều hành trước (Windows: Laragon — có sẵn PHP/Composer/Node/npm, tự thêm vào PATH; macOS: Homebrew; Linux: apt/dnf), rồi mới tiếp tục.
- Toàn bộ Phase 1 chạy trên **máy local (localhost)**, không cần domain thật, không cần HTTPS, không cần deploy lên server.
- Từ Module 1 trở đi, mọi bước đều thực thi bằng lệnh thật (`composer create-project`, `composer require ...`, `php artisan make:migration/model/livewire`, `php artisan migrate`, `php artisan test`...) và kiểm tra kết quả thật (migrate thành công, seeder chạy không lỗi, test pass/fail) trước khi báo cáo module đã xong — không suy đoán rằng code "sẽ chạy được".
- Database local: dùng **SQLite** (`database/database.sqlite`) làm mặc định để khởi động nhanh nhất, không cần cài đặt PostgreSQL server riêng. Nếu người dùng đã có PostgreSQL cài sẵn, có thể chuyển `.env` sang PostgreSQL local (`127.0.0.1`), nhưng SQLite là lựa chọn mặc định cho giai đoạn test này.
- Riêng môi trường test (`.env.testing`) luôn dùng SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) để test chạy nhanh, độc lập với database dev.
- Sau khi cài xong, chạy được ngay bằng `php artisan serve` (mặc định `http://127.0.0.1:8000`), kèm `npm run dev` cho Tailwind/Vite nếu cần.
- Không cấu hình gửi email thật (dùng `MAIL_MAILER=log` trong `.env` local — email sẽ ghi vào log thay vì gửi thật).

## CHẾ ĐỘ LOCAL/TEST — NHỮNG GÌ ĐƯỢC NỚI LỎNG VÀ NHỮNG GÌ KHÔNG
Mục tiêu: giảm ma sát khi test trên máy cá nhân, nhưng không phá vỡ kiến trúc bảo mật cốt lõi (để sau này chuyển sang production không phải viết lại).

**Được phép nới lỏng, chỉ áp dụng khi `APP_ENV=local`:**
- `APP_DEBUG=true` để hiện lỗi chi tiết khi test.
- Bỏ yêu cầu xác thực email (email verification) — không cần thiết ở giai đoạn demo nội bộ.
- Tăng thời gian session timeout hoặc tắt tự động logout khi rảnh, để không bị văng ra giữa lúc test.
- Seed sẵn 5 tài khoản demo (một tài khoản cho mỗi vai trò) với mật khẩu đơn giản, cố định, **in rõ trong README/seeder** để người dùng đăng nhập test ngay, ví dụ: `admin@mems.local / password`.
- Không bắt buộc mật khẩu phức tạp (độ dài tối thiểu thấp) cho tài khoản seed — chỉ áp dụng cho dữ liệu giả lập, không áp dụng logic này cho form đăng ký thật.

**Không được tắt, kể cả ở local** (vì đây là thói quen code, không phải rào cản khi test):
- Không tắt CSRF protection của Laravel.
- Không tắt password hashing (bcrypt/argon2) — kể cả tài khoản demo vẫn phải hash bình thường, chỉ là mật khẩu gốc đơn giản.
- Không bỏ qua Policy/Gate kiểm tra quyền — thay vào đó seed đủ tài khoản mỗi vai trò để test đúng luồng phân quyền thật.
- Không tắt validation ở Form Request.

Tất cả cấu hình nới lỏng phải đọc từ `.env` (biến `APP_ENV`, `SESSION_LIFETIME`,...), **tuyệt đối không hard-code `if (app()->environment('local')) { bỏ qua check quyền }`** trong code nghiệp vụ — điều này tạo lỗ hổng nếu quên xóa trước khi lên production.

## DỮ LIỆU GIẢ LẬP (FAKE DATA) — CHƯA CÓ DỮ LIỆU THẬT
Vì bệnh viện chưa cung cấp dữ liệu thật, toàn bộ seeder phải tạo dữ liệu giả nhưng **thực tế, bằng tiếng Việt**, dùng Faker (`fakerphp/faker`, locale `vi_VN` nếu có, hoặc tự viết mảng dữ liệu mẫu cho các danh mục y tế):
- 5–8 khoa/phòng thực tế của bệnh viện (Nội, Ngoại, Cấp cứu, Hồi sức tích cực, Sản, Nhi, Chẩn đoán hình ảnh, Xét nghiệm...).
- 15–20 hãng sản xuất thiết bị y tế có thật (Philips, GE Healthcare, Siemens Healthineers, Mindray, Dräger, Nihon Kohden...).
- 20–30 model thiết bị y tế thực tế (máy thở, monitor bệnh nhân, máy X-quang, máy siêu âm, máy ECG, bơm tiêm điện, lồng ấp sơ sinh...) gắn với hãng và loại tương ứng.
- Khoảng **100–150 thiết bị (asset)** cụ thể, phân bổ ngẫu nhiên vào các khoa/vị trí, có mã thiết bị, serial number không trùng, ngày mua/ngày dùng hợp lý (rải trong 1–10 năm gần đây), tình trạng đa dạng (đa số "Đang hoạt động", một ít "Cần theo dõi/Cần bảo trì/Hỏng" để demo được các luồng lọc/tra cứu).
- Một vài bản ghi `equipment_condition_history` và `inventory_sessions`/`inventory_items` mẫu để demo được ngay tab lịch sử và kiểm kê mà không cần thao tác tay từ đầu.
- Một file Excel mẫu (`storage/app/samples/sample_import.xlsx` hoặc tương đương) chứa cả dòng hợp lệ lẫn vài dòng cố tình lỗi (thiếu mã, trùng serial, sai định dạng ngày) để test tính năng Import Preview ở Module 3 mà không cần tự tạo file tay.
- Ghi rõ trong README cách chạy lại seeder (`php artisan migrate:fresh --seed`) để reset dữ liệu giả bất cứ lúc nào trong quá trình test.

## TECH STACK BẮT BUỘC
- Backend: Laravel (bản mới nhất ổn định)
- Frontend: Laravel + Livewire + Tailwind CSS (ưu tiên giảm code JS riêng)
- Database dev/demo: **SQLite** (mặc định, xem phần Môi trường ở trên); Database test: SQLite in-memory riêng
- Lưu file: local storage giai đoạn đầu (thiết kế để dễ chuyển sang S3-compatible sau)
- Phân quyền: dùng thẳng package **`spatie/laravel-permission`** — dùng nguyên migration/bảng mặc định của package (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`). Nếu cần thêm field riêng (`display_name`, `group_name`...), publish migration của package rồi thêm cột bằng migration bổ sung, **không tự ý tạo bảng trùng tên**.
- QR Code quét: thư viện JS `html5-qrcode` nhúng trong Livewire component; **luôn có ô nhập mã thiết bị thủ công cạnh khung quét** để dùng khi camera không khả dụng hoặc thiết bị không có camera tốt.
- Excel import/export: maatwebsite/excel (Laravel Excel)
- PDF export: barryvdh/laravel-dompdf

## PHẠM VI PHASE 1 — 7 MODULE (theo đúng thứ tự triển khai)

### Module 1: Đăng nhập & Phân quyền
- Đăng nhập/đăng xuất, quên mật khẩu (dùng `MAIL_MAILER=log` ở local), session timeout cấu hình qua `.env`
- Model: User, Department + Role/Permission của `spatie/laravel-permission`
- 5 role mặc định: Quản trị hệ thống, Phòng Vật tư-TBYT, Kỹ thuật viên, Khoa sử dụng, Ban giám đốc — theo đúng ma trận quyền bên dưới
- Kiểm tra quyền theo action (xem/sửa/xóa/xuất): dùng middleware `permission:` / `role:` có sẵn của `spatie/laravel-permission` ngay trong route hoặc trong Policy, **không viết middleware `CheckPermission` riêng** vì trùng chức năng package đã cung cấp. Chỉ viết riêng middleware `CheckDepartmentScope` để giới hạn Khoa sử dụng chỉ xem được thiết bị của khoa mình (logic này package không có sẵn)
- Seeder tạo sẵn 5 tài khoản demo (một tài khoản/vai trò), mật khẩu đơn giản đã hash, in rõ thông tin đăng nhập trong README

### Module 2: Danh mục thiết bị
- CRUD: Equipment Category (loại thiết bị), Manufacturer (hãng), Equipment Model (model), Supplier (nhà cung cấp), Location (vị trí), Department (khoa/phòng)
- CRUD Equipment (asset) — tách riêng khái niệm **Equipment Model** (VD: Philips IntelliVue MX450) và **Equipment Asset** (từng thiết bị vật lý có serial number riêng, mã quản lý riêng)
- Trường dữ liệu Equipment: mã thiết bị, tên, model_id, category_id, manufacturer_id, serial_number, năm sản xuất, nước sản xuất, ngày nhập, ngày đưa vào sử dụng, giá trị, nguồn kinh phí, supplier_id, thời hạn bảo hành, department_id, location_id, người phụ trách, trạng thái, tình trạng, ghi chú
- Soft delete (deleted_at, deleted_by). Ở Phase 1: **chỉ Quản trị hệ thống được xóa thật (soft delete)**; Phòng Vật tư-TBYT chỉ có nút "Đề nghị xóa" trong màn hình chi tiết thiết bị — action này set `delete_requested_at`/`delete_requested_by` trên bảng `equipment` (thêm 2 cột này vào migration) và ghi audit log, Admin thấy được danh sách "đang chờ xóa" để tự quyết định; chưa làm workflow duyệt nhiều bước (để Phase 2)
- Dùng **Model Factory** (`EquipmentFactory`, `EquipmentModelFactory`, `ManufacturerFactory`...) cho tất cả model chính, Seeder gọi factory thay vì viết vòng lặp tạo dữ liệu tay — đúng convention Laravel và tái dùng được cho factory trong test
- Seeder dữ liệu giả theo mục "DỮ LIỆU GIẢ LẬP" ở trên

### Module 3: Nhập/Xuất dữ liệu
- Bảng `import_batches`: `id, file_name, imported_by, total_rows, success_rows, failed_rows, created_at` để lưu vết mỗi lần import, biết thiết bị nào được import từ đợt nào
- Import Excel: upload file → validate từng dòng → hiển thị preview (số dòng hợp lệ / lỗi thiếu mã / trùng serial / sai định dạng ngày...) → người dùng sửa hoặc bỏ qua dòng lỗi → xác nhận import → ghi vào `import_batches` + error log chi tiết
- Có sẵn file Excel mẫu (hợp lệ + có lỗi) để test ngay, theo mục Dữ liệu giả lập
- Export Excel/CSV/PDF: toàn bộ danh sách hoặc theo bộ lọc hiện tại (theo khoa, theo tình trạng, v.v.)

### Module 4: Tra cứu thiết bị
- Màn hình danh sách: tìm kiếm full-text (mã, tên, model, serial, hãng, khoa, người quản lý), bộ lọc (khoa, loại, hãng, tình trạng, trạng thái), sắp xếp, phân trang
- Nút xuất Excel ngay từ kết quả lọc hiện tại

### Module 5: Tình trạng thiết bị
- Các giá trị tình trạng cố định: Đang hoạt động / Cần theo dõi / Cần bảo trì / Hỏng / Ngừng sử dụng
- Bảng equipment_condition_history: mỗi lần đổi tình trạng phải ghi log (equipment_id, old_condition, new_condition, reason, note, checked_by, checked_at) — **không được ghi đè trực tiếp** trường condition trên bảng equipment mà không lưu lịch sử (dùng DB transaction)

### Module 6: Kiểm kê thiết bị (Inventory)
- Tạo kỳ kiểm kê (inventory_session): tên, ngày bắt đầu/kết thúc, khoa áp dụng, trạng thái, người tạo
- Mỗi thiết bị trong khoa được đưa vào inventory_items: vị trí hệ thống, vị trí thực tế, kết quả (có mặt/không tìm thấy/sai vị trí/hỏng), tình trạng, ghi chú, người kiểm, thời gian kiểm
- Sinh mã QR cho từng thiết bị, in ra (mã QR + mã thiết bị)
- Giao diện quét QR (HTML5 camera qua web) **kèm ô nhập mã thủ công dự phòng** → hiện thông tin thiết bị → chọn nhanh (Có mặt / Hư hỏng / Đã chuyển / Không tìm thấy)
- Báo cáo chênh lệch cuối kỳ: theo hệ thống bao nhiêu, đã kiểm bao nhiêu, chênh lệch bao nhiêu, xuất biên bản kiểm kê (PDF)

### Module 7: Audit Log & hệ thống
- Bảng `audit_logs` là **append-only**: chỉ insert, không cho phép update/delete qua Eloquent model (không định nghĩa các method update/delete, hoặc chặn bằng model event)
- Ghi log mọi hành động tạo/sửa/xóa trên **tất cả model quan trọng** (Equipment, User, Role, Department, InventorySession, InventoryItem, EquipmentConditionHistory...) qua Observer dùng chung, không chỉ riêng Equipment: ai, thời gian, trước/sau, IP
- File attachment: cho phép đính kèm tài liệu/hình ảnh vào hồ sơ thiết bị
- Artisan command hướng dẫn/thực hiện backup database (cho SQLite: copy file `.sqlite`; cho PostgreSQL: `pg_dump`)

## MA TRẬN QUYỀN (áp dụng ngay từ Module 1)
| Vai trò | Xem thiết bị | Sửa thiết bị | Xóa thiết bị | Đổi tình trạng | Kiểm kê | Import/Export | Quản lý user |
|---|---|---|---|---|---|---|---|
| Quản trị hệ thống | Toàn bộ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Phòng Vật tư-TBYT | Toàn bộ | ✓ | ✗ (chỉ đề nghị) | ✓ | ✓ | ✓ | ✗ |
| Kỹ thuật viên | Toàn bộ | Cập nhật tình trạng | ✗ | ✓ | ✓ | ✗ | ✗ |
| Khoa sử dụng | Chỉ khoa mình | ✗ (chỉ đề nghị) | ✗ | ✗ | ✓ (khoa mình) | ✗ | ✗ |
| Ban giám đốc | Toàn bộ (chỉ xem) | ✗ | ✗ | ✗ | ✗ | Xuất báo cáo | ✗ |

## THIẾT KẾ DATABASE (bảng chính — tạo migration đầy đủ)
```
users, departments
roles, permissions, model_has_roles, model_has_permissions, role_has_permissions  (bảng chuẩn của spatie/laravel-permission)
equipment_categories, manufacturers, equipment_models, suppliers, locations
equipment (asset), equipment_condition_history
import_batches
inventory_sessions, inventory_items
audit_logs, attachments
```
Lưu ý quan hệ bắt buộc: `equipment_models` (1) — (n) `equipment` (asset); `departments` (1) — (n) `equipment`; `equipment` (1) — (n) `equipment_condition_history`; `import_batches` (1) — (n) `equipment` (thiết bị nào import từ đợt nào, nullable nếu tạo tay); `inventory_sessions` (1) — (n) `inventory_items` (n) — (1) `equipment`.

## YÊU CẦU KỸ THUẬT & CHUẨN CODE
1. Tuân thủ chuẩn PSR-12, đặt tên bảng/field theo snake_case, đúng convention Laravel (Eloquent model, migration, factory, seeder cho mỗi bảng)
2. Validate dữ liệu ở Form Request riêng cho từng action, không validate trực tiếp trong controller
3. Áp dụng Policy/Gate cho từng model để kiểm soát quyền theo ma trận ở trên — **không bỏ qua kể cả khi chạy local** (xem mục "Chế độ local/test")
4. Toàn bộ thao tác xóa dùng SoftDeletes, không xóa cứng dữ liệu nghiệp vụ
5. Viết Feature Test tối thiểu cho: đăng nhập, CRUD equipment, import Excel (kèm dữ liệu lỗi), tạo kỳ kiểm kê — chạy trên SQLite in-memory riêng (`.env.testing`)
6. Có seeder dữ liệu giả đầy đủ theo mục "DỮ LIỆU GIẢ LẬP" để có thể demo toàn bộ 7 module ngay sau khi cài đặt, không cần nhập tay
7. Bảo mật cốt lõi (hash password, CSRF, validation, Policy) giữ nguyên như production; chỉ nới lỏng các mục tiện lợi khi test đã liệt kê ở mục "Chế độ local/test", không nới lỏng gì thêm ngoài danh sách đó

## TIÊU CHÍ HOÀN THÀNH (Definition of Done) CHO PHASE 1
- [ ] Chạy được trên máy local bằng `php artisan serve` + SQLite, không cần cấu hình server/domain thật
- [ ] Đăng nhập được ngay bằng 5 tài khoản demo có sẵn (một tài khoản/vai trò), phân quyền hoạt động đúng ma trận
- [ ] CRUD đầy đủ cho 6 danh mục (category, manufacturer, model, supplier, location, department) và equipment
- [ ] Dữ liệu giả đã có sẵn sau khi seed: đủ khoa, hãng, model, ~100–150 thiết bị, không cần nhập tay để demo
- [ ] Import Excel có preview lỗi trước khi xác nhận (có sẵn file mẫu để test), export Excel/PDF hoạt động
- [ ] Tìm kiếm/lọc/sắp xếp/phân trang trên danh sách thiết bị
- [ ] Đổi tình trạng thiết bị luôn ghi vào equipment_condition_history
- [ ] Tạo được kỳ kiểm kê, sinh QR, quét QR (có ô nhập tay dự phòng) cập nhật kết quả, xuất báo cáo chênh lệch
- [ ] Audit log ghi nhận đầy đủ hành động tạo/sửa/xóa, không sửa/xóa được bản ghi log
- [ ] Test tự động chạy trên SQLite in-memory và pass

## ĐỊNH DẠNG OUTPUT MONG MUỐN
Triển khai theo từng module một, mỗi module gồm: migration → model + quan hệ → seeder mẫu (dữ liệu giả tiếng Việt) → policy → Form Request → controller/Livewire component → view Blade → test. Sau mỗi module, tóm tắt ngắn gọn các file đã tạo/sửa và **cách chạy thử ngay trên local** (artisan command, URL localhost, tài khoản đăng nhập nếu cần) trước khi sang module tiếp theo. Không triển khai gộp nhiều module cùng lúc để dễ review.
