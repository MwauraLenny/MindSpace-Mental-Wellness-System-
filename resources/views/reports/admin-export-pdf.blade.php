<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Report</title>
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
    <h1>Admin Report</h1>
    <p class="meta">Generated at: {{ $generatedAt->format('Y-m-d H:i:s') }}</p>

    <h2>User Statistics</h2>
    <table>
        <tbody>
            <tr><th>Total Users</th><td>{{ $totalUsers }}</td></tr>
            <tr><th>Active Users ({{ $activeWindowLabel }})</th><td>{{ $activeUsers }}</td></tr>
        </tbody>
    </table>

    <h2>Most Common Moods</h2>
    <table>
        <thead>
            <tr>
                <th>Mood</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mostCommonMoods as $mood)
                <tr>
                    <td>{{ $mood['label'] }}</td>
                    <td>{{ $mood['total'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No mood data yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Most Liked Routines</h2>
    <table>
        <thead>
            <tr>
                <th>Routine</th>
                <th>Likes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mostLikedRoutines as $routine)
                <tr>
                    <td>{{ $routine->display_title }}</td>
                    <td>{{ $routine->likes_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No routine data yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Reported Content Statistics</h2>
    <table>
        <tbody>
            <tr><th>Total</th><td>{{ $reportStats['total'] }}</td></tr>
            <tr><th>Pending</th><td>{{ $reportStats['pending'] }}</td></tr>
            <tr><th>Resolved</th><td>{{ $reportStats['resolved'] }}</td></tr>
            <tr><th>Dismissed</th><td>{{ $reportStats['dismissed'] }}</td></tr>
        </tbody>
    </table>

    <h2>Routine Feedback Monitoring</h2>
    <table>
        <tbody>
            <tr><th>Feedback events (30d)</th><td>{{ $monitoringMetrics['feedback_events_30d'] }}</td></tr>
            <tr><th>Help rate (30d)</th><td>{{ $monitoringMetrics['feedback_help_rate_30d'] }}%</td></tr>
        </tbody>
    </table>
</body>
</html>
