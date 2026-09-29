# MEMS - Hệ Thống Quản Lý Trang Thiết Bị Y Tế

Hệ thống quản lý tài sản, trang thiết bị y tế dành cho bệnh viện quy mô 200 giường (~1.000 thiết bị), được phát triển trên nền tảng **Laravel 13 + Livewire 4 + SQLite**.

---

## 🛠️ Công Nghệ Sử Dụng

- **Backend Framework:** Laravel 13.x (PHP 8.3+)
- **Frontend Framework:** Livewire 4 + Alpine.js
- **Styling:** Tailwind CSS v4
- **Database:** SQLite (Laravel ORM Eloquent)
- **Phân Quyền:** Spatie Laravel-Permission (Role & Permission)
- **Mã QR:** Simple Software IO QR Code
- **Excel Export/Import:** PhpSpreadsheet / Laravel Excel

---

## 🚀 Hướng Dẫn Cài Đặt (Local Environment)

### 1. Yêu cầu hệ thống
- PHP >= 8.3 (extensions: `pdo_sqlite`, `mbstring`, `openssl`, `gd`, `zip`)
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 2. Các bước cài đặt

```bash
# 1. Clone project và cài đặt PHP dependencies
composer install

# 2. Cài đặt Node dependencies
npm install

# 3. Tạo file môi trường .env (nếu chưa có)
cp .env.example .env

# 4. Generate Application Key
php artisan key:generate

# 5. Chạy Migration & Seed cơ sở dữ liệu thật (19 đơn vị, 5 tài khoản, 0 thiết bị)
php artisan migrate:fresh --seed

# 6. Khởi chạy server phát triển
php artisan serve
# Hoặc chạy Vite cho frontend assets (Terminal riêng)
npm run dev
```

---

## 📦 Luồng Dữ Liệu Thật vs Dữ Liệu Mẫu (Demo)

### 1. Chế độ Dữ liệu thật (Mặc định)
Chạy command tiêu chuẩn:
```bash
php artisan migrate:fresh --seed
```
- Khởi tạo **19 khoa/phòng** (16 khoa/phòng lâm sàng thật + 3 đơn vị hệ thống: *Phòng Vật tư-TBYT*, *Ban Giám đốc*, *Chưa phân khoa*).
- Khởi tạo **5 tài khoản hệ thống**.
- Khởi tạo **0 thiết bị** và 0 master data mẫu để chuẩn bị nhập dữ liệu thật.
- Các file dữ liệu thật được lưu tại thư mục `/data-real/` (đã được cấu hình `.gitignore` bảo vệ).

### 2. Chế độ Dữ liệu mẫu (Demo)
Khi cần dữ liệu giả định để kiểm thử giao diện & tính năng thiết bị:
```bash
# Seed danh mục & địa điểm mẫu
php artisan db:seed --class=DemoMasterDataSeeder

# Seed danh sách thiết bị mẫu (~110 thiết bị)
php artisan db:seed --class=DemoEquipmentSeeder
```

---

## 🔑 Tài Khoản Demo (Mật khẩu chung: `password`)

| Tên Role | Email Đăng Nhập | Khoa / Phòng Gán | Mô Tả Quyền Hạn |
|---|---|---|---|
| `admin` | `admin@mems.local` | *(Chưa gán)* | Toàn quyền hệ thống, quản lý tài khoản & phân quyền |
| `equipment_dept` | `vattu@mems.local` | *(Chưa gán)* | Quản lý master data, danh mục thiết bị, duyệt điều chuyển/thanh lý, kiểm kê |
| `technician` | `kythuat@mems.local` | *(Chưa gán)* | Xem thiết bị, cập nhật trạng thái kỹ thuật, hỗ trợ kiểm kê |
| `using_dept` | `khoa@mems.local` | Khoa Nội (`KNOI`) | Xem thiết bị thuộc khoa mình quản lý, đề xuất điều chuyển/sửa chữa |
| `directorate` | `giamdoc@mems.local` | *(Chưa gán)* | Xem báo cáo, dashboard tổng quan toàn bệnh viện |

---

## 📋 Cấu Trúc Phân Quyền (Spatie Permissions)

- **`admin`**: Toàn quyền (`*`)
- **`equipment_dept`**: `equipment.*`, `master_data.manage`, `inventory.*`, `audit_log.view`
- **`technician`**: `equipment.view`, `equipment.status_update`, `inventory.scan`
- **`using_dept`**: `equipment.view_department`, `equipment.request_transfer`
- **`directorate`**: `dashboard.view_all`, `reports.view`

---

## 🧪 Chạy Kiểm Thử (Tests)

```bash
php artisan test
```
