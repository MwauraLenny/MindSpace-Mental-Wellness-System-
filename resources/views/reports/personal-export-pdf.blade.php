<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Personal Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 16px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0 18px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Personal Report</h1>
    <p class="meta">Generated at: {{ $generatedAt->format('Y-m-d H:i:s') }}</p>

    <h2>Mood Report Overview</h2>
    <table>
        <tbody>
            <tr><th>Total Mood Entries</th><td>{{ $totalEntries }}</td></tr>
            <tr><th>Average Mood Score</th><td>{{ $averageScore }}</td></tr>
            <tr><th>Positive Mood Rate</th><td>{{ $positiveRate }}%</td></tr>
            <tr><th>Most Common Mood</th><td>{{ $mostFrequentMood }}</td></tr>
            <tr><th>Trend Summary</th><td>{{ $trendSummary }}</td></tr>
        </tbody>
    </table>

    <h2>Emotional Statistics</h2>
    <table>
        <thead>
            <tr>
                <th>Mood</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($moodCounts as $key => $count)
                <tr>
                    <td>{{ $categories[$key]['label'] }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Activity Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Activity</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activitySummary as $metric => $count)
                <tr>
                    <td>{{ str_replace('_', ' ', $metric) }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
