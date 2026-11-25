# MEMS — Hệ Thống Quản Lý Trang Thiết Bị Y Tế Bệnh Viện

**MEMS (Medical Equipment Management System)** là hệ thống quản lý danh mục, hồ sơ pháp lý, tình trạng kỹ thuật và kiểm kê trang thiết bị y tế nội bộ bệnh viện. Phần mềm phục vụ trực tiếp **Phòng Vật tư – Thiết bị Y tế**, **các khoa/phòng sử dụng**, **kỹ thuật viên**, **Ban Giám đốc** và **Quản trị hệ thống**, chạy trên nền tảng web nội bộ (Laravel + Livewire + SQLite).

---

## 🛠️ Công Nghệ Sử Dụng

| Thành phần | Công nghệ & Phiên bản (`composer.json` / `package.json`) |
|---|---|
| **Ngôn ngữ & Runtime** | PHP `^8.3` |
| **Backend Framework** | Laravel Framework `^13.17` |
| **Giao diện (Frontend)** | Livewire `^4.4`, Tailwind CSS `^4.0`, Vite `^8.0` |
| **Cơ sở dữ liệu** | SQLite (file `database/database.sqlite`) |
| **Phân quyền** | `spatie/laravel-permission` `^8.3` |
| **Nhập / Xuất Excel & CSV** | `maatwebsite/excel` `^4.0` |
| **Xuất PDF** | `barryvdh/laravel-dompdf` `^3.1` |
| **Tạo & Quét mã QR** | `simplesoftwareio/simple-qrcode` `^4.2`, `html5-qrcode` `^2.3.8` |
| **Kiểm thử & Định dạng mã** | PHPUnit `^12.5`, Laravel Pint `^1.27` |

---

## PHẦN A — HƯỚNG DẪN SỬ DỤNG (DÀNH CHO NGƯỜI DÙNG CUỐI)

### 1. Đăng nhập & Đổi mật khẩu cá nhân
1. Truy cập địa chỉ hệ thống bằng trình duyệt (trang chủ tự động chuyển tới `/login`).
2. Nhập **Email** và **Mật khẩu**. Ô **Ghi nhớ đăng nhập** được chọn sẵn mặc định để hạn chế gián đoạn khi đi kiểm kê.
3. Để đổi mật khẩu cá nhân: bấm vào mục **Đổi mật khẩu** trên thanh menu (`/profile/password`), nhập mật khẩu hiện tại, mật khẩu mới (tối thiểu 8 ký tự) và xác nhận mật khẩu mới.

### 2. Bảng vai trò và quyền hạn

Hệ thống phân chia thành **5 vai trò** (theo `RolePermissionSeeder` và `EquipmentPolicy`):

| Vai trò | Mã vai trò | Phạm vi dữ liệu | Quyền hạn chính |
|---|---|---|---|
| **Quản trị hệ thống** | `admin` | Toàn viện | Toàn quyền: xem/thêm/sửa/xóa mềm thiết bị, duyệt đề nghị xóa, nhập/xuất dữ liệu, kiểm kê, quản lý danh mục, quản lý người dùng (`/users`), xem Audit Log. |
| **Phòng Vật tư – TBYT** | `equipment_dept` | Toàn viện | Xem/thêm/sửa thiết bị, đề nghị xóa, đổi tình trạng kỹ thuật, nhập/xuất Excel/CSV/PDF, tạo và thực hiện kiểm kê, quản lý danh mục, xem Audit Log. **Không được xóa mềm trực tiếp.** |
| **Kỹ thuật viên** | `technician` | Toàn viện | Xem toàn bộ thiết bị, cập nhật tình trạng kỹ thuật, in nhãn QR, quét kiểm kê và báo hỏng trong kỳ kiểm kê đang mở. **Không được tạo/chốt kỳ kiểm kê, không thêm/sửa/xóa thiết bị.** |
| **Khoa sử dụng** | `using_dept` | **Chỉ khoa mình** | Chỉ xem và đề nghị xóa thiết bị thuộc khoa mình (truy cập máy khoa khác báo lỗi `403`). Tạo/quét/chốt kỳ kiểm kê của khoa mình, in nhãn khoa mình, xem báo cáo hồ sơ khoa mình. |
| **Ban Giám đốc** | `directorate` | Toàn viện | **Chỉ xem (Read-only)**: xem danh sách/chi tiết thiết bị, xuất Excel/CSV/PDF, in nhãn đơn lẻ/theo khoa, xem báo cáo hồ sơ, xem Audit Log. **Không được thêm/sửa/xóa hay truy cập Kiểm kê.** |

