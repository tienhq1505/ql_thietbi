<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Trang thiết bị Y tế - MEMS</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-b: 2px solid #0284c7;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #0369a1;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9pt;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>BÁO CÁO DANH SÁCH TRANG THIẾT BỊ Y TẾ</h2>
        <p>Bệnh viện MEMS Hospital — Xuất ngày: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">STT</th>
                <th>Mã TB</th>
                <th>Tên Thiết bị</th>
                <th>Model</th>
                <th>Khoa phòng</th>
                <th>Số Serial</th>
                <th>Trạng thái</th>
                <th>Tình trạng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->code }}</strong></td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->equipmentModel?->code ?? '-' }}</td>
                    <td>{{ $item->department?->name ?? '-' }}</td>
                    <td>{{ $item->serial_number }}</td>
                    <td>{{ $item->status?->label() ?? $item->status }}</td>
                    <td>{{ $item->condition?->label() ?? $item->condition }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Không có thiết bị nào phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Tổng số thiết bị: <strong>{{ count($items) }}</strong></p>
    </div>
</body>
</html>
