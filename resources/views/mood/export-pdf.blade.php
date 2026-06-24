<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mood Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 20px;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .meta {
            color: #555;
            margin-bottom: 14px;
        }

        .stats {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }

        .stats div {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            width: 33.3%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f5f5f5;
            font-weight: 700;
        }

        .note {
            color: #444;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <h1>Mood Report</h1>
    <p class="meta">Period: {{ $period }} | Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}</p>

    <div class="stats">
        <div>
            <strong>Total entries</strong><br>
            {{ $totalEntries }}
        </div>
        <div>
            <strong>Average mood score</strong><br>
            {{ $averageScore }}
        </div>
        <div>
            <strong>Positive mood rate</strong><br>
            {{ $positiveRate }}%
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Date/Time</th>
                <th style="width: 18%;">Mood</th>
                <th style="width: 10%;">Score</th>
                <th style="width: 52%;">Journal Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ optional($log->logged_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->mood_label }}</td>
                    <td>{{ $log->mood_value }}</td>
                    <td class="note">{{ $log->journal_note ?? 'No note' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No mood entries found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