### 3. Quản lý thiết bị (`/equipment`)
1. **Tìm kiếm & Lọc**: Lọc theo từ khóa (mã, tên, serial, model), Khoa/Phòng, Loại thiết bị, Hãng sản xuất, Trạng thái sử dụng (`Đang sử dụng`, `Dự phòng`, `Đề nghị thanh lý`, `Đã thanh lý`), Tình trạng kỹ thuật (`Đang hoạt động`, `Cần theo dõi`, `Cần bảo trì`, `Hỏng`, `Ngừng sử dụng`), Loại sở hữu (`Mua sắm`, `Máy đặt của nhà cung cấp`, `Mượn`, `Tài trợ`), thiết bị thiếu Serial hoặc thiếu loại hồ sơ.
2. **Thêm mới / Chỉnh sửa**: Người có quyền bấm **Thêm thiết bị** (`/equipment/create`) hoặc mở chi tiết thiết bị → **Chỉnh sửa** (`/equipment/{id}/edit`). Lưu ý: khi đổi khoa trên màn hình sửa, hệ thống tự động ghi nhận vào lịch sử chuyển khoa.
3. **Xuất dữ liệu**: Bấm **Xuất Excel**, **Xuất CSV** hoặc **Xuất PDF**. Xuất PDF danh sách giới hạn tối đa **200 thiết bị/lần** (nếu vượt quá, hãy thu hẹp bộ lọc hoặc dùng Excel/CSV).
4. **Đổi tình trạng kỹ thuật**: Tại trang chi tiết thiết bị (`/equipment/{id}`), người có quyền chọn tình trạng mới (phải khác tình trạng hiện tại) và **bắt buộc nhập lý do** (có thể thêm ghi chú). Hệ thống lưu lại toàn bộ lịch sử thay đổi.
5. **Quy trình xóa 2 bước**:
   - Bước 1: Cán bộ Phòng Vật tư hoặc Khoa sử dụng bấm **Đề nghị xóa** tại trang chi tiết máy.
   - Bước 2: Quản trị hệ thống (`admin`) kiểm tra và bấm **Xóa thiết bị** để xóa mềm (`Soft Delete`, dữ liệu lịch sử vẫn được bảo toàn) hoặc **Hủy đề nghị xóa**.

### 4. Hồ sơ giấy tờ & Báo cáo tổng hợp (`/reports/documents`)
- Mỗi thiết bị được theo dõi **16 loại hồ sơ**: *Hợp đồng/hóa đơn, Biên bản bàn giao, Hồ sơ lưu hành, CO, CQ, Tờ khai hải quan, Tài liệu kỹ thuật/HDSD, Công bố điều kiện mua bán, Lý lịch thiết bị, Nhật ký sử dụng, Quy trình vận hành, Hồ sơ bảo dưỡng, Hồ sơ điều chuyển/thanh lý, Hồ sơ kiểm định, Hồ sơ hiệu chuẩn, Phân công người phụ trách*.
- Mỗi hồ sơ có **4 trạng thái**: `Có`, `Không`, `Không áp dụng`, `Chưa rà soát` (kèm ngày hết hạn và ghi chú).
- Trang **Báo cáo hồ sơ** (`/reports/documents`) thống kê tỷ lệ hoàn thiện hồ sơ theo từng khoa và hỗ trợ **Xuất Excel** (Khoa sử dụng chỉ xem được số liệu khoa mình).

