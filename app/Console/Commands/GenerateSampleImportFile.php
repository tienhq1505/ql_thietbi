<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateSampleImportFile extends Command
{
    protected $signature = 'equipment:generate-sample-import';

    protected $description = 'Tạo file Excel mẫu (storage/app/samples/sample_import.xlsx) phục vụ test Import';

    public function handle(): int
    {
        $this->info('Đang đọc dữ liệu danh mục từ cơ sở dữ liệu...');

        $model = EquipmentModel::first();
        $dept = Department::first();
        $supplier = Supplier::first();
        $location = $dept ? Location::where('department_id', $dept->id)->first() : Location::first();

        $modelCode = $model ? $model->code : 'PHIL-MX450';
        $deptCode = $dept ? $dept->code : 'KCC';
        $locCode = $location ? $location->code : '';
        $supplierCode = $supplier ? $supplier->code : 'NCC-VIETMED';

        $dir = storage_path('app/samples');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh sách thiết bị');

        $headers = [
            'Mã thiết bị',
            'Tên thiết bị',
            'Mã model',
            'Mã khoa phòng',
            'Mã vị trí',
            'Số serial',
            'Năm sản xuất',
            'Xuất xứ',
            'Ngày nhập',
            'Ngày sử dụng',
            'Giá mua',
            'Nguồn kinh phí',
            'Mã nhà cung cấp',
            'Hạn bảo hành',
            'Người phụ trách',
            'Trạng thái',
            'Tình trạng',
            'Ghi chú',
        ];

        $sheet->fromArray([$headers], null, 'A1');

        $rows = [
            // 6 dòng HỢP LỆ
            ['EQ-SMP-001', 'Máy theo dõi bệnh nhân 5 thông số', $modelCode, $deptCode, $locCode, 'SN-SMP-001', 2023, 'Mỹ', '15/01/2023', '01/02/2023', 150000000, 'Ngân sách nhà nước', $supplierCode, '15/01/2026', 'Nguyễn Văn A', 'Đang sử dụng', 'Đang hoạt động', 'Thiết bị mới 100%'],
            ['EQ-SMP-002', 'Máy thở cao cấp', $modelCode, $deptCode, $locCode, 'SN-SMP-002', 2022, 'Đức', '10/05/2022', '15/05/2022', 450000000, 'Dự án ODA', $supplierCode, '10/05/2025', 'Trần Thị B', 'Đang sử dụng', 'Cần theo dõi', 'Bảo trì định kỳ'],
            ['EQ-SMP-003', 'Máy đo điện tim 12 kênh', $modelCode, $deptCode, $locCode, 'SN-SMP-003', 2024, 'Nhật Bản', '01/03/2024', '05/03/2024', 85000000, 'Quỹ phát triển sự nghiệp', $supplierCode, '01/03/2026', 'Lê Văn C', 'Dự phòng', 'Đang hoạt động', 'Máy dự phòng phòng cấp cứu'],
            ['EQ-SMP-004', 'Bơm tiêm điện tự động', $modelCode, $deptCode, $locCode, 'SN-SMP-004', 2023, 'Hàn Quốc', '20/08/2023', '25/08/2023', 28000000, 'Ngân sách nhà nước', $supplierCode, '20/08/2025', 'Phạm Văn D', 'Đang sử dụng', 'Đang hoạt động', 'Thiết bị hoạt động tốt'],
            ['EQ-SMP-005', 'Máy siêu âm màu 4D', $modelCode, $deptCode, $locCode, 'SN-SMP-005', 2021, 'Nhật Bản', '12/11/2021', '01/12/2021', 890000000, 'Nguồn vốn vay', $supplierCode, '12/11/2024', 'Hoàng Thị E', 'Đang sử dụng', 'Cần bảo trì', 'Cần thay đầu dò siêu âm'],
            ['EQ-SMP-006', 'Máy X-quang di động', $modelCode, $deptCode, $locCode, 'SN-SMP-006', 2022, 'Mỹ', '05/09/2022', '10/09/2022', 650000000, 'Ngân sách nhà nước', $supplierCode, '05/09/2025', 'Vũ Văn F', 'Đang sử dụng', 'Đang hoạt động', 'Thiết bị di động khoa cấp cứu'],

            // 4 dòng CỐ TÌNH LỖI (để kiểm tra chức năng Preview & Bỏ qua dòng lỗi)
            ['', 'Máy thiếu mã thiết bị', $modelCode, $deptCode, $locCode, 'SN-SMP-ERR1', 2023, 'Đức', '01/01/2023', '05/01/2023', 50000000, 'Ngân sách', $supplierCode, '01/01/2025', 'Lỗi 1', 'Đang sử dụng', 'Đang hoạt động', 'LỖI: Thiếu mã thiết bị'],
            ['EQ-SMP-007', 'Máy trùng Serial với dòng 001', $modelCode, $deptCode, $locCode, 'SN-SMP-001', 2023, 'Mỹ', '10/01/2023', '15/01/2023', 70000000, 'Ngân sách', $supplierCode, '10/01/2025', 'Lỗi 2', 'Đang sử dụng', 'Đang hoạt động', 'LỖI: Trùng serial với dòng 1'],
            ['EQ-SMP-008', 'Máy sai định dạng ngày nhập', $modelCode, $deptCode, $locCode, 'SN-SMP-008', 2023, 'Mỹ', '2024-12-31', '01/01/2025', 60000000, 'Ngân sách', $supplierCode, '31/12/2025', 'Lỗi 3', 'Đang sử dụng', 'Đang hoạt động', 'LỖI: Ngày nhập sai dạng YYYY-MM-DD (chỉ chấp nhận dd/mm/yyyy)'],
            ['EQ-SMP-009', 'Máy sai mã model', 'MODEL-KHONG-TON-TAI-999', $deptCode, $locCode, 'SN-SMP-009', 2023, 'Mỹ', '01/01/2023', '05/01/2023', 40000000, 'Ngân sách', $supplierCode, '01/01/2025', 'Lỗi 4', 'Đang sử dụng', 'Đang hoạt động', 'LỖI: Model không có trong DB'],
        ];

        $sheet->fromArray($rows, null, 'A2');

        // Auto-size columns (A through R)
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filePath = $dir.'/sample_import.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $this->info("Đã tạo file Excel mẫu thành công tại: {$filePath}");

        return Command::SUCCESS;
    }
}
