<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Biên bản kiểm kê - {{ $session->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .hospital-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }
        .hospital-sub {
            font-size: 8pt;
            color: #64748b;
        }
        .motto-title {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
        }
        .motto-sub {
            text-align: center;
            font-size: 8pt;
            font-style: italic;
        }
        .title-block {
            text-align: center;
            margin: 15px 0 10px 0;
        }
        .main-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .draft-badge {
            display: inline-block;
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px dashed #ef4444;
            font-size: 9pt;
            font-weight: bold;
            padding: 3px 12px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .official-badge {
            display: inline-block;
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #86efac;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .info-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-card td {
            border: none;
            padding: 2px 4px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 15px 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #cbd5e1;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .result-khop { color: #16a34a; font-weight: bold; }
        .result-thieu { color: #dc2626; font-weight: bold; }
        .result-thua { color: #7c3aed; font-weight: bold; }
        .result-hong { color: #d97706; font-weight: bold; }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
            padding: 0 4px;
            width: 25%;
        }
        .sign-role {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .sign-note {
            font-size: 7.5pt;
            font-style: italic;
            color: #64748b;
        }
        .sign-space {
            height: 60px;
        }
        .sign-name {
            font-weight: bold;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 42pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.12);
            transform: rotate(-30deg);
            z-index: -1000;
            text-transform: uppercase;
            letter-spacing: 5px;
        }
    </style>
</head>
<body>
    @if(!$session->isCompleted())
        <div class="watermark">DỰ THẢO - CHƯA CHỐT</div>
    @endif

    <!-- Quốc hiệu & Tên đơn vị -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="hospital-title">{{ config('app.hospital_name', 'Bệnh viện') }}</div>
                <div class="hospital-sub">HỆ THỐNG QUẢN LÝ THIẾT BỊ Y TẾ</div>
            </td>
            <td style="width: 50%;">
                <div class="motto-title">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="motto-sub">Độc lập - Tự do - Hạnh phúc</div>
                <div style="text-align: center; font-size: 8pt; margin-top: 2px;">---------o0o---------</div>
            </td>
        </tr>
    </table>

    <!-- Tiêu đề biên bản -->
    <div class="title-block">
        <h1 class="main-title">
            BIÊN BẢN KIỂM KÊ TRANG THIẾT BỊ Y TẾ
        </h1>
        @if(!$session->isCompleted())
            <div class="draft-badge">BẢN DỰ THẢO - KỲ KIỂM KÊ CHƯA CHỐT (ĐANG THỰC HIỆN)</div>
        @else
            <div class="official-badge">BIÊN BẢN CHÍNH THỨC (ĐÃ CHỐT SỐ LIỆU)</div>
        @endif
    </div>

    <!-- Thông tin kỳ kiểm kê -->
    <div class="info-card">
        <table>
            <tr>
                <td style="width: 18%;" class="font-bold">Đợt kiểm kê:</td>
                <td style="width: 42%;">{{ $session->name }}</td>
                <td style="width: 18%;" class="font-bold">Phạm vi:</td>
                <td style="width: 22%;">{{ $session->scope_name }}</td>
            </tr>
            <tr>
                <td class="font-bold">Thời gian kiểm:</td>
                <td>{{ $session->start_date?->format('d/m/Y') }} đến {{ $session->end_date?->format('d/m/Y') }}</td>
                <td class="font-bold">Người lập:</td>
                <td>{{ $session->creator?->name ?? 'Hệ thống' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Ngày xuất biên bản:</td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
                <td class="font-bold">Trạng thái:</td>
                <td>
                    @if($session->isCompleted())
                        <span class="font-bold" style="color: #16a34a;">Đã chốt lúc {{ $session->completed_at?->format('H:i d/m/Y') }}</span>
                    @else
                        <span class="font-bold" style="color: #dc2626;">Chưa chốt</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Phần 1: Bảng tổng hợp số liệu đối soát -->
    <div class="section-title">I. BẢNG TỔNG HỢP SỐ LIỆU ĐỐI SOÁT</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 45%;">Chỉ tiêu kiểm kê</th>
                <th style="width: 18%;">Số lượng thiết bị</th>
                <th style="width: 15%;">Tỷ lệ (%)</th>
                <th style="width: 17%;">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-bold">1</td>
                <td><strong>Tổng số thiết bị trong danh mục kiểm kê</strong></td>
                <td class="text-center font-bold">{{ $stats['total'] }}</td>
                <td class="text-center font-bold">100%</td>
                <td class="text-center">Số liệu theo sổ sách</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Số lượng thiết bị kiểm kê <span class="result-khop">Khớp</span></td>
                <td class="text-center font-bold result-khop">{{ $stats['matched'] }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round(($stats['matched'] / $stats['total']) * 100, 1) : 0 }}%</td>
                <td class="text-center">Đúng khoa, đúng vị trí</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Số lượng thiết bị <span class="result-thieu">Thiếu</span> (chưa tìm thấy)</td>
                <td class="text-center font-bold result-thieu">{{ $stats['missing'] }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round(($stats['missing'] / $stats['total']) * 100, 1) : 0 }}%</td>
                <td class="text-center">Không thấy tại thực địa</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Số lượng thiết bị <span class="result-thua">Thừa</span> (ngoài danh mục / chưa phân khoa)</td>
                <td class="text-center font-bold result-thua">{{ $stats['excess'] }}</td>
                <td class="text-center">-</td>
                <td class="text-center">Máy xuất hiện tại khoa</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Số lượng thiết bị báo <span class="result-hong">Hỏng</span> / sự cố kỹ thuật</td>
                <td class="text-center font-bold result-hong">{{ $stats['broken'] }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round(($stats['broken'] / $stats['total']) * 100, 1) : 0 }}%</td>
                <td class="text-center">Cần bảo trì / sửa chữa</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Số lượng thiết bị chưa kiểm đếm</td>
                <td class="text-center font-bold" style="color: #64748b;">{{ $stats['untested'] }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round(($stats['untested'] / $stats['total']) * 100, 1) : 0 }}%</td>
                <td class="text-center">{{ $session->isCompleted() ? 'Đã kết thúc kỳ' : 'Đang tiếp tục quét' }}</td>
            </tr>
            <tr style="background-color: #f1f5f9;">
                <td colspan="2" class="text-right font-bold">Tiến độ kiểm kê hoàn tất:</td>
                <td colspan="3" class="font-bold" style="color: #1e3a8a;">
                    {{ $stats['checked_total'] }}/{{ $stats['total'] }} thiết bị ({{ $stats['progress_pct'] }}%)
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Phần 2: Danh sách chi tiết chênh lệch -->
    <div class="section-title">II. DANH SÁCH CHI TIẾT THIẾT BỊ CHÊNH LỆCH (THIẾU, THỪA, HỎNG)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">STT</th>
                <th style="width: 14%;">Mã thiết bị</th>
                <th style="width: 25%;">Tên thiết bị</th>
                <th style="width: 16%;">Model / Serial</th>
                <th style="width: 15%;">Khoa / Vị trí</th>
                <th style="width: 11%;">Kết quả</th>
                <th style="width: 15%;">Ghi chú / Tình trạng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($discrepancyItems as $idx => $item)
                @php
                    $eq = $item->equipment;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->scanned_code ?: ($eq?->code ?? '-') }}</strong></td>
                    <td>{{ $eq?->name ?? 'Thiết bị không xác định' }}</td>
                    <td>
                        <div>{{ $eq?->equipmentModel?->name ?? ($eq?->equipmentModel?->code ?? '-') }}</div>
                        <div style="font-size: 7.5pt; color: #64748b;">SN: {{ $eq?->serial_number ?: '-' }}</div>
                    </td>
                    <td>
                        <div>{{ $item->systemDepartment?->name ?? ($eq?->department?->name ?? '-') }}</div>
                        <div style="font-size: 7.5pt; color: #64748b;">{{ $item->actual_location ?: ($item->systemLocation?->name ?? ($eq?->location?->name ?? '-')) }}</div>
                    </td>
                    <td class="text-center">
                        @if($item->result?->value === 'thieu')
                            <span class="result-thieu">Thiếu</span>
                        @elseif($item->result?->value === 'thua')
                            <span class="result-thua">Thừa</span>
                        @elseif($item->result?->value === 'hong')
                            <span class="result-hong">Hỏng</span>
                        @else
                            {{ $item->result?->label() ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item->note ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 10px; color: #16a34a; font-style: italic;">
                        ✓ Không phát hiện chênh lệch (Không có thiết bị thiếu, thừa hoặc hỏng trong danh mục).
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Chữ ký -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sign-role">ĐẠI DIỆN KHOA SỬ DỤNG</div>
                <div class="sign-note">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
                <div class="sign-name">................................................</div>
            </td>
            <td>
                <div class="sign-role">NGƯỜI LẬP BIÊN BẢN</div>
                <div class="sign-note">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $session->creator?->name ?? '................................................' }}</div>
            </td>
            <td>
                <div class="sign-role">PHÒNG VẬT TƯ - TBYT</div>
                <div class="sign-note">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
                <div class="sign-name">................................................</div>
            </td>
            <td>
                <div class="sign-role">GIÁM ĐỐC BỆNH VIỆN</div>
                <div class="sign-note">(Ký, đóng dấu)</div>
                <div class="sign-space"></div>
                <div class="sign-name">................................................</div>
            </td>
        </tr>
    </table>
</body>
</html>