### 5. Thiết bị "Chưa phân khoa" & Gán khoa hàng loạt (`/equipment/unassigned`)
- Các thiết bị nhập từ phụ lục cũ hoặc chưa rõ đơn vị sử dụng được gom vào khoa hệ thống **`CHUA-PHAN-KHOA`**.
- `admin` và `equipment_dept` truy cập mục **Chưa phân khoa**, tích chọn nhiều thiết bị, chọn khoa đích và bấm **Gán khoa**. Hệ thống tự động chuyển khoa và ghi lịch sử điều chuyển.

### 6. In nhãn QR và Quét QR bằng điện thoại
- **In nhãn QR**: Hỗ trợ in nhãn đơn lẻ từ trang chi tiết máy (`/equipment/{id}/label`), in toàn bộ thiết bị một khoa (`/departments/{id}/equipment-labels`), hoặc in theo kỳ kiểm kê (`/inventory/{session}/labels`). Thiết bị `Đã thanh lý` tự động bị loại khỏi danh sách in theo khoa/kỳ.
- **Quét bằng camera điện thoại**: Khi dùng camera điện thoại quét nhãn QR, trình duyệt mở đường dẫn `/q/{mã_thiết_bị}`:
  - Hệ thống **bắt buộc đăng nhập** mới hiển thị thông tin máy (sau khi đăng nhập tự động quay lại đúng thẻ thiết bị vừa quét).
  - Giao diện di động hiển thị gọn gàng thông tin nhận diện, thông số, 5 dòng lịch sử tình trạng gần nhất và hồ sơ pháp lý thu gọn. **Không hiển thị giá mua và nguồn kinh phí.**
  - Người có quyền đổi tình trạng (`admin`, `equipment_dept`, `technician`) có nút **Đổi tình trạng** kích thước lớn thao tác trực tiếp trên điện thoại.

### 7. Kiểm kê thiết bị (`/inventory`)
1. **Tạo kỳ kiểm kê**: `admin`, `equipment_dept` tạo kỳ toàn viện hoặc theo khoa; `using_dept` chỉ tạo được kỳ cho khoa mình; `technician` không được tạo kỳ; `directorate` không có quyền truy cập kiểm kê.
2. **Quét / Nhập mã kiểm kê**: Mở kỳ kiểm kê (`/inventory/{session}`), dùng camera trực tiếp trên web hoặc nhập mã thiết bị / quét bằng máy đọc mã vạch (hệ thống nhận diện cả mã thô cũ lẫn đường dẫn QR đầy đủ `/q/{code}`).
3. **Phân loại kết quả**: `Khớp` (đúng khoa), `Thừa` (máy khoa khác hoặc máy không nằm trong kỳ), `Hỏng` (báo hỏng trực tiếp; nếu người quét có quyền `updateCondition` thì tình trạng máy cũng tự động chuyển sang `Hỏng`), `Thiếu` (máy chưa quét khi chốt kỳ).
4. **Chốt kỳ & Xuất biên bản**: `admin`, `equipment_dept`, `using_dept` (chỉ khoa mình) bấm **Chốt kỳ kiểm kê** để khóa dữ liệu và chuyển các máy chưa quét sang `Thiếu`. Sau đó có thể xem báo cáo đối soát (`/inventory/{session}/report`), xuất **Biên bản PDF** hoặc **Excel chênh lệch**.

### 8. Nhập thiết bị từ Excel (`/equipment/import`)
- **Các bước thực hiện** (dành cho `admin`, `equipment_dept`):
  1. Bấm **Tải File Mẫu Excel** để lấy file chuẩn.
  2. Điền thông tin vào file mẫu theo đúng quy tắc bảng dưới đây.
  3. Tải file lên và bấm **Xem trước & Kiểm tra dữ liệu**. Hệ thống đánh dấu các dòng hợp lệ và dòng lỗi (có thể tích bỏ qua dòng lỗi).
  4. Bấm **Xác nhận nhập** để lưu vào hệ thống (nếu có lỗi hệ thống trong quá trình lưu, toàn bộ lô được hoàn tác an toàn).
- **Giới hạn**: Định dạng `.xlsx` hoặc `.xls`, dung lượng tối đa **5 MB**, tối đa **500 dòng/file**, định dạng ngày bắt buộc **`dd/mm/yyyy`** (kiểm tra lịch hợp lệ, ví dụ `31/02/2025` sẽ báo lỗi).

