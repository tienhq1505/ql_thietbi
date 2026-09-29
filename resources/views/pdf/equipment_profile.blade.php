<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ Thiết bị Y tế - {{ $equipment->code }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            color: #0369a1;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9pt;
            color: #64748b;
            margin-top: 4px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 6px 10px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #0284c7;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.info-table td {
            padding: 6px 8px;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
        }
        table.info-table td.label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        table.info-table td.value {
            width: 25%;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">HỒ SƠ TRANG THIẾT BỊ Y TẾ</div>
        <div class="subtitle">Mã thiết bị: <strong>{{ $equipment->code }}</strong> — Ngày xuất hồ sơ: {{ date('d/m/Y H:i') }}</div>
    </div>

    <div class="section-title">I. THÔNG TIN CHUNG</div>
    <table class="info-table">
        <tr>
            <td class="label">Mã thiết bị:</td>
            <td class="value"><strong>{{ $equipment->code }}</strong></td>
            <td class="label">Tên thiết bị:</td>
            <td class="value"><strong>{{ $equipment->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Model:</td>
            <td class="value">{{ $equipment->equipmentModel?->name }} ({{ $equipment->equipmentModel?->code }})</td>
            <td class="label">Loại thiết bị:</td>
            <td class="value">{{ $equipment->category?->name }}</td>
        </tr>
        <tr>
            <td class="label">Hãng sản xuất:</td>
            <td class="value">{{ $equipment->manufacturer?->name }}</td>
            <td class="label">Xuất xứ:</td>
            <td class="value">{{ $equipment->origin_country ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Số Serial:</td>
            <td class="value"><strong>{{ $equipment->serial_number }}</strong></td>
            <td class="label">Năm sản xuất:</td>
            <td class="value">{{ $equipment->manufacture_year ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">II. QUẢN LÝ & SỬ DỤNG</div>
    <table class="info-table">
        <tr>
            <td class="label">Khoa / Phòng:</td>
            <td class="value">{{ $equipment->department?->name }}</td>
            <td class="label">Vị trí chi tiết:</td>
            <td class="value">{{ $equipment->location?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Người phụ trách:</td>
            <td class="value">{{ $equipment->manager_name ?? $equipment->manager?->name ?? '-' }}</td>
            <td class="label">Nhà cung cấp:</td>
            <td class="value">{{ $equipment->supplier?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Trạng thái:</td>
            <td class="value">{{ $equipment->status?->label() }}</td>
            <td class="label">Tình trạng KT:</td>
            <td class="value"><strong>{{ $equipment->condition?->label() }}</strong></td>
        </tr>
    </table>

    <div class="section-title">III. TÀI CHÍNH & NGHĨA VỤ</div>
    <table class="info-table">
        <tr>
            <td class="label">Ngày nhập kho:</td>
            <td class="value">{{ $equipment->import_date ? $equipment->import_date->format('d/m/Y') : '-' }}</td>
            <td class="label">Ngày sử dụng:</td>
            <td class="value">{{ $equipment->usage_start_date ? $equipment->usage_start_date->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Giá mua (VNĐ):</td>
            <td class="value">{{ number_format($equipment->purchase_price, 0, ',', '.') }} VNĐ</td>
            <td class="label">Nguồn kinh phí:</td>
            <td class="value">{{ $equipment->funding_source ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Hạn bảo hành:</td>
            <td class="value">{{ $equipment->warranty_expiration_date ? $equipment->warranty_expiration_date->format('d/m/Y') : '-' }}</td>
            <td class="label">Ghi chú:</td>
            <td class="value">{{ $equipment->note ?? '-' }}</td>
        </tr>
    </table>
</body>
</html>
