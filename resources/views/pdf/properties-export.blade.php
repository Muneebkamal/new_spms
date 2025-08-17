<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Noto Sans SC';
            src: url('{{ public_path('fonts/NotoSansSC-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Noto Sans SC', DejaVu Sans, sans-serif;
        }
        .header { font-size: 18px; font-weight: bold; }
        .subheader { font-size: 14px; }
        .license { font-size: 12px; margin-bottom: 20px; }
        .date { font-size: 12px; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">保誠物業代理有限公司</div>
    <div class="subheader">Bo Shing Property Agency Limited</div>
    <div class="license">牌照號碼：(C-088969)</div>
    <div class="date">Date: {{ now()->format('F d, Y') }}</div>

    <table>
        <thead>
            <tr>
                @foreach($header as $head)
                    <th>{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    @foreach($columnsToFetch as $col)
                        <td>{{ $item->$col ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        聲明：有關此物業之介紹書，包括本物業之細則及平面圖僅供參考，本公司巳力求準確，但不擔保或保證他們完整性及正確，貴客戶應自行研究及了解方可作根據。 一切資料並不能構成出價根據或合約中的任何部分。
    </div>
</body>
</html>