| Tên cột trong Excel | Bắt buộc | Định dạng & Quy tắc kiểm tra (`EquipmentImportValidator`) |
|---|---|---|
| `Mã thiết bị` (`ma_thiet_bi`) | **Có** | Chuỗi, duy nhất trong file và trong CSDL (kể cả thiết bị đã xóa mềm). |
| `Tên thiết bị` (`ten_thiet_bi`) | **Có** | Chuỗi tên thiết bị. |
| `Mã model` (`ma_model`) | **Có** | Phải khớp với **Mã Model** đã tồn tại trong Danh mục hệ thống. |
| `Mã khoa phòng` (`ma_khoa_phong`) | **Có** | Phải khớp với **Mã Khoa/Phòng** đã tồn tại trong Danh mục hệ thống. |
| `Mã vị trí` (`ma_vi_tri`) | Không | Nếu nhập, phải tồn tại và **thuộc đúng Khoa/Phòng** đã chọn ở trên. |
| `Số serial` (`so_serial`) | Không | Nếu nhập, không được trùng trong file và trong CSDL (kể cả đã xóa mềm). |
| `Năm sản xuất` (`nam_san_xuat`) | Không | Số nguyên từ `1900` đến `năm hiện tại + 1`. |
| `Xuất xứ` (`xuat_xu`) | Không | Chuỗi văn bản (tên quốc gia). |
| `Ngày nhập` (`ngay_nhap`) | Không | Định dạng `dd/mm/yyyy` hoặc ô ngày chuẩn của Excel. |
| `Ngày sử dụng` (`ngay_su_dung`) | Không | Định dạng `dd/mm/yyyy` hoặc ô ngày chuẩn của Excel. |
| `Giá mua` (`gia_mua` / `don_gia`) | Không | Số dương >= 0 (hỗ trợ số chuẩn hoặc kiểu `15.000.000` / `15.000.000,50`). |
| `Nguồn kinh phí` (`nguon_kinh_phi`) | Không | Chuỗi văn bản. |
| `Mã nhà cung cấp` (`ma_nha_cung_cap`) | Không | Nếu nhập, phải khớp với **Mã Nhà cung cấp** trong Danh mục. |
| `Hạn bảo hành` (`han_bao_hanh`) | Không | Định dạng `dd/mm/yyyy` hoặc ô ngày chuẩn của Excel. |
| `Người phụ trách` (`nguoi_phu_trach`) | Không | Tên người phụ trách dạng văn bản. |
| `Trạng thái` (`trang_thai`) | Không | `Đang sử dụng`, `Dự phòng`, `Đề nghị thanh lý`, `Đã thanh lý` (mặc định: Đang sử dụng). |
| `Tình trạng` (`tinh_trang`) | Không | `Đang hoạt động`, `Cần theo dõi`, `Cần bảo trì`, `Hỏng`, `Ngừng sử dụng` (mặc định: Đang hoạt động). |
| `Loại sở hữu` (`loai_so_huu`) | Không | `Mua sắm`, `Máy đặt của nhà cung cấp`, `Mượn`, `Tài trợ` (mặc định: Mua sắm). |
| `Ghi chú` (`ghi_chu`) | Không | Chuỗi văn bản (cột mở rộng tùy chọn: `nguyen_van_model`, `ma_dieu_chuyen_goc`). |

### 9. Nhật ký hệ thống, Quản lý người dùng & Danh mục
- **Nhật ký hệ thống (`/audit-logs`)**: Xem lịch sử tạo/cập nhật/xóa dữ liệu toàn hệ thống (bảng append-only không thể sửa/xóa, tự động ẩn mật khẩu và token). Dành cho `admin`, `equipment_dept`, `directorate`.
- **Quản lý người dùng (`/users`)**: Chỉ `admin`. Hỗ trợ tạo, sửa, phân vai trò, gán khoa (`using_dept` bắt buộc chọn khoa), khóa/mở khóa tài khoản (vô hiệu phiên làm việc ngay), đặt lại mật khẩu và xóa mềm. Hệ thống chặn admin tự khóa/tự xóa/tự hạ quyền và bảo vệ admin hoạt động cuối cùng.
- **Quản lý danh mục (`/categories`)**: Dành cho `admin` và `equipment_dept` để quản lý Loại thiết bị, Hãng sản xuất, Model, Nhà cung cấp, Vị trí lắp đặt và Khoa/Phòng (chặn sửa/xóa khoa `CHUA-PHAN-KHOA`, không cho phép tạo trùng mã với bản ghi đã xóa mềm).

