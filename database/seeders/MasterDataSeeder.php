<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            ['code' => 'CAT-HSCC', 'name' => 'Thiết bị Hồi sức & Cấp cứu', 'description' => 'Máy thở, máy sốc tim, monitor theo dõi, bơm tiêm điện...'],
            ['code' => 'CAT-CDHA', 'name' => 'Thiết bị Chẩn đoán hình ảnh', 'description' => 'Máy X-quang, máy siêu âm, CT-Scanner, MRI...'],
            ['code' => 'CAT-TDCN', 'name' => 'Thiết bị Thăm dò chức năng', 'description' => 'Máy điện tim (ECG), máy điện não (EEG), máy đo hô hấp ký...'],
            ['code' => 'CAT-PT', 'name' => 'Thiết bị Phẫu thuật & Bàn mổ', 'description' => 'Bàn mổ đa năng, đèn mổ vô tuyến, máy dao điện cao tần...'],
            ['code' => 'CAT-XN', 'name' => 'Thiết bị Xét nghiệm', 'description' => 'Máy xét nghiệm sinh hóa, huyết học, miễn dịch, nước tiểu...'],
            ['code' => 'CAT-SN', 'name' => 'Thiết bị Sản - Nhi', 'description' => 'Lồng ấp sơ sinh, đèn chiếu vàng da, máy nghe tim thai...'],
            ['code' => 'CAT-TT', 'name' => 'Thiết bị Tiệt trùng & Hấp sấy', 'description' => 'Nồi hấp tiệt trùng, tủ sấy dụng cụ, máy rửa siêu âm...'],
            ['code' => 'CAT-TH', 'name' => 'Thiết bị Y tế tổng hợp', 'description' => 'Giường bệnh đa năng, xe đẩy y tế, máy hút dịch...'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['code']] = EquipmentCategory::updateOrCreate(['code' => $cat['code']], $cat);
        }

        // 2. Manufacturers
        $manufacturersData = [
            ['code' => 'MFG-PHILIPS', 'name' => 'Philips Healthcare', 'country' => 'Hà Lan', 'website' => 'https://www.philips.com'],
            ['code' => 'MFG-GE', 'name' => 'GE Healthcare', 'country' => 'Mỹ', 'website' => 'https://www.gehealthcare.com'],
            ['code' => 'MFG-SIEMENS', 'name' => 'Siemens Healthineers', 'country' => 'Đức', 'website' => 'https://www.siemens-healthineers.com'],
            ['code' => 'MFG-MINDRAY', 'name' => 'Mindray Bio-Medical', 'country' => 'Trung Quốc', 'website' => 'https://www.mindray.com'],
            ['code' => 'MFG-DRAGER', 'name' => 'Dräger Medical', 'country' => 'Đức', 'website' => 'https://www.draeger.com'],
            ['code' => 'MFG-NIHON', 'name' => 'Nihon Kohden', 'country' => 'Nhật Bản', 'website' => 'https://www.nihonkohden.com'],
            ['code' => 'MFG-OLYMPUS', 'name' => 'Olympus Medical', 'country' => 'Nhật Bản', 'website' => 'https://www.olympus-global.com'],
            ['code' => 'MFG-FUJIFILM', 'name' => 'Fujifilm Healthcare', 'country' => 'Nhật Bản', 'website' => 'https://www.fujifilm.com'],
            ['code' => 'MFG-BBRAUN', 'name' => 'B. Braun Medical', 'country' => 'Đức', 'website' => 'https://www.bbraun.com'],
            ['code' => 'MFG-SYSMEX', 'name' => 'Sysmex Corporation', 'country' => 'Nhật Bản', 'website' => 'https://www.sysmex.com'],
            ['code' => 'MFG-MEDTRONIC', 'name' => 'Medtronic', 'country' => 'Mỹ', 'website' => 'https://www.medtronic.com'],
            ['code' => 'MFG-ZEISS', 'name' => 'Carl Zeiss Meditec', 'country' => 'Đức', 'website' => 'https://www.zeiss.com'],
            ['code' => 'MFG-STRYKER', 'name' => 'Stryker Corporation', 'country' => 'Mỹ', 'website' => 'https://www.stryker.com'],
            ['code' => 'MFG-SONOSCAPE', 'name' => 'SonoScape Medical', 'country' => 'Trung Quốc', 'website' => 'https://www.sonoscape.com'],
            ['code' => 'MFG-CANON', 'name' => 'Canon Medical Systems', 'country' => 'Nhật Bản', 'website' => 'https://global.medical.canon'],
        ];

        $manufacturers = [];
        foreach ($manufacturersData as $mfg) {
            $manufacturers[$mfg['code']] = Manufacturer::updateOrCreate(['code' => $mfg['code']], $mfg);
        }

        // 3. Equipment Models
        $modelsData = [
            ['code' => 'MOD-EVITA-V800', 'name' => 'Máy thở cao cấp Evita V800', 'cat' => 'CAT-HSCC', 'mfg' => 'MFG-DRAGER', 'specs' => 'Thở xâm nhập & không xâm nhập, màn hình 18.5 inch, chế độ thở APRV, BiPap'],
            ['code' => 'MOD-MX450', 'name' => 'Monitor theo dõi bệnh nhân IntelliVue MX450', 'cat' => 'CAT-HSCC', 'mfg' => 'MFG-PHILIPS', 'specs' => 'Đo 5 thông số (ECG, SpO2, NIBP, Resp, Temp), màn hình cảm ứng 12 inch'],
            ['code' => 'MOD-uMEC12', 'name' => 'Monitor bệnh nhân uMEC12', 'cat' => 'CAT-HSCC', 'mfg' => 'MFG-MINDRAY', 'specs' => 'Đo SpO2, ECG, NIBP, Nhịp thở, Nhiệt độ, pin dự phòng 4 giờ'],
            ['code' => 'MOD-BENEVIEW-T8', 'name' => 'Monitor trung tâm BeneView T8', 'cat' => 'CAT-HSCC', 'mfg' => 'MFG-MINDRAY', 'specs' => 'Theo dõi đa thông số chuyên sâu ICU, màn hình 19 inch'],
            ['code' => 'MOD-PERFUSOR-SPACE', 'name' => 'Bơm tiêm điện Perfusor Space', 'cat' => 'CAT-HSCC', 'mfg' => 'MFG-BBRAUN', 'specs' => 'Tốc độ 0.01 - 999.9 ml/h, bảo vệ quá liều, kết nối wifi xếp chồng'],
            ['code' => 'MOD-VOLUSON-E10', 'name' => 'Máy siêu âm màu 4D Voluson E10', 'cat' => 'CAT-CDHA', 'mfg' => 'MFG-GE', 'specs' => 'Công nghệ HDlive, 4 cổng đầu dò, chuyên khoa Sản phụ khoa & Tim mạch'],
            ['code' => 'MOD-FDR-SMART', 'name' => 'Máy X-quang kỹ thuật số FDR Smart f', 'cat' => 'CAT-CDHA', 'mfg' => 'MFG-FUJIFILM', 'specs' => 'Tấm cảm biến FPD không dây 14x17 inch, công suất phát 50kW'],
            ['code' => 'MOD-SOMATOM-GO', 'name' => 'Máy chụp cắt lớp vi tính SOMATOM Go.Up 32 dãy', 'cat' => 'CAT-CDHA', 'mfg' => 'MFG-SIEMENS', 'specs' => '32 lát cắt/vòng quay, công nghệ giảm liều tia CARE Dose4D'],
            ['code' => 'MOD-CARDIOFAX-M', 'name' => 'Máy điện tim 12 kênh Cardiofax M ECG-2350', 'cat' => 'CAT-TDCN', 'mfg' => 'MFG-NIHON', 'specs' => 'Màn hình màu LCD 7 inch, tự động phân tích kết quả ECAPS 18C'],
            ['code' => 'MOD-XN-550', 'name' => 'Máy xét nghiệm huyết học tự động XN-550', 'cat' => 'CAT-XN', 'mfg' => 'MFG-SYSMEX', 'specs' => '26 thông số huyết học, công suất 60 mẫu/giờ, công nghệ Laser huỳnh quang'],
            ['code' => 'MOD-BS-240', 'name' => 'Máy xét nghiệm sinh hóa tự động BS-240', 'cat' => 'CAT-XN', 'mfg' => 'MFG-MINDRAY', 'specs' => 'Công suất 200 xét nghiệm/giờ, 80 vị trí mẫu bệnh phẩm'],
            ['code' => 'MOD-OPMI-LUMERA', 'name' => 'Kính hiển vi phẫu thuật OPMI Lumera 700', 'cat' => 'CAT-PT', 'mfg' => 'MFG-ZEISS', 'specs' => 'Công nghệ chiếu sáng StereoCoaxial, phóng đại tự động liên tục'],
            ['code' => 'MOD-ENDOSCOPY-EVIS', 'name' => 'Hệ thống nội soi tiêu hóa EVIS EXERA III', 'cat' => 'CAT-PT', 'mfg' => 'MFG-OLYMPUS', 'specs' => 'Công nghệ dải tần ánh sáng hẹp NBI chẩn đoán ung thư sớm'],
            ['code' => 'MOD-ISOLETTE-8000', 'name' => 'Lồng ấp sơ sinh Isolette 8000 plus', 'cat' => 'CAT-SN', 'mfg' => 'MFG-DRAGER', 'specs' => 'Kiểm soát nhiệt độ, độ ẩm tự động, tích hợp cân điện tử nội bộ'],
            ['code' => 'MOD-VACUKLAV-41B', 'name' => 'Nồi hấp tiệt trùng Vacuklav 41 B+ 18L', 'cat' => 'CAT-TT', 'mfg' => 'MFG-DRAGER', 'specs' => 'Tiệt trùng hơi nước Class B chân không hút 3 lần, ghi dữ liệu SD card'],
        ];

        foreach ($modelsData as $mod) {
            EquipmentModel::updateOrCreate(['code' => $mod['code']], [
                'name' => $mod['name'],
                'category_id' => $categories[$mod['cat']]->id,
                'manufacturer_id' => $manufacturers[$mod['mfg']]->id,
                'specs' => $mod['specs'],
            ]);
        }

        // 4. Suppliers
        $suppliersData = [
            ['code' => 'SUP-HOANGLONG', 'name' => 'Công ty TNHH Thiết bị Y tế Hoàng Long', 'phone' => '024-38291029', 'email' => 'contact@hoanglongmed.com.vn', 'address' => 'Số 145 Nguyễn Chí Thanh, Q. Đống Đa, Hà Nội'],
            ['code' => 'SUP-VIETNHAT', 'name' => 'Công ty CP Đầu tư & Phát triển Y tế Việt Nhật', 'phone' => '028-39201888', 'email' => 'info@vietnhatmed.vn', 'address' => 'Số 82 Điện Biên Phủ, Phường 15, Q. Bình Thạnh, TP.HCM'],
            ['code' => 'SUP-THANGLONG', 'name' => 'Công ty TNHH Vật tư & TBYT Thăng Long', 'phone' => '024-37554920', 'email' => 'sales@thanglongmed.com', 'address' => 'Lô C2 KĐT Nam Trung Yên, Q. Cầu Giấy, Hà Nội'],
            ['code' => 'SUP-MYDINH', 'name' => 'Công ty CP Kỹ thuật Y tế Mỹ Đình', 'phone' => '024-37920111', 'email' => 'medicals@mydinh.vn', 'address' => 'Số 26 Phạm Hùng, Q. Nam Từ Liêm, Hà Nội'],
            ['code' => 'SUP-MEDIC', 'name' => 'Công ty TNHH Thương mại & Dịch vụ Y khoa Medic', 'phone' => '028-38302999', 'email' => 'cskh@medic-tech.vn', 'address' => 'Số 254 Hòa Hảo, Phường 4, Quận 10, TP.HCM'],
        ];

        foreach ($suppliersData as $sup) {
            Supplier::updateOrCreate(['code' => $sup['code']], $sup);
        }

        // 5. Locations (attached to Departments)
        $departments = Department::all()->keyBy('code');

        if ($departments->isNotEmpty()) {
            $locationsData = [
                ['code' => 'LOC-ICU-01', 'dept' => 'K-HSTC', 'name' => 'Phòng Bệnh nặng 1 (ICU)', 'building' => 'Tòa A', 'floor' => 'Tầng 2', 'room' => 'P.201'],
                ['code' => 'LOC-ICU-02', 'dept' => 'K-HSTC', 'name' => 'Phòng Bệnh nặng 2 (ICU)', 'building' => 'Tòa A', 'floor' => 'Tầng 2', 'room' => 'P.202'],
                ['code' => 'LOC-CC-01', 'dept' => 'K-CC', 'name' => 'Phòng Cấp cứu tổng hợp', 'building' => 'Tòa A', 'floor' => 'Tầng 1', 'room' => 'P.105'],
                ['code' => 'LOC-NOI-01', 'dept' => 'K-NOI', 'name' => 'Phòng Điều trị Khoa Nội 1', 'building' => 'Tòa B', 'floor' => 'Tầng 3', 'room' => 'P.304'],
                ['code' => 'LOC-NGOAI-01', 'dept' => 'K-NGOAI', 'name' => 'Phòng Hậu phẫu Khoa Ngoại', 'building' => 'Tòa B', 'floor' => 'Tầng 4', 'room' => 'P.402'],
                ['code' => 'LOC-SAN-01', 'dept' => 'K-SAN', 'name' => 'Phòng Sinh & Cấp cứu Sơ sinh', 'building' => 'Tòa C', 'floor' => 'Tầng 2', 'room' => 'P.210'],
                ['code' => 'LOC-CDHA-01', 'dept' => 'K-CDHA', 'name' => 'Phòng Chụp X-quang KTS', 'building' => 'Tòa A', 'floor' => 'Tầng 1', 'room' => 'P.112'],
                ['code' => 'LOC-CDHA-02', 'dept' => 'K-CDHA', 'name' => 'Phòng Siêu âm Màu 1', 'building' => 'Tòa A', 'floor' => 'Tầng 1', 'room' => 'P.115'],
                ['code' => 'LOC-XN-01', 'dept' => 'K-XN', 'name' => 'Phòng Xét nghiệm Huyết học', 'building' => 'Tòa A', 'floor' => 'Tầng 3', 'room' => 'P.301'],
            ];

            foreach ($locationsData as $loc) {
                if (isset($departments[$loc['dept']])) {
                    Location::updateOrCreate(['code' => $loc['code']], [
                        'name' => $loc['name'],
                        'department_id' => $departments[$loc['dept']]->id,
                        'building' => $loc['building'],
                        'floor' => $loc['floor'],
                        'room' => $loc['room'],
                    ]);
                }
            }
        }
    }
}
