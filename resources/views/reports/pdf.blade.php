@php
if (!function_exists('formatHoursPdf')) {
    function formatHoursPdf($minutes) {
        $h = $minutes / 60;
        return (abs(round($h, 2) - round($h, 0)) < 0.01) ? (string)(int)round($h) : number_format($h, 1, ',', '');
    }
}
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report {{ $month->translatedFormat('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; table-layout: fixed; border-radius: 8px; overflow: hidden; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #1e40af; color: #fff; font-weight: bold; }
        th.col-date { width: 12%; }
        th.col-project { width: 26%; }
        th.col-hours { width: 8%; }
        th.col-note { width: 54%; }
        td.note { word-wrap: break-word; overflow-wrap: break-word; }
        tr.row-alt { background: #f8fafc; }
        tr.day-header td { background: #e0e7ff; color: #1e3a8a; font-weight: bold; padding: 8px; border-bottom: 1px solid #c7d2fe; font-size: 10px; }
        .header { background: #1e40af; color: #fff; padding: 18px 22px; margin: -8px -8px 20px -8px; border-radius: 0 0 12px 12px; }
        .header h1 { margin: 0 0 6px 0; font-size: 18px; font-weight: bold; }
        .header p { margin: 0; font-size: 11px; opacity: 0.95; }
        .summary-box { margin-top: 24px; border: 1px solid #c7d2fe; background: #f8fafc; border-radius: 12px; overflow: hidden; page-break-inside: avoid; break-inside: avoid; }
        .summary-box h3 { margin: 0; padding: 12px 16px; background: #1e40af; color: #fff; font-size: 12px; font-weight: bold; }
        .summary-total { padding: 12px 16px; font-weight: bold; font-size: 13px; color: #1e293b; border-bottom: 1px solid #e2e8f0; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 8px 16px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .summary-table tr:last-child td { border-bottom: 0; }
        .summary-table .project-name { font-weight: 600; color: #1e40af; }
        .summary-table .project-hours { font-weight: 600; color: #1e293b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Work report — {{ $month->locale('sk')->translatedFormat('F Y') }}</h1>
        <p>{{ $user->name }} ({{ $user->email }})</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-date">Dátum</th>
                <th class="col-project">Projekt</th>
                <th class="col-hours">Hodiny</th>
                <th class="col-note">Poznámka</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workLogsByDay as $dateStr => $dayLogs)
                @php $dayCarbon = \Carbon\Carbon::parse($dateStr)->locale('sk'); @endphp
                <tr class="day-header">
                    <td colspan="4">{{ $dayCarbon->translatedFormat('l') }} — {{ $dayCarbon->format('d. m. Y') }}</td>
                </tr>
                @foreach($dayLogs as $index => $log)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'row-alt' : '' }}">
                        <td>{{ $log->work_date->format('d.m.Y') }}</td>
                        <td>{{ $log->project->name }}</td>
                        <td>{{ formatHoursPdf($log->minutes) }} h</td>
                        <td class="note">{{ $log->note ?? '—' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Zhrnutie</h3>
        <div class="summary-total">Spolu: {{ formatHoursPdf($totalMinutes) }} h</div>
        <table class="summary-table">
            @foreach($byProject as $row)
                <tr>
                    <td class="project-name">{{ $row['name'] }}</td>
                    <td class="project-hours">{{ formatHoursPdf($row['minutes']) }} h</td>
                    <td>{{ $row['entries'] }} {{ $row['entries'] === 1 ? 'záznam' : ($row['entries'] >= 2 && $row['entries'] <= 4 ? 'záznamy' : 'záznamov') }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