---

## PHẦN B — CÀI ĐẶT & VẬN HÀNH (DÀNH CHO BỘ PHẬN CNTT)

### 1. Yêu cầu hệ thống
- **PHP**: `>= 8.3` với các extension: `pdo_sqlite`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo`.
- **Composer**: `>= 2.x`
- **Node.js & NPM**: Node.js `>= 18.x` để biên dịch giao diện bằng Vite.

### 2. Cài đặt lần đầu từng bước
Thực hiện các lệnh sau tại **thư mục gốc của dự án** (`ql_thietbi`):

```bash
# 1. Cài đặt thư viện PHP
composer install

# 2. Cài đặt thư viện Frontend và build tài nguyên tĩnh (tạo public/build)
npm install
npm run build

# 3. Tạo file cấu hình môi trường .env
cp .env.example .env

# 4. Tạo APP_KEY bảo mật
php artisan key:generate

# 5. Khởi tạo CSDL SQLite (tạo file database/database.sqlite nếu chưa có), chạy migration và seed dữ liệu nền
php artisan migrate --seed

# 6. Chạy thử server phát triển
php artisan serve
```

> Trên Windows dùng PHP cài riêng (ví dụ `C:\php8\php.exe`), thay `php` bằng đường dẫn đầy đủ `C:\php8\php.exe`.

### 3. Bảng biến môi trường quan trọng (`.env`)

| Biến | Giá trị mẫu (`.env.example`) | Ý nghĩa & Khuyến nghị cho môi trường thật (Production) |
|---|---|---|
| `APP_ENV` | `local` | Đặt thành `production` khi vận hành thật. |
| `APP_DEBUG` | `true` | **Bắt buộc đặt `false`** trên môi trường thật để tránh lộ thông tin lỗi/mã nguồn. |
| `APP_URL` | `http://localhost:8000` | **Rất quan trọng**: Đây là tiền tố tạo đường dẫn trong mã QR (`{APP_URL}/q/{code}`). Phải đặt đúng tên miền hoặc IP cố định của máy chủ (VD: `https://tbyt.benhvien.local`) **trước khi in nhãn QR**. |
| `APP_HOSPITAL_NAME` | `"Bệnh viện"` | Tên bệnh viện in trên tiêu đề biên bản kiểm kê PDF. |
| `APP_LOCALE` | `vi` | Ngôn ngữ giao diện và thông báo kiểm tra dữ liệu (`vi` = Tiếng Việt). |
| `APP_FALLBACK_LOCALE` | `vi` | Ngôn ngữ dự phòng khi thiếu bản dịch. |
| `DB_CONNECTION` | `sqlite` | Loại CSDL mặc định (`sqlite` lưu tại `database/database.sqlite`). |
| `SESSION_DRIVER` | `database` | Lưu phiên đăng nhập vào bảng `sessions` (hỗ trợ vô hiệu phiên khi khóa tài khoản). |
| `SESSION_LIFETIME` | `120` | Thời gian sống của phiên (phút). Khuyến nghị `120`–`480` (2–8 giờ) cho ca kiểm kê; thời gian càng dài rủi ro nếu dùng chung thiết bị không khóa màn hình càng cao. |

