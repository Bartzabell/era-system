<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Incident Report – {{ $incidentReport->incident_code ?? 'N/A' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
        }

        @page {
            margin: 0.6in 0.65in;
        }

        body {
            padding: 0.55in 0.6in;
        }

        .page {
            width: 100%;
        }

        /* ── HEADER ── */
        .header {
            border-top: 4px solid #111;
            border-bottom: 1.5px solid #111;
            padding: 10px 0 10px 0;
            margin-bottom: 14px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td { vertical-align: middle; }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
            letter-spacing: 0.3px;
        }
        .header-code {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-meta {
            text-align: right;
            font-size: 9.5px;
            color: #555;
            margin-top: 3px;
        }

        /* ── SECTION ── */
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            background: #333;
            padding: 4px 8px;
            margin-bottom: 0;
        }

        /* ── FIELD TABLE — fully bordered grid ── */
        .field-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #bbb;
        }
        .field-table tr {
            border-bottom: 1px solid #ddd;
        }
        .field-table tr:last-child {
            border-bottom: none;
        }
        .field-table td {
            padding: 5px 8px;
            vertical-align: middle;
            border-right: 1px solid #ddd;
        }
        .field-table td:last-child {
            border-right: none;
        }
        .field-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            background: #f5f5f5;
            width: 130px;
            white-space: nowrap;
        }
        .field-value {
            font-size: 11px;
            color: #111;
            min-width: 100px;
        }
        .field-value.empty {
            color: #aaa;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #999;
            background: #eee;
            color: #333;
        }
        .badge-status-waiting   { background: #f5f5f5; border-color: #bbb; color: #555; }
        .badge-status-assigned  { background: #e3edf7; border-color: #7aaad4; color: #1a4f7a; }
        .badge-status-arriving  { background: #fdf3dc; border-color: #d4a017; color: #7a5200; }
        .badge-status-resolved  { background: #e3f2e3; border-color: #5a9e5a; color: #1a5a1a; }
        .badge-status-cancelled { background: #f7e3e3; border-color: #c77; color: #7a1a1a; }

        .badge-priority-p1 { background: #f7e3e3; border-color: #c00; color: #7a0000; }
        .badge-priority-p2 { background: #fdf3dc; border-color: #c80; color: #7a4000; }
        .badge-priority-p3 { background: #e3edf7; border-color: #47a; color: #1a4f7a; }
        .badge-priority-p4 { background: #e3f2e3; border-color: #5a9; color: #1a5a1a; }
        .badge-priority-p5 { background: #f5f5f5; border-color: #aaa; color: #555; }

        /* ── CASUALTY BOX ── */
        .casualty-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #bbb;
        }
        .casualty-table th {
            background: #333;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: bold;
            padding: 5px 8px;
            border-right: 1px solid #555;
            text-align: center;
        }
        .casualty-table th:last-child { border-right: none; }
        .casualty-table td {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #ddd;
            color: #111;
        }
        .casualty-table td:last-child { border-right: none; }
        .casualty-table tbody tr { background: #fff; }
        .casualty-table tbody tr td:last-child {
            background: #f5f5f5;
        }

        /* ── FOOTER ── */
        .footer {
            border-top: 1.5px solid #333;
            padding-top: 10px;
            margin-top: 24px;
            font-size: 9px;
            color: #888;
        }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: bottom; }

        /* ── SIGNATURE BOX ── */
        .sig-box {
            border-top: 1px solid #444;
            width: 170px;
            margin-top: 36px;
            padding-top: 4px;
            font-size: 9px;
            text-align: center;
            color: #444;
        }

        /* ── ATTACHMENT PAGE ── */
        .page-break { page-break-before: always; }
        .attachment-header {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            color: #111;
        }
        .attachment-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.8px;
            color: #fff;
            background: #555;
            padding: 3px 8px;
            margin-bottom: 8px;
            margin-top: 16px;
            display: block;
        }
        .attachment-img {
            max-width: 100%;
            max-height: 580px;
            display: block;
            border: 1px solid #ccc;
        }

        /* ── PDF ATTACHMENT NOTICE ── */
        .pdf-notice {
            border: 1px solid #ccc;
            background: #f7f7f7;
            padding: 14px 16px;
        }
        .pdf-notice-icon {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }
        .pdf-notice-row {
            margin-top: 5px;
            font-size: 10px;
            color: #555;
        }
        .pdf-notice-row span { font-weight: bold; color: #222; }
        .pdf-notice-hint {
            margin-top: 8px;
            font-size: 9px;
            color: #999;
            border-top: 1px dashed #ddd;
            padding-top: 7px;
        }

        /* ── HELPERS ── */
        .text-gray  { color: #777; }
        .text-bold  { font-weight: bold; }
        .mt4        { margin-top: 4px; }
        .mt8        { margin-top: 8px; }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════ --}}
{{-- REPORT PAGE                               --}}
{{-- ══════════════════════════════════════════ --}}
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:60%;">
                    <div class="header-title">Incident Report</div>
                    <div class="header-subtitle">Emergency Response Management System</div>
                </td>
                <td style="width:40%;">
                    <div class="header-code">{{ $incidentReport->incident_code ?? '—' }}</div>
                    <div class="header-meta">
                        Generated: {{ now()->format('M d, Y h:i A') }}
                    </div>
                    <div class="header-meta">
                        Reported by: {{ optional($incidentReport->user)->full_name ?? '—' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── SECTION 1: INCIDENT INFORMATION ── --}}
    <div class="section">
        <div class="section-title">Incident Information</div>
        <table class="field-table">
            <tr>
                <td class="field-label">Emergency Type</td>
                <td class="field-value">{{ optional($incidentReport->emergency)->emergency_name ?? '—' }}</td>
                <td class="field-label">Incident Type</td>
                <td class="field-value">{{ optional($incidentReport->incident)->incident_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="field-label">Barangay</td>
                <td class="field-value">{{ optional($incidentReport->barangay)->barangay_name ?? '—' }}</td>
                <td class="field-label">Site Location</td>
                <td class="field-value">{{ optional($incidentReport->siteLocation)->site_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="field-label">Map Coordinates</td>
                <td class="field-value">{{ $incidentReport->map_coordinates ?? '—' }}</td>
                <td class="field-label">Distance (road)</td>
                <td class="field-value">
                    @if($incidentReport->distance)
                        {{ $incidentReport->distance }} km
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="field-label">Date Reported</td>
                <td class="field-value">
                    {{ $incidentReport->reported_at
                        ? \Carbon\Carbon::parse($incidentReport->reported_at)->format('M d, Y h:i A')
                        : '—' }}
                </td>
                <td class="field-label">Severity Level</td>
                <td class="field-value">
                    {{ $incidentReport->severity_level
                        ? ucfirst($incidentReport->severity_level)
                        : '—' }}
                </td>
            </tr>
            <tr>
                <td class="field-label">Status</td>
                <td class="field-value">
                    @php $status = $incidentReport->status ?? 'waiting'; @endphp
                    <span class="badge badge-status-{{ $status }}">{{ ucfirst($status) }}</span>
                </td>
                <td class="field-label">Priority</td>
                <td class="field-value">
                    @if($incidentReport->priority_level)
                        @php $pl = strtolower($incidentReport->priority_level); @endphp
                        <span class="badge badge-priority-{{ $pl }}">
                            {{ $incidentReport->priority_level }} — {{ $incidentReport->priority_label }}
                            &nbsp;({{ $incidentReport->priority_score }}/10)
                        </span>
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ── SECTION 2: CASUALTY COUNT ── --}}
    <div class="section">
        <div class="section-title">Casualty Count</div>
        <table class="casualty-table">
            <thead>
                <tr>
                    <th>Minor</th>
                    <th>Serious</th>
                    <th>Deceased</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ (int)($incidentReport->minor_casualty_count ?? 0) }}</td>
                    <td>{{ (int)($incidentReport->serious_casualty_count ?? 0) }}</td>
                    <td>{{ (int)($incidentReport->deceased_casualty_count ?? 0) }}</td>
                    <td>{{ (int)($incidentReport->casualty_count ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── SECTION 3: RESPONDER DETAILS ── --}}
    <div class="section">
        <div class="section-title">Responder Details</div>
        <table class="field-table">
            <tr>
                <td class="field-label">Responder Name</td>
                <td class="field-value">{{ $incidentReport->responder_name ?: '—' }}</td>
                <td class="field-label">Contact No.</td>
                <td class="field-value">{{ $incidentReport->responder_contact_no ?: '—' }}</td>
            </tr>
            <tr>
                <td class="field-label">Responder Count</td>
                <td class="field-value">{{ $incidentReport->responder_count ?? '—' }}</td>
                <td class="field-label">Plate No.</td>
                <td class="field-value">{{ $incidentReport->plate_no ?: '—' }}</td>
            </tr>
            <tr>
                <td class="field-label">Estimated Arrival</td>
                <td class="field-value">
                    {{ $incidentReport->estimated_arrival
                        ? \Carbon\Carbon::parse($incidentReport->estimated_arrival)->format('M d, Y h:i A')
                        : '—' }}
                </td>
                <td class="field-label">Datetime Arrived</td>
                <td class="field-value">
                    {{ $incidentReport->datetime_arrived
                        ? \Carbon\Carbon::parse($incidentReport->datetime_arrived)->format('M d, Y h:i A')
                        : '—' }}
                </td>
            </tr>
            @if($incidentReport->reported_at && $incidentReport->datetime_arrived)
            <tr>
                <td class="field-label">Response Time</td>
                <td class="field-value" colspan="3">
                    @php
                        $diff = \Carbon\Carbon::parse($incidentReport->reported_at)
                                    ->diffInMinutes(\Carbon\Carbon::parse($incidentReport->datetime_arrived));
                        $hrs  = intdiv($diff, 60);
                        $mins = $diff % 60;
                    @endphp
                    {{ $hrs > 0 ? "{$hrs}h {$mins}m" : "{$mins}m" }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ── SECTION 4: NOTES & REMARKS ── --}}
    @if($incidentReport->remarks || $incidentReport->responder_remarks || $incidentReport->treatment_provided || $incidentReport->cancel_remarks)
    <div class="section">
        <div class="section-title">Notes &amp; Remarks</div>
        <table class="field-table">
            @if($incidentReport->remarks)
            <tr>
                <td class="field-label">Remarks</td>
                <td class="field-value" colspan="3">{{ $incidentReport->remarks }}</td>
            </tr>
            @endif
            @if($incidentReport->responder_remarks)
            <tr>
                <td class="field-label">Responder Remarks</td>
                <td class="field-value" colspan="3">{{ $incidentReport->responder_remarks }}</td>
            </tr>
            @endif
            @if($incidentReport->treatment_provided)
            <tr>
                <td class="field-label">Treatment Provided</td>
                <td class="field-value" colspan="3">{{ $incidentReport->treatment_provided }}</td>
            </tr>
            @endif
            @if($incidentReport->cancel_remarks)
            <tr>
                <td class="field-label">Cancel Remarks</td>
                <td class="field-value" colspan="3">{{ $incidentReport->cancel_remarks }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- ── SECTION 5: SYSTEM INFO ── --}}
    <div class="section">
        <div class="section-title">System Information</div>
        <table class="field-table">
            <tr>
                <td class="field-label">Created At</td>
                <td class="field-value">
                    {{ $incidentReport->created_at
                        ? \Carbon\Carbon::parse($incidentReport->created_at)->format('M d, Y h:i A')
                        : '—' }}
                </td>
                <td class="field-label">Last Updated</td>
                <td class="field-value">
                    {{ $incidentReport->updated_at
                        ? \Carbon\Carbon::parse($incidentReport->updated_at)->format('M d, Y h:i A')
                        : '—' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    <div class="sig-box">Prepared by / Signature over Printed Name</div>
                </td>
                <td style="text-align:right;">
                    <div class="sig-box" style="margin-left:auto;">Received by / Signature over Printed Name</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top:10px; font-size:9px; color:#aaa; text-align:center;">
                    {{ $incidentReport->incident_code ?? '' }}
                    &nbsp;·&nbsp; This document is system-generated and may not require a wet signature.
                </td>
            </tr>
        </table>
    </div>

</div>{{-- /page --}}


{{-- ══════════════════════════════════════════ --}}
{{-- ATTACHMENT PAGES                          --}}
{{-- ══════════════════════════════════════════ --}}

@php
    $hasReporterAttachment  = !empty($incidentReport->attachment);
    $hasResponderAttachment = !empty($incidentReport->responder_attachment);

    $reporterExt  = $hasReporterAttachment  ? strtolower(pathinfo($incidentReport->attachment, PATHINFO_EXTENSION))  : '';
    $responderExt = $hasResponderAttachment ? strtolower(pathinfo($incidentReport->responder_attachment, PATHINFO_EXTENSION)) : '';

    $reporterIsImage  = in_array($reporterExt,  ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $responderIsImage = in_array($responderExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

    $reporterIsPdf  = $reporterExt  === 'pdf';
    $responderIsPdf = $responderExt === 'pdf';
@endphp

@if($hasReporterAttachment || $hasResponderAttachment)
<div class="page-break">
<div class="page">

    <div class="attachment-header">
        Attachments — {{ $incidentReport->incident_code ?? 'N/A' }}
    </div>

    {{-- Reporter Attachment --}}
    @if($hasReporterAttachment)
        <div class="attachment-label">Reporter Attachment</div>

        @if($reporterIsImage)
            <img
                src="{{ storage_path('app/public/' . $incidentReport->attachment) }}"
                class="attachment-img"
                alt="Reporter Attachment"
            />
        @else
            <div class="pdf-notice">
                <div class="pdf-notice-icon">&#128196; PDF Document</div>
                <div class="pdf-notice-row">File: <span>{{ basename($incidentReport->attachment) }}</span></div>
                <div class="pdf-notice-row">Path: <span>{{ $incidentReport->attachment }}</span></div>
                <div class="pdf-notice-hint">PDF attachments cannot be rendered inline. Please access the file directly from storage.</div>
            </div>
        @endif
    @endif

    {{-- Responder Attachment --}}
    @if($hasResponderAttachment)
        <div class="attachment-label">Responder Attachment</div>

        @if($responderIsImage)
            <img
                src="{{ storage_path('app/public/' . $incidentReport->responder_attachment) }}"
                class="attachment-img"
                alt="Responder Attachment"
            />
        @else
            <div class="pdf-notice">
                <div class="pdf-notice-icon">&#128196; PDF Document</div>
                <div class="pdf-notice-row">File: <span>{{ basename($incidentReport->responder_attachment) }}</span></div>
                <div class="pdf-notice-row">Path: <span>{{ $incidentReport->responder_attachment }}</span></div>
                <div class="pdf-notice-hint">PDF attachments cannot be rendered inline. Please access the file directly from storage.</div>
            </div>
        @endif
    @endif

    {{-- Footer on attachment page --}}
    <div class="footer" style="margin-top:30px;">
        <table class="footer-table">
            <tr>
                <td style="font-size:9px; color:#aaa;">
                    {{ $incidentReport->incident_code ?? '' }}
                    &nbsp;·&nbsp; Attachments page — system-generated
                </td>
                <td style="text-align:right; font-size:9px; color:#aaa;">
                    Generated: {{ now()->format('M d, Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

</div>
</div>
@endif

</body>
</html>
