<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Incident Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        h1   { text-align: center; margin-bottom: 4px; font-size: 17px; color: rgba(224, 28, 28, 0.849); }
        h3   { text-align: center; font-size: 10px; color: rgba(40, 111, 204, 0.568); }
        h4   { text-align: center; font-size: 14px; color: rgba(40, 111, 204, 0.568); }
        h2   { text-align: center; margin-bottom: 4px; }
        h1, h2, h3, h4, p.sub { margin: 0; padding: 0; line-height: 1.2; }
        p.sub  { text-align: center; margin: 0 0 10px; font-size: 9px; color: #555; }
        p.date { font-size: 9px; margin: 6px 0 4px 0; }
        p.addressee { font-size: 9px; margin: 2px 0; }
        p.salutation { font-size: 9px; margin: 6px 0; }
        p.body-text  { font-size: 9px; text-align: center; margin: 0 0 8px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #444; padding: 3px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #ffce3be0; color: #000; font-size: 7.5px; }
        th.group { background-color: #ffe121; }
        tr:nth-child(even) { background-color: #c3c3c3; }
        tr.totals-row td { background-color: #ffe121; font-weight: bold; }

        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div style="text-align:center;">
        <img src="{{ public_path('storage/img/gma-logo.png') }}" style="width:90px; height:70px;" />
    </div>
    <h1>MUNICIPALITY OF GEN. MARIANO ALVAREZ</h1>
    <h3>ᜆᜅ᜔ᜄᜉᜈ᜔ ᜈᜅ᜔ ᜉᜋ᜔ᜊᜌᜅ᜔ ᜉᜋᜋᜑᜎ ᜐ ᜉᜄ᜔ᜆᜓᜄᜓᜈ᜔ ᜐ ᜐᜓᜊᜓᜈ ᜀᜆ᜔ ᜊᜈ᜔ᜆ ᜈᜅ᜔ ᜉᜅ᜔ᜄᜈᜒᜊ᜔</h3>
    <h3>TANGGAPAN NG PAMBAYANG PAMAMAHALA SA PAGTUGON SA SAKUNA AT BANTA NG PANGANIB</h3>
    <h4>OFFICE OF THE MUNICIPAL DISASTER RISK REDUCTION AND MANAGEMENT</h4>

    <br>

    @if($date_from)
    <p class="date">{{ \Carbon\Carbon::parse($date_from)->format('F j, Y') }}</p>
    @endif

    <p class="addressee"><strong>Hon.</strong></p>
    <p class="addressee">Municipal Mayor</p>
    <p class="addressee">GMA, Cavite</p>

    <br>
    <p class="salutation">Dear Mayor,</p>
    <br>

    <p class="body-text">
        Please see below the Monthly Report of GMA RESCUE UNIT
        @if($date_from && $date_to)
            for the period of <strong>{{ \Carbon\Carbon::parse($date_from)->format('F j, Y') }}</strong>
            to <strong>{{ \Carbon\Carbon::parse($date_to)->format('F j, Y') }}</strong>.
        @endif
        All victims brought to CARSIGMA Hospital for proper treatment and assessment.
    </p>

    <table>
        <thead>
            <tr>
                <th rowspan="3">NO.</th>
                <th rowspan="3">BARANGAY</th>
                <th rowspan="3">Major Establishment / Facilities / Building located in the area</th>
                <th colspan="4">MEDICAL CONDITION</th>
                <th colspan="3">TOTAL NUMBER OF<br>RECORDED ACCIDENTS</th>
                <th colspan="2">SPECIFIC TIME OF<br>OCCURRENCE</th>
                <th rowspan="3">BRIEF DESCRIPTION</th>
            </tr>
            <tr>
                <th rowspan="2">Minor</th>
                <th rowspan="2">Serious</th>
                <th rowspan="2">Dead</th>
                <th rowspan="2">TOTAL</th>
                <th rowspan="2">Total No.<br>of Incidents</th>
                <th rowspan="2">DATE</th>
                <th rowspan="2">Incidents</th>
                <th rowspan="2">FROM</th>
                <th rowspan="2">TO</th>
            </tr>
            <tr>
                {{-- FROM/TO are already set with rowspan=2 in the row above, this row is intentionally empty --}}
                {{-- Needed for the 3-row header structure --}}
                <th style="display:none;"></th>
                <th style="display:none;"></th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMinor   = 0;
                $totalSerious = 0;
                $totalDead    = 0;
                $totalCasualty = 0;
                $totalIncidents = 0;
            @endphp

            @forelse ($rows as $row)
            @php
                $totalMinor    += $row['minor'];
                $totalSerious  += $row['serious'];
                $totalDead     += $row['dead'];
                $totalCasualty += $row['minor'] + $row['serious'] + $row['dead'];
                $totalIncidents += $row['total_incidents'];
            @endphp
            <tr>
                <td>{{ $row['no'] }}</td>
                <td class="text-left">{{ $row['barangay_name'] }}</td>
                <td class="text-left">{{ $row['landmark'] }}</td>
                <td>{{ $row['minor'] }}</td>
                <td>{{ $row['serious'] }}</td>
                <td>{{ $row['dead'] }}</td>
                <td>{{ $row['minor'] + $row['serious'] + $row['dead'] }}</td>
                <td>{{ $row['total_incidents'] }}</td>
                <td>{{ $row['incident_date'] ?? '' }}</td>
                <td class="text-left">{{ $row['top_incidents'] }}</td>
                <td>{{ $row['time_from'] ?? '' }}</td>
                <td>{{ $row['time_to'] ?? '' }}</td>
                <td class="text-left">{{ $row['brief_description'] ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align:center; color:#999;">No data found.</td>
            </tr>
            @endforelse

            <tr class="totals-row">
                <td colspan="3" class="text-left"><strong>Total Number of Victims</strong></td>
                <td>{{ $totalMinor }}</td>
                <td>{{ $totalSerious }}</td>
                <td>{{ $totalDead }}</td>
                <td>{{ $totalCasualty }}</td>
                <td>{{ $totalIncidents }}</td>
                <td colspan="5"></td>
            </tr>
        </tbody>
    </table>

    <br>
    <p style="font-size:9px;">For your information and reference.</p>
    <br>
    <p style="font-size:9px;">Very Truly yours,</p>
    <br><br>
    <p style="font-size:9px;"><strong>Engr. TEOFILO G. REA, II, MUDRM</strong></p>
    <p style="font-size:9px;">MGDH-I/LDRRM Officer</p>
</body>
</html>
