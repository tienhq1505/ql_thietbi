<?php

namespace Tests\Feature;

use App\Console\Commands\ImportPl2EquipmentCommand;
use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\Manufacturer;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionMethod;
use Tests\TestCase;

class ImportPl2EquipmentCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $testExcelPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);

        $this->testExcelPath = storage_path('framework/testing/test_pl2_sample.xlsx');
        $this->createSampleExcelFile();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testExcelPath)) {
            @unlink($this->testExcelPath);
        }
        parent::tearDown();
    }

    private function createSampleExcelFile(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Row 1..5: Headers
        $sheet->setCellValue('A1', 'PHỤ LỤC 2');
        $sheet->setCellValue('A5', 'STT');
        $sheet->setCellValue('B5', 'Tên thiết bị');
        $sheet->setCellValue('C5', 'ĐVT');
        $sheet->setCellValue('D5', 'Số lượng');
        $sheet->setCellValue('E5', 'Model, hãng, nước, năm SX');
        $sheet->setCellValue('F5', 'Hợp đồng, hóa đơn');
        $sheet->setCellValue('G5', 'Biên bản bàn giao');
        $sheet->setCellValue('H5', 'Hồ sơ lưu hành');
        $sheet->setCellValue('I5', 'CO');
        $sheet->setCellValue('J5', 'CQ');
        $sheet->setCellValue('K5', 'Tờ khai hải quan');
        $sheet->setCellValue('L5', 'Tài liệu KT/HDSD');
        $sheet->setCellValue('M5', 'Công bố đủ ĐK mua bán');

        // Row 6 (STT 1): SL > 1 (2), Độ tin cậy CAO
        $sheet->setCellValue('A6', 1);
        $sheet->setCellValue('B6', 'Bàn mổ cơ đa năng');
        $sheet->setCellValue('C6', 'Cái');
        $sheet->setCellValue('D6', 2);
        $sheet->setCellValue('E6', '3008C Keling Trung Quốc 2022');
        $sheet->setCellValue('F6', 'Có');
        $sheet->setCellValue('G6', 'Có');
        $sheet->setCellValue('H6', 'Có');
        $sheet->setCellValue('I6', 'Có');
        $sheet->setCellValue('J6', 'Có');
        $sheet->setCellValue('K6', 'Có');
        $sheet->setCellValue('L6', 'Có');
        $sheet->setCellValue('M6', 'Có');

        // Row 7 (STT 2) & Row 8 (STT 3): Merged Cell F7:F8 "MÁY ĐẶT", Row 7 Độ tin cậy THẤP, Hồ sơ trống
        $sheet->setCellValue('A7', 2);
        $sheet->setCellValue('B7', 'Hệ thống PCR');
        $sheet->setCellValue('C7', 'Bộ');
        $sheet->setCellValue('D7', 1);
        $sheet->setCellValue('E7', 'Hệ thống PCR'); // Low confidence string (no country/model)
        $sheet->setCellValue('G7', 'Có');
        // H7..M7 left empty (chua_ra_soat)

        $sheet->setCellValue('A8', 3);
        $sheet->setCellValue('B8', 'Dao mổ điện Zerone');
        $sheet->setCellValue('C8', 'Cái');
        $sheet->setCellValue('D8', 1);
        $sheet->setCellValue('E8', 'ZUES-400 ZERONE Hàn Quốc 2023');
        $sheet->setCellValue('G8', 'Có');
        $sheet->setCellValue('H8', 'Không');
        $sheet->setCellValue('I8', 'Không');

        // Merge cells F7:F8 with value "MÁY ĐẶT"
        $sheet->mergeCells('F7:F8');
        $sheet->setCellValue('F7', 'MÁY ĐẶT');

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->testExcelPath);
    }

    public function test_dry_run_mode_creates_review_csv_and_does_not_modify_database(): void
    {
        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('DRY-RUN (Chưa ghi CSDL)')
            ->expectsOutputToContain('Tổng số dòng STT đã đọc        : 3')
            ->expectsOutputToContain('Tổng số đơn vị thiết bị        : 4');

        $this->assertDatabaseCount('equipment', 0);
        $this->assertDatabaseCount('equipment_documents', 0);
        $this->assertDatabaseMissing('equipment_categories', ['code' => 'CAT-TH']);
        $this->assertDatabaseMissing('manufacturers', ['code' => 'MFG-UNKNOWN']);
        $this->assertDatabaseMissing('equipment_models', ['code' => 'MOD-UNKNOWN']);
        $this->assertFileExists(storage_path('app/import-review/pl2_review.csv'));
    }

    public function test_commit_mode_creates_equipment_documents_and_master_data(): void
    {
        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
            '--commit' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('COMMIT (Đã ghi CSDL)');

        // Expected 4 total equipment items (STT 1: qty 2, STT 2: qty 1, STT 3: qty 1)
        $this->assertDatabaseCount('equipment', 4);

        $eq1_1 = Equipment::where('code', 'TB-001-01')->firstOrFail();
        $eq1_2 = Equipment::where('code', 'TB-001-02')->firstOrFail();
        $this->assertNull($eq1_1->serial_number);
        $this->assertTrue($eq1_1->serial_pending);
        $this->assertEquals(OwnershipTypeEnum::MUA_SAM, $eq1_1->ownership_type);

        // Check merged cells "MÁY ĐẶT" for STT 2 and STT 3
        $eq2 = Equipment::where('code', 'TB-002-01')->firstOrFail();
        $this->assertEquals(OwnershipTypeEnum::MAY_DAT, $eq2->ownership_type);
        $this->assertEquals('Hệ thống PCR', $eq2->origin_model_text);

        // Check 8 document items created for eq2
        $this->assertDatabaseCount('equipment_documents', 32); // 4 equipment * 8 docs = 32

        $docF = EquipmentDocument::where('equipment_id', $eq2->id)
            ->where('document_type', DocumentTypeEnum::HOP_DONG_HOA_DON->value)
            ->firstOrFail();
        $this->assertEquals(DocumentStatusEnum::KHONG_AP_DUNG, $docF->status);

        $docH = EquipmentDocument::where('equipment_id', $eq2->id)
            ->where('document_type', DocumentTypeEnum::HO_SO_LUU_HANH->value)
            ->firstOrFail();
        $this->assertEquals(DocumentStatusEnum::CHUA_RA_SOAT, $docH->status);
    }

    public function test_idempotence_running_commit_twice_does_not_duplicate_records(): void
    {
        // Run 1
        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
            '--commit' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('equipment', 4);
        $this->assertDatabaseCount('equipment_documents', 32);

        // Run 2 (Second execution must be idempotent)
        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
            '--commit' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('equipment', 4);
        $this->assertDatabaseCount('equipment_documents', 32);
    }

    public function test_parse_doc_status_handles_khong_ap_dung_and_khong_before_co(): void
    {
        $command = new ImportPl2EquipmentCommand;
        $ref = new ReflectionMethod(ImportPl2EquipmentCommand::class, 'parseDocStatus');

        $this->assertEquals(DocumentStatusEnum::KHONG_AP_DUNG, $ref->invoke($command, 'không áp dụng'));
        $this->assertEquals(DocumentStatusEnum::KHONG_AP_DUNG, $ref->invoke($command, 'Khong ap dung'));
        $this->assertEquals(DocumentStatusEnum::KHONG, $ref->invoke($command, 'không có'));
        $this->assertEquals(DocumentStatusEnum::KHONG, $ref->invoke($command, 'khong'));
        $this->assertEquals(DocumentStatusEnum::CHUA_RA_SOAT, $ref->invoke($command, 'chưa có'));
        $this->assertEquals(DocumentStatusEnum::CHUA_RA_SOAT, $ref->invoke($command, 'chua'));
        $this->assertEquals(DocumentStatusEnum::CO, $ref->invoke($command, 'có'));
        $this->assertEquals(DocumentStatusEnum::CO, $ref->invoke($command, 'x'));
    }

    public function test_commit_preserves_existing_department_condition_status_serial_and_reviewed_documents(): void
    {
        $dept = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->firstOrFail();
        $cat = EquipmentCategory::create(['code' => 'CAT-TEST', 'name' => 'Cat test']);
        $mfg = Manufacturer::create(['code' => 'MFG-TEST', 'name' => 'Mfg test']);
        $model = EquipmentModel::create(['code' => 'MOD-TEST', 'name' => 'Mod test', 'category_id' => $cat->id, 'manufacturer_id' => $mfg->id]);

        $existing = Equipment::create([
            'code' => 'TB-001-01',
            'name' => 'Tên cũ',
            'department_id' => $dept->id,
            'condition' => EquipmentConditionEnum::CAN_BAO_TRI,
            'status' => EquipmentStatusEnum::THANH_LY,
            'serial_number' => 'SERIAL-GIU-NGUYEN',
            'serial_pending' => false,
            'equipment_model_id' => $model->id,
            'category_id' => $cat->id,
            'manufacturer_id' => $mfg->id,
            'ownership_type' => OwnershipTypeEnum::MUA_SAM,
        ]);

        $existingDoc = EquipmentDocument::create([
            'equipment_id' => $existing->id,
            'document_type' => DocumentTypeEnum::HOP_DONG_HOA_DON->value,
            'status' => DocumentStatusEnum::KHONG->value,
        ]);

        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
            '--commit' => true,
        ])->assertExitCode(0);

        $existing->refresh();
        // Giữ nguyên khoa, tình trạng, trạng thái, serial
        $this->assertEquals($dept->id, $existing->department_id);
        $this->assertEquals(EquipmentConditionEnum::CAN_BAO_TRI, $existing->condition);
        $this->assertEquals(EquipmentStatusEnum::THANH_LY, $existing->status);
        $this->assertEquals('SERIAL-GIU-NGUYEN', $existing->serial_number);
        $this->assertFalse($existing->serial_pending);

        // Giữ nguyên trạng thái hồ sơ đã rà soát
        $existingDoc->refresh();
        $this->assertEquals(DocumentStatusEnum::KHONG, $existingDoc->status);
    }

    public function test_system_importer_uses_random_password(): void
    {
        $this->artisan('equipment:import-pl2', [
            'file' => $this->testExcelPath,
            '--commit' => true,
        ])->assertExitCode(0);

        $importer = User::where('email', 'system-importer@mems.local')->first();
        if ($importer) {
            $this->assertFalse(Hash::check('password', $importer->password));
        }
    }
}