### 4. Danh sách kiểm tra trước khi chạy thật (Production Checklist)
- [ ] Đặt `APP_ENV=production` và `APP_DEBUG=false`.
- [ ] Chạy `php artisan key:generate` để sinh `APP_KEY` riêng biệt cho máy chủ.
- [ ] Cấu hình đúng `APP_URL` theo tên miền/IP nội bộ cố định trước khi in bất kỳ nhãn QR nào.
- [ ] Bật **HTTPS** (chứng chỉ SSL nội bộ hoặc reverse proxy Nginx/Caddy) — trình duyệt di động/máy tính **chỉ cấp quyền mở Camera quét mã trong trang kiểm kê khi chạy qua HTTPS hoặc `localhost`**.
- [ ] **Đổi mật khẩu hoặc xóa toàn bộ 5 tài khoản demo** được tạo từ `UserSeeder`.
- [ ] Thiết lập lịch sao lưu CSDL tự động hàng ngày ở mức hệ điều hành.

### 5. Tài khoản demo trong Seeder (CHỈ dùng phát triển / thử nghiệm)

> **CẢNH BÁO BẢO MẬT**: Các tài khoản dưới đây được tạo bởi `Database\Seeders\UserSeeder` với mật khẩu mặc định `password` chỉ nhằm mục đích phát triển và kiểm thử. **Tuyệt đối không giữ nguyên mật khẩu này khi đưa vào sử dụng thật.**

| Vai trò | Email đăng nhập mẫu | Khoa mặc định |
|---|---|---|
| Quản trị hệ thống (`admin`) | `admin@mems.local` | Toàn viện |
| Phòng Vật tư – TBYT (`equipment_dept`) | `vattu@mems.local` | Toàn viện |
| Kỹ thuật viên (`technician`) | `kythuat@mems.local` | Toàn viện |
| Khoa sử dụng (`using_dept`) | `khoa@mems.local` | Khoa Nội (`KNOI`) |
| Ban Giám đốc (`directorate`) | `giamdoc@mems.local` | Toàn viện |

### 6. Sao lưu và Khôi phục CSDL
- **Tạo bản sao lưu thủ công**:
  ```bash
  php artisan db:backup --keep=5
  ```
  Lệnh này sao chép `database/database.sqlite` vào thư mục `storage/app/backups/` với tên `database_backup_YYYY-MM-DD_HH-MM-SS.sqlite` và tự động giữ lại `--keep` bản mới nhất (mặc định 5 bản).
- **Lịch sao lưu tự động**: Mã nguồn hiện tại **chưa cài đặt lịch chạy tự động** trong `routes/console.php`. Bộ phận CNTT cần cấu hình lịch ở mức hệ điều hành:
  - **Linux (Cron)**: `0 2 * * * cd /duong/dan/ql_thietbi && php artisan db:backup --keep=14`
  - **Windows (Task Scheduler)**: Tạo tác vụ chạy hàng ngày gọi `C:\php8\php.exe artisan db:backup --keep=14` tại thư mục gốc dự án.
- **Khôi phục dữ liệu**: Dừng dịch vụ web (`php artisan down` hoặc dừng web server), sao chép bản sao lưu mong muốn từ `storage/app/backups/` đè lên file `database/database.sqlite`, sau đó bật lại dịch vụ (`php artisan up`).

### 7. Các lệnh Artisan tùy chỉnh

| Lệnh | Tác dụng & Tham số | Lưu ý / Cảnh báo |
|---|---|---|
| `php artisan equipment:import-pl2 {file} [--commit]` | Nhập danh sách thiết bị từ file Excel Phụ lục 2. Mặc định chạy ở chế độ **DRY-RUN** (chỉ kiểm tra và xuất file review tại `storage/app/import-review/pl2_review.csv`, không ghi CSDL). Thêm `--commit` để ghi thật vào CSDL. | Đưa tất cả thiết bị mới vào khoa `CHUA-PHAN-KHOA`. Khi chạy lại với `--commit` trên mã đã có, giữ nguyên khoa, tình trạng, trạng thái, serial và hồ sơ đã rà soát. |
| `php artisan equipment:reconcile-kqrs {file}` | Đọc file Excel báo cáo kết quả rà soát và in bảng đối soát so với dữ liệu hồ sơ trong CSDL. | Chỉ đọc và hiển thị báo cáo trên terminal, không sửa đổi CSDL. |
| `php artisan equipment:generate-sample-import` | Tạo file Excel mẫu tại `storage/app/samples/sample_import.xlsx` (gồm 6 dòng hợp lệ và 4 dòng mẫu lỗi). | Dùng để thử nghiệm tính năng nhập Excel trên giao diện web. |
| `php artisan db:backup {--keep=5}` | Sao lưu file CSDL SQLite vào `storage/app/backups/` và xóa bớt bản cũ. | Chỉ áp dụng cho kết nối SQLite mặc định tại `database/database.sqlite`. |

