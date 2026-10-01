<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
QUY TẮC BẮT BUỘC cho mọi thay đổi:
- Đọc CLAUDE.md/AGENTS.md và tuân thủ convention hiện có. Không thêm package mới, không đổi kiến trúc thư mục nếu chưa hỏi.
- Mọi thay đổi schema phải là MIGRATION MỚI (không sửa migration cũ đã chạy).
- Sau mỗi bước: chạy `php artisan migrate:fresh --seed` và `php artisan test`, báo kết quả THẬT (pass/fail), không suy đoán.
- Viết/cập nhật test cho mọi logic mới.
- Dữ liệu thật nằm trong data-real/ (đã gitignore). KHÔNG chép tên người, số điện thoại từ file thật vào seeder, test, log hay commit.
- Cuối mỗi bước: liệt kê file đã tạo/sửa và cách chạy thử.