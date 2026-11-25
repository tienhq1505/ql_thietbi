<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'In nhãn QR - MEMS' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #1a1a1a;
            font-size: 11px;
        }

        /* Toolbar — hidden when printing */
        .no-print {
            background: #1e293b;
            color: #e2e8f0;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .no-print button, .no-print a {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #06b6d4; color: #fff; }
        .btn-print:hover { background: #22d3ee; }
        .btn-back { background: #334155; color: #cbd5e1; }
        .btn-back:hover { background: #475569; }
        .toolbar-info { margin-left: auto; font-size: 12px; color: #94a3b8; }

        /* Label Grid */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 15mm;
            max-width: 210mm;
            margin: 0 auto;
        }

        .label-card {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            break-inside: avoid;
            page-break-inside: avoid;
            min-height: 55mm;
        }
        .label-qr {
            flex-shrink: 0;
            width: 100px;
            height: 100px;
        }
        .label-qr svg {
            width: 100px;
            height: 100px;
        }
        .label-info {
            flex: 1;
            overflow: hidden;
        }
        .label-code {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #0e7490;
            margin-bottom: 1px;
            word-break: break-all;
            line-height: 1.2;
        }
        .label-url {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 8.5px;
            color: #64748b;
            margin-bottom: 3px;
            word-break: break-all;
            line-height: 1.1;
        }
        .label-name {
            font-size: 10px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .label-dept, .label-model {
            font-size: 9px;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
            font-size: 16px;
        }

        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .label-grid { padding: 10mm; gap: 8px; }
            .label-card { border-color: #9ca3af; }
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ In ngay</button>
        <a href="javascript:history.back()" class="btn-back">← Quay lại</a>
        <span class="toolbar-info">{{ $title ?? '' }} — {{ count($labels ?? []) }} nhãn</span>
    </div>

    {{ $slot }}
</body>
</html>
