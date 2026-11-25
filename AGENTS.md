<!-- MEMS PROJECT CONTEXT FOR CLAUDE -->
# BỐI CẢNH DỰ ÁN MEMS (CHO CLAUDE)
Xem chi tiết toàn bộ nghiệp vụ, phân quyền, schema và tiến độ tại: [PROJECT_CONTEXT_FOR_CLAUDE.md](PROJECT_CONTEXT_FOR_CLAUDE.md)

### Lệnh chạy kiểm tra bắt buộc trên máy local (Windows):
- **PHP Path**: `C:\php8\php.exe`
- **Composer Path**: `C:\php8\composer.bat`
- **Chạy toàn bộ Test**: `C:\php8\php.exe vendor/phpunit/phpunit/phpunit` (hiện tại PASS 100% 196/196 tests)
- **Chạy Migrate & Seed**: `C:\php8\php.exe artisan migrate:fresh --seed`
- **Chạy Server**: `C:\php8\php.exe artisan serve`

### Quy tắc bất di bất dịch:
1. Mọi thay đổi cấu trúc bảng phải tạo **migration mới**, không sửa migration cũ.
2. Tuyệt đối không đưa thông tin cá nhân từ `data-real/` vào seeder, test, log hay code.
3. Sau khi code, luôn chạy `phpunit` để đảm bảo 100% pass thật trước khi báo cáo hoàn thành.

### QUY TẮC BẮT BUỘC cho mọi thay đổi:
- Đọc CLAUDE.md/AGENTS.md và tuân thủ convention hiện có. Không thêm package mới, không đổi kiến trúc thư mục nếu chưa hỏi.
- Mọi thay đổi schema phải là MIGRATION MỚI (không sửa migration cũ đã chạy).
- Sau mỗi bước: chạy `C:\php8\php.exe artisan migrate:fresh --seed` và `C:\php8\php.exe artisan test`, báo kết quả THẬT (pass/fail), không suy đoán.
- Viết/cập nhật test cho mọi logic mới.
- Dữ liệu thật nằm trong `data-real/` (đã gitignore). KHÔNG chép tên người, số điện thoại từ file thật vào seeder, test, log hay commit.
- Cuối mỗi bước: liệt kê file đã tạo/sửa và cách chạy thử.

### Tiến độ & Route mới nhất (Hoàn thành gói sửa lỗi & tối ưu Items A–I):
- **Kiểm thử**: PASS 100% 196/196 tests (882 assertions).
- **Gói sửa lỗi & tối ưu A–I hoàn thành**:
  - **A**: `/equipment/{id}/edit` khắc phục 500 do `$conditions`, validation dùng `Rule::unique` ignore id cho `code` và `serial_number`, loại bỏ `setRouteResolver`.
  - **B**: `EquipmentExport::query(): Builder`, chặn formula injection chuỗi bắt đầu bằng `=`, `+`, `-`, `@` (thoát bằng `'`) cho cả xlsx và csv trên `EquipmentExport`, `DocumentReportExport`, `InventoryDiscrepancyExport`.
  - **C**: `ImportPl2EquipmentCommand`: không dùng `updateOrCreate` ghi đè (giữ khoa, tình trạng, trạng thái, serial, hồ sơ đã rà soát); `parseDocStatus` xét "không áp dụng", "không", "chưa" trước "có"; dry-run không ghi CSDL; mật khẩu ngẫu nhiên cho `system-importer`.
  - **D**: `EquipmentImport`: bọc ghi trong `DB::transaction` (lỗi chuyển batch thành `failed`), kiểm tra lại cả mã lẫn serial trùng lúc xác nhận, chặt chẽ ngày tháng (31/02/2025 báo lỗi).
  - **E**: Validate kiểm tra cả bản ghi đã xóa mềm cho Danh mục/Hãng/Model/Khoa/Vị trí/NCC và email User, báo lỗi thân thiện thay vì 500.
  - **F**: Ghi audit log khi admin reset mật khẩu (không lưu mật khẩu); đổi khoa ở `EquipmentEdit` ghi `EquipmentDepartmentHistory`.
  - **G**: `APP_LOCALE=vi` và `APP_FALLBACK_LOCALE=vi` trong `.env` và `.env.example`, thông báo validate tiếng Việt chuẩn.
  - **H**: Chặn sửa/xóa khoa `CHUA-PHAN-KHOA` trong `MasterDataIndex`; `EquipmentShow::saveDocument` validate `editingDocType` theo `DocumentTypeEnum` tránh `ValueError`.
  - **I**: Smoke test toàn diện `SmokeTest.php`: mọi route cho 5 vai trò không trả về 500.
