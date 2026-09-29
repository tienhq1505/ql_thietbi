<?php

namespace Tests\Feature;

use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ReconcileKqrsCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $testExcelPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->testExcelPath = storage_path('framework/testing/test_kqrs_sample.xlsx');
        $this->createSampleKqrsExcel();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testExcelPath)) {
            @unlink($this->testExcelPath);
        }
        parent::tearDown();
    }

    private function createSampleKqrsExcel(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bổ sung');

        // Headers
        $sheet->setCellValue('A3', 'STT');
        $sheet->setCellValue('B3', 'Tên khoa /phòng');
        $sheet->setCellValue('C4', 'Lý lịch thiết bị');
        $sheet->setCellValue('D4', 'Quyết định/ biên bản bàn giao');
        $sheet->setCellValue('E4', 'Tài liệu kỹ thuật');
        $sheet->setCellValue('F4', 'Hướng dẫn sử dụng');
        $sheet->setCellValue('G4', 'Nhật ký sử dụng máy');
        $sheet->setCellValue('H4', 'Nội quy/ Quy trình vận hành');
        $sheet->setCellValue('I4', 'Hồ sơ bảo dưỡng');
        $sheet->setCellValue('J4', 'Hồ sơ điều chuyển/ thanh lý');
        $sheet->setCellValue('K4', 'Kiểm định');
        $sheet->setCellValue('L4', 'Hiệu chuẩn');
        $sheet->setCellValue('M4', 'Phân công người phụ trách');

        // Row 5: Khoa 1
        $sheet->setCellValue('A5', 1);
        $sheet->setCellValue('B5', 'Khoa Gây mê hồi sức');
        $sheet->setCellValue('C5', '24/33');
        $sheet->setCellValue('D5', '0/33');
        $sheet->setCellValue('E5', '12/33');
        $sheet->setCellValue('F5', '3/33');
        $sheet->setCellValue('G5', '0/33');
        $sheet->setCellValue('H5', '31/33');
        $sheet->setCellValue('I5', '0/33');
        $sheet->setCellValue('M5', 'Duyệt : 0901234567'); // Sensitive data column M

        // Row 6: Khoa 2
        $sheet->setCellValue('A6', 2);
        $sheet->setCellValue('B6', 'Khoa Thận nhân tạo');
        $sheet->setCellValue('C6', '36/36');
        $sheet->setCellValue('D6', '36/36');
        $sheet->setCellValue('E6', '36/36');
        $sheet->setCellValue('F6', '36/36');
        $sheet->setCellValue('G6', '36/36');
        $sheet->setCellValue('H6', '36/36');
        $sheet->setCellValue('I6', '36/36');
        $sheet->setCellValue('M6', 'Chung Anh : 0987654321');

        // Row 7: TỔNG (Vô tình ghi 261/280 để test cảnh báo)
        $sheet->setCellValue('B7', 'TỔNG');
        $sheet->setCellValue('C7', '261/280');
        $sheet->setCellValue('D7', '36/69');
        $sheet->setCellValue('E7', '48/69');

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->testExcelPath);
    }

    public function test_reconcile_command_fails_when_file_does_not_exist(): void
    {
        $this->artisan('equipment:reconcile-kqrs', [
            'file' => 'invalid/file/path.xlsx',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain('File không tồn tại');
    }

    public function test_reconcile_command_runs_successfully_and_prints_report(): void
    {
        $this->artisan('equipment:reconcile-kqrs', [
            'file' => $this->testExcelPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('BÁO CÁO ĐỐI SOÁT HỒ SƠ THIẾT BỊ TỪ KQRS.XLSX VỚI CSDL MEMS')
            ->expectsOutputToContain('Khoa Gây mê hồi sức')
            ->expectsOutputToContain('Khoa Thận nhân tạo')
            ->expectsOutputToContain('Cột J')
            ->expectsOutputToContain('CHƯA RÀ SOÁT');
    }

    public function test_reconcile_command_warns_on_total_row_discrepancy(): void
    {
        $this->artisan('equipment:reconcile-kqrs', [
            'file' => $this->testExcelPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('CẢNH BÁO SAI LỆCH DÒNG TỔNG NỘI BỘ KQRS - Cột "Lý lịch thiết bị"');
    }

    public function test_reconcile_command_does_not_print_or_store_column_m(): void
    {
        $this->artisan('equipment:reconcile-kqrs', [
            'file' => $this->testExcelPath,
        ])
            ->assertExitCode(0)
            ->doesntExpectOutputToContain('0901234567')
            ->doesntExpectOutputToContain('0987654321')
            ->doesntExpectOutputToContain('Duyệt')
            ->doesntExpectOutputToContain('Chung Anh');
    }
}