### 8. Chạy kiểm thử (Tests)
Chạy tại thư mục gốc dự án (dùng SQLite `:memory:` độc lập, không ảnh hưởng dữ liệu thật):
```bash
composer test
# Hoặc chạy trực tiếp:
php artisan test
```

### 9. Cấu trúc thư mục dự án

```text
ql_thietbi/
├── app/
│   ├── Console/Commands/       # Lệnh Artisan (backup, import PL2, đối soát KQRS, tạo file mẫu)
│   ├── Enums/                  # Trạng thái thiết bị, tình trạng, loại hồ sơ, kết quả kiểm kê
│   ├── Exports/                # Xuất Excel/CSV (thiết bị, báo cáo hồ sơ, chênh lệch kiểm kê)
│   ├── Http/
│   │   ├── Controllers/        # Controller in nhãn QR, xuất PDF/Excel kiểm kê
│   │   ├── Middleware/         # CheckDepartmentScope (kiểm tra khóa tài khoản & phạm vi khoa)
│   │   └── Requests/           # Form Request validation (thiết bị, danh mục hệ thống)
│   ├── Imports/                # Đọc file Excel nhập thiết bị
│   ├── Livewire/               # Các màn hình Livewire (Auth, Equipment, Inventory, Users, AuditLogs, Reports, MasterDataIndex)
│   ├── Models/                 # Eloquent Models (Equipment, Department, InventorySession, AuditLog, User...)
│   ├── Observers/              # AuditableObserver (tự động ghi nhận Audit Log)
│   ├── Policies/               # Phân quyền chi tiết (EquipmentPolicy, EquipmentDocumentPolicy)
│   ├── Providers/              # Đăng ký Observer và cấu hình ứng dụng
│   └── Services/               # Logic dùng chung (EquipmentConditionService, EquipmentImportValidator, InventoryReportService)
├── bootstrap/                  # Khởi tạo ứng dụng Laravel và cấu hình middleware
├── config/                     # Cấu hình app, database, session, permission, dompdf
├── database/
│   ├── factories/              # Factory tạo dữ liệu phục vụ kiểm thử
│   ├── migrations/             # Các file migration tạo và cập nhật cấu trúc bảng
│   ├── seeders/                # Seeder dữ liệu nền (khoa/phòng, vai trò/quyền, tài khoản mẫu)
│   └── database.sqlite         # File cơ sở dữ liệu SQLite
├── lang/vi/                    # Bản dịch tiếng Việt (auth, validation)
├── public/                     # Thư mục gốc web & tài nguyên tĩnh đã biên dịch (public/build/)
├── resources/
│   ├── css/, js/               # CSS (Tailwind) & JS (khởi tạo html5-qrcode quét QR)
│   └── views/
│       ├── components/layouts/ # Layout chính (app), di động (/q/{code} - mobile) và in nhãn (print)
│       ├── equipment/          # View in nhãn QR
│       ├── livewire/           # Giao diện Blade của các component Livewire
│       └── pdf/                # Mẫu xuất PDF danh sách thiết bị & biên bản kiểm kê
├── routes/
│   ├── web.php                 # Toàn bộ route web của hệ thống
│   └── console.php             # Định nghĩa lệnh console
├── storage/                    # Nhật ký (logs/), bản sao lưu (app/backups/), file mẫu & review import
└── tests/
    ├── Feature/                # Kiểm thử chức năng, phân quyền 5 vai trò & smoke test
    └── Unit/                   # Kiểm thử đơn vị (EquipmentImportValidatorTest)
```

---

## 🔧 Xử Lý Sự Cố Thường Gặp