- **Module 6d hoàn thành**: Quét QR bằng camera điện thoại mở thẳng thẻ thông tin máy (`/q/{code}`). Layout riêng cho di động (`components.layouts.mobile`) tinh gọn, không có sidebar `<aside>`, thẻ thiết bị đặt ngay đầu trang; route `/q/{code}` khai báo duy nhất; nhãn in QR bổ sung dòng chữ ngắn `/q/{code}` cỡ nhỏ không tràn nhãn. Bắt buộc đăng nhập mới xem được thông tin (redirect intended), phân quyền xem theo khoa/BGĐ (EquipmentPolicy::view), thẻ máy tối ưu di động chữ lớn không hiển thị giá mua và nguồn kinh phí, nút "Đổi tình trạng" to dễ bấm cho người có quyền updateCondition (sử dụng EquipmentConditionService ghi lịch sử), nhãn QR chứa đường dẫn URL đầy đủ kích thước ≥25mm, màn quét kiểm kê (`InventoryShow` + `app.js`) nhận diện song song cả đường dẫn URL lẫn mã thô cũ, ô ghi nhớ đăng nhập được chọn sẵn mặc định và hỗ trợ cấu hình `SESSION_LIFETIME`.
- **Module 8b (Sửa Module Quản lý Người dùng) hoàn thành**: Kiểm tra `$user->can('users.manage')` ở đầu từng hành động (createUser, updateUser, resetPassword, toggleStatus, deleteUser, openEditModal, openResetModal); Ghi Audit Log tường minh khi tạo user (kèm role + khoa), đổi vai trò (cũ → mới), đổi khoa; `isLastAdmin` chỉ coi admin cuối cùng khi đối tượng là admin đang hoạt động (status active) và là admin hoạt động duy nhất.
- **Module Người dùng & Đổi mật khẩu hoàn thành**: Quản lý tài khoản toàn diện `/users` (tìm kiếm, lọc vai trò/khoa/trạng thái, phân 5 vai trò, using_dept bắt buộc gán khoa, khóa/mở khóa vô hiệu phiên hiện tại, đặt lại mật khẩu, xóa mềm bảo toàn audit log, chặn tự hại & bảo vệ admin cuối cùng) và `/profile/password` (đổi mật khẩu cá nhân cho mọi user đã đăng nhập).
- **Module 6c hoàn thành**: Báo cáo đối soát kiểm kê, Xuất biên bản kiểm kê PDF (dompdf, font DejaVu Sans tiếng Việt), Xuất Excel chênh lệch (maatwebsite/excel), Service dùng chung `InventoryReportService`. Tên bệnh viện trong PDF đọc từ `config('app.hospital_name')` (`APP_HOSPITAL_NAME`).
- **Các route mới**:
  1. `GET /q/{code}` (`equipment.scan`) - Quét QR mở thẻ thiết bị di động (yêu cầu `auth`)
  2. `GET /users` (`users.index`) - Quản lý người dùng (quyền `users.manage`, chỉ admin)
  3. `GET /profile/password` (`profile.password`) - Đổi mật khẩu cá nhân
  4. `GET /inventory/{session}/report` (`inventory.report`) - Báo cáo đối soát & tiến độ
  5. `GET /inventory/{session}/export-pdf` (`inventory.export-pdf`) - Xuất biên bản PDF
  6. `GET /inventory/{session}/export-excel` (`inventory.export-excel`) - Xuất Excel chênh lệch
---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy và scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
