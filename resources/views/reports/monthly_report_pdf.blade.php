<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Incident Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h2   { text-align: center; margin-bottom: 4px; }
        p.sub { text-align: center; margin: 0 0 10px; font-size: 9px; color: #555; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 4px 6px; text-align: center; }
        th { background-color: #ffce3be0; color: #000000; }
        th.group { background-color: #ffe121; }
        tr:nth-child(even) { background-color: #c3c3c3; }

        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h2>Monthly Incident Report</h2>
    <p class="sub">
        Date Range: {{ $date_from }} &mdash; {{ $date_to }}
    </p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Barangay</th>
                <th rowspan="2">Landmark</th>
                <th colspan="3">Medical Condition</th>
                <th rowspan="2">Total No. of Incidents</th>
                <th rowspan="2">Incidents</th>
            </tr>
            <tr>
                <th>Minor</th>
                <th>Serious</th>
                <th>Dead</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td class="text-left">{{ $row['barangay_name'] }}</td>
                <td class="text-left">{{ $row['landmark'] }}</td>
                <td>{{ $row['minor'] }}</td>
                <td>{{ $row['serious'] }}</td>
                <td>{{ $row['dead'] }}</td>
                <td>{{ $row['total_incidents'] }}</td>
                <td class="text-left">{{ $row['top_incidents'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999;">No data found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