| Hiện tượng | Nguyên nhân | Cách xử lý |
|---|---|---|
| Lỗi `ViteException: Unable to locate file in Vite manifest` | Chưa build giao diện frontend (`public/build/manifest.json` chưa có). | Chạy `npm install && npm run build` tại thư mục gốc dự án. |
| Quét mã QR trên nhãn in ra bị trỏ về `http://localhost:8000/...` | Biến `APP_URL` trong `.env` vẫn để giá trị mặc định `localhost`. | Cập nhật `APP_URL` đúng IP/tên miền máy chủ, chạy `php artisan config:clear` và in lại nhãn QR. |
| Lỗi `SQLSTATE[HY000]: General error: 5 database is locked` | Nhiều tiến trình ghi đồng thời vào file SQLite hoặc tiến trình trước bị treo. | Kiểm tra không có tiến trình import/backup chạy song song; thử lại thao tác; cân nhắc bật WAL mode cho SQLite nếu tải cao. |
| Không mở được Camera khi bấm quét QR trong màn Kiểm kê | Trình duyệt chặn quyền camera trên kết nối HTTP thường (không phải `localhost`). | Cấu hình HTTPS cho máy chủ web, hoặc dùng tạm tính năng nhập tay mã thiết bị / máy quét mã vạch USB. |
| Tải lên file Excel bị từ chối hoặc báo lỗi ngày `31/02/2025` | File vượt quá `5 MB`, vượt quá `500 dòng`, sai đuôi `.xlsx/.xls` hoặc ngày không hợp lệ theo lịch. | Chia nhỏ file dưới 500 dòng, kiểm tra định dạng ngày đúng `dd/mm/yyyy` và ngày tồn tại thực tế. |

---

## ⚠️ Hạn Chế Đã Biết & Chức Năng Chưa Có

- **Chưa có trong phiên bản hiện tại (Phase 1)**:
  - Quy trình quản lý bảo trì, sửa chữa, thay thế linh kiện.
  - Lịch nhắc và quản lý kiểm định / hiệu chuẩn định kỳ.
  - Tính khấu hao tài sản, hạch toán kế toán, quản lý kho vật tư tiêu hao và mua sắm đấu thầu.
  - Tải lên và lưu trữ file scan đính kèm (PDF/ảnh) cho 16 loại hồ sơ thiết bị (hiện mới lưu trạng thái Có/Không, hạn hiệu lực và ghi chú).
  - Giao diện xem danh sách và khôi phục thiết bị đã xóa mềm (`Soft Deleted`).
  - Lịch chạy sao lưu CSDL tự động sẵn trong `routes/console.php` (CNTT cần cấu hình Cron/Task Scheduler bên ngoài).
  - Quy trình duyệt điều chuyển thiết bị nhiều bước giữa các khoa (hiện cập nhật trực tiếp và ghi lịch sử).
- **Giới hạn kỹ thuật hiện tại**:
  - Xuất PDF danh sách thiết bị giới hạn tối đa **200 thiết bị/lần** để tránh quá tải bộ nhớ `dompdf`.
  - Lệnh `equipment:reconcile-kqrs` ánh xạ cả hai cột Tài liệu kỹ thuật (`E`) và Hướng dẫn sử dụng (`F`) về chung một loại hồ sơ `TAI_LIEU_KY_THUAT_HDSD`.

---

## 📝 Quy Trình Cập Nhật Tài Liệu & Liên Hệ

- **Cập nhật tài liệu**: Khi bổ sung quyền, thay đổi cấu trúc cột Excel, thêm lệnh Artisan hoặc biến môi trường mới, cần cập nhật đồng thời vào `README.md` và chạy `composer test` để xác nhận trước khi bàn giao.
- **Thông tin liên hệ hỗ trợ**:
  - Đơn vị quản lý thiết bị: `[ĐIỀN: Tên Phòng Vật tư – TBYT / Email nội bộ]`
  - Bộ phận CNTT / Quản trị hệ thống: `[ĐIỀN: Họ tên / SĐT nội bộ / Email hỗ trợ CNTT]`
  - Địa chỉ máy chủ nội bộ: `[ĐIỀN: URL truy cập chính thức của hệ thống]`
