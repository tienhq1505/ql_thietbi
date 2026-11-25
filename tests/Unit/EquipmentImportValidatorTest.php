<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Services\EquipmentImportValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentImportValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected EquipmentImportValidator $validator;

    protected EquipmentModel $model;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new EquipmentImportValidator;

        $category = EquipmentCategory::create(['code' => 'CAT-01', 'name' => 'Chẩn đoán hình ảnh']);
        $manufacturer = Manufacturer::create(['code' => 'MFG-01', 'name' => 'Philips']);

        $this->model = EquipmentModel::create([
            'code' => 'TEST-MODEL-01',
            'name' => 'Model Test 01',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);

        $this->department = Department::create([
            'code' => 'TEST-DEPT-01',
            'name' => 'Khoa Cấp Cứu Test',
        ]);
    }

    public function test_valid_row_passes_validation(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-VALID-001',
                'ten_thiet_bi' => 'Máy siêu âm test',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-VALID-001',
                'ngay_nhap' => '15/03/2024',
                'trang_thai' => 'Đang sử dụng',
                'tinh_trang' => 'Đang hoạt động',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['total_count']);
        $this->assertEquals(1, $result['valid_count']);
        $this->assertEquals(0, $result['error_count']);
        $this->assertEquals('valid', $result['rows'][0]['status']);
        $this->assertEmpty($result['rows'][0]['errors']);
        $this->assertEquals($this->model->id, $result['rows'][0]['data']['equipment_model_id']);
        $this->assertEquals($this->department->id, $result['rows'][0]['data']['department_id']);
    }

    public function test_row_with_missing_required_fields_fails(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => '',
                'ten_thiet_bi' => 'Máy thiếu mã',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => '',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertEquals('error', $result['rows'][0]['status']);
        $this->assertContains('Mã thiết bị không được để trống.', $result['rows'][0]['errors']);
    }

    public function test_row_with_duplicate_code_or_serial_in_db_fails(): void
    {
        Equipment::create([
            'code' => 'EQ-EXISTING',
            'name' => 'Thiết bị đã có',
            'equipment_model_id' => $this->model->id,
            'category_id' => $this->model->category_id,
            'manufacturer_id' => $this->model->manufacturer_id,
            'department_id' => $this->department->id,
            'serial_number' => 'SN-EXISTING',
        ]);

        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-EXISTING',
                'ten_thiet_bi' => 'Thiết bị mới trùng mã',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-EXISTING',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertTrue(str_contains(implode(' ', $result['rows'][0]['errors']), 'đã tồn tại trong hệ thống'));
    }

    public function test_duplicate_code_within_same_file_fails(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-DUP-FILE',
                'ten_thiet_bi' => 'Máy dòng 1',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-001',
            ],
            [
                'ma_thiet_bi' => 'EQ-DUP-FILE',
                'ten_thiet_bi' => 'Máy dòng 2 trùng mã dòng 1',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-002',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['valid_count']);
        $this->assertEquals(1, $result['error_count']);
        $this->assertContains("Mã thiết bị 'EQ-DUP-FILE' bị trùng lặp nhiều lần trong file.", $result['rows'][1]['errors']);
    }

    public function test_location_validation_matching_department(): void
    {
        $location = Location::create([
            'code' => 'LOC-CC-01',
            'name' => 'Phòng Hồi sức cấp cứu',
            'department_id' => $this->department->id,
        ]);

        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-LOC-01',
                'ten_thiet_bi' => 'Máy thở',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'ma_vi_tri' => 'LOC-CC-01',
                'so_serial' => 'SN-LOC-01',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['valid_count']);
        $this->assertEquals('valid', $result['rows'][0]['status']);
        $this->assertEquals($location->id, $result['rows'][0]['data']['location_id']);
    }

    public function test_location_validation_belonging_to_wrong_department_fails(): void
    {
        $otherDept = Department::create(['code' => 'TEST-DEPT-02', 'name' => 'Khoa Khác']);
        Location::create([
            'code' => 'LOC-KHAC-01',
            'name' => 'Phòng Khoa Khác',
            'department_id' => $otherDept->id,
        ]);

        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-LOC-ERR',
                'ten_thiet_bi' => 'Máy sai vị trí khoa',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'ma_vi_tri' => 'LOC-KHAC-01',
                'so_serial' => 'SN-LOC-ERR',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertContains("Vị trí 'LOC-KHAC-01' không thuộc khoa phòng 'TEST-DEPT-01'.", $result['rows'][0]['errors']);
    }

    public function test_invalid_date_format_fails(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-DATE-ERR',
                'ten_thiet_bi' => 'Máy sai định dạng ngày Y-m-d',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-DATE-ERR',
                'ngay_nhap' => '2024-12-31', // Định dạng YYYY-MM-DD không được chấp nhận
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertContains("Ngày nhập '2024-12-31' không đúng định dạng chuẩn (dd/mm/yyyy).", $result['rows'][0]['errors']);
    }

    public function test_misspelled_status_or_condition_fails(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-STATUS-ERR',
                'ten_thiet_bi' => 'Máy gõ sai chính tả',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-STATUS-ERR',
                'trang_thai' => 'Dang dung sai', // Sai chính tả
                'tinh_trang' => 'Hongg', // Sai chính tả
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertTrue(str_contains(implode(' ', $result['rows'][0]['errors']), "Trạng thái 'Dang dung sai' không hợp lệ"));
        $this->assertTrue(str_contains(implode(' ', $result['rows'][0]['errors']), "Tình trạng 'Hongg' không hợp lệ"));
    }

    public function test_vietnamese_price_parsing_formats(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-PRICE-01',
                'ten_thiet_bi' => 'Máy test giá VN 1',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-PRICE-01',
                'don_gia' => '15.000.000',
            ],
            [
                'ma_thiet_bi' => 'EQ-PRICE-02',
                'ten_thiet_bi' => 'Máy test giá VN 2',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-PRICE-02',
                'don_gia' => '15.000.000,50',
            ],
            [
                'ma_thiet_bi' => 'EQ-PRICE-03',
                'ten_thiet_bi' => 'Máy test giá không hợp lệ',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'so_serial' => 'SN-PRICE-03',
                'don_gia' => '15.000.000,abc',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(2, $result['valid_count']);
        $this->assertEquals(1, $result['error_count']);
        $this->assertEquals(15000000.0, $result['rows'][0]['data']['purchase_price']);
        $this->assertEquals(15000000.5, $result['rows'][1]['data']['purchase_price']);
        $this->assertTrue(str_contains(implode(' ', $result['rows'][2]['errors']), 'Giá mua'));
    }

    public function test_invalid_calendar_date_such_as_february_31_fails(): void
    {
        $rawRows = [
            [
                'ma_thiet_bi' => 'EQ-DATE-FEB31',
                'ten_thiet_bi' => 'Máy ngày 31 tháng 2',
                'ma_model' => 'TEST-MODEL-01',
                'ma_khoa_phong' => 'TEST-DEPT-01',
                'ngay_nhap' => '31/02/2025',
            ],
        ];

        $result = $this->validator->validateRows($rawRows);

        $this->assertEquals(1, $result['error_count']);
        $this->assertTrue(str_contains(implode(' ', $result['rows'][0]['errors']), "Ngày nhập '31/02/2025' không phải là ngày hợp lệ trong lịch."));
    }
}
