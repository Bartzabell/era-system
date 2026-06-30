<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Patient Care Report – {{ $record->chart_code }}</title>
<style>
    @page {
        margin: 6mm 8mm;
        size: legal portrait;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 6.3pt;
        color: #111;
        background: #fff;
        margin: 0;
        padding: 0;
        line-height: 1.15;
    }

    .page {
        width: 100%;
        padding: 0 2px;
    }

    /* ── Header ── */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
    }
    .header-logo-left  { width: 80px; text-align: left;  vertical-align: middle; }
    .header-logo-right { width: 80px; text-align: right; vertical-align: middle; }
    .header-center     { text-align: center; vertical-align: middle; }
    .header-center .seal img { width: 50px; height: 45px; }
    .mdrrmo-title {
        font-size: 7.5pt;
        font-weight: bold;
        margin-top: 1px;
    }

    /* ── Utility ── */
    .bordered { border: 1px solid #333; }
    .cell-label { font-size: 5.5pt; font-weight: bold; }

    .underline { border-bottom: 1px solid #555; min-width: 70px; }
    .ul-sm     { border-bottom: 1px solid #555; min-width: 32px; }
    .ul-lg     { border-bottom: 1px solid #555; min-width: 100px; }

    .check {
        display: inline-block;
        width: 6px;
        height: 6px;
        border: 1px solid #333;
        margin-right: 1px;
        vertical-align: middle;
        text-align: center;
        font-size: 5.5pt;
        line-height: 6px;
        overflow: hidden;
    }

    .section-header {
        background: #ddd;
        text-align: center;
        font-weight: bold;
        font-size: 6.8pt;
        padding: 1px 0;
        border: 1px solid #333;
        border-bottom: none;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
    }
    .main-table td, .main-table th {
        border: 1px solid #333;
        padding: 1px 2px;
        vertical-align: top;
    }
    .td-label {
        font-size: 5pt;
        font-weight: bold;
        color: #333;
        white-space: nowrap;
    }
    .td-value {
        font-size: 6.3pt;
        border-bottom: 1px solid #888;
        display: block;
        min-height: 9px;
    }

    .cb-row { margin: 0; line-height: 1.1; }
    .cb-item { display: inline-block; margin-right: 4px; }

    .priority-table { width: 100%; border-collapse: collapse; margin-top: 1px; }
    .priority-table td { border: 1px solid #333; padding: 1px 2px; vertical-align: top; font-size: 5.3pt; }
    .priority-title { font-weight: bold; font-size: 5.8pt; text-align: center; }

    .vitals-table { width: 100%; border-collapse: collapse; }
    .vitals-table th, .vitals-table td { border: 1px solid #333; padding: 1px 3px; font-size: 6pt; text-align: center; }
    .vitals-table th { background: #eee; font-weight: bold; }

    .gcs-table { width: 100%; border-collapse: collapse; margin-top: 2px; }
    .gcs-table th, .gcs-table td { border: 1px solid #333; padding: 1px 2px; font-size: 5.5pt; vertical-align: top; }
    .gcs-table th { background: #eee; font-weight: bold; text-align: center; }

    .sig-table { width: 100%; border-collapse: collapse; margin-top: 1px; }
    .sig-table td { border: 1px solid #333; padding: 2px 4px; font-size: 6pt; }
    .sig-line { border-top: 1px solid #333; font-size: 5pt; color: #555; margin-top: 10px; }

    .body-diagram { text-align: center; font-size: 5pt; color: #777; padding: 2px; }

    .page-break { page-break-before: always; padding-top: 4px; }

    .sample-table { width: 100%; border-collapse: collapse; }
    .sample-table td { border: 1px solid #333; padding: 1px 3px; }
    .sample-letter { font-weight: bold; font-size: 6.5pt; width: 10px; }
    .sample-desc   { font-size: 5pt; color: #555; white-space: nowrap; }
    .sample-value  { min-height: 18px; font-size: 6.3pt; }
</style>
</head>
<body>

@php
function cb(bool $checked): string {
    $inner = $checked ? 'X' : '&nbsp;';
    return '<span class="check">' . $inner . '</span>';
}
@endphp

{{-- PAGE 1 --}}
<div class="page">

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-logo-left">
                <div style="font-size:8pt; font-weight:bold; line-height:1.1;">
                    PATIENT<br>CARE<br>REPORT
                </div>
            </td>
            <td class="header-center">
                <div class="seal">
                    <img src="{{ public_path('storage/img/gma-logo.png') }}" style="width:55px; height:45px;" />
                </div>
                <div class="mdrrmo-title">MUNICIPAL DISASTER RISK REDUCTION AND MANAGEMENT OFFICE</div>
            </td>
            <td class="header-logo-right">
                @if(file_exists(public_path('storage/img/mdrrmo.png'))) {{-- mdrrmo --}}
                    <img src="{{ public_path('storage/img/mdrrmo.png') }}" style="width:45px;height:45px;">
                @else
                    <div style="width:45px;height:45px;border:1px solid #999;
                                display:inline-block;line-height:45px;text-align:center;font-size:5pt;color:#aaa;">
                        [LOGO]
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Row 1: CASE #, DATE, TIME, MILEAGE, CREW ON DUTY --}}
    <table class="main-table">
        <tr>
            <td style="width:13%">
                <div class="td-label">CASE #:</div>
                <div class="td-value">{{ $record->case_number }}</div>
            </td>
            <td style="width:13%">
                <div class="td-label">DATE:</div>
                <div class="td-value">{{ $record->case_date ? $record->case_date->format('m/d/Y') : '' }}</div>
            </td>
            <td style="width:27%">
                <div class="td-label">TIME</div>
                <div class="cb-row">Dispatch: <span class="underline">{{ $record->time_dispatch ? \Carbon\Carbon::parse($record->time_dispatch)->format('H:i') : '' }}</span></div>
                <div class="cb-row">On Scene: <span class="underline">{{ $record->time_arrived_on_scene ? \Carbon\Carbon::parse($record->time_arrived_on_scene)->format('H:i') : '' }}</span></div>
                <div class="cb-row">En-route Hosp.: <span class="underline">{{ $record->time_enroute_to_hospital ? \Carbon\Carbon::parse($record->time_enroute_to_hospital)->format('H:i') : '' }}</span></div>
                <div class="cb-row">Arrival Hosp.: <span class="underline">{{ $record->time_arrival_in_hospital ? \Carbon\Carbon::parse($record->time_arrival_in_hospital)->format('H:i') : '' }}</span></div>
                <div class="cb-row">Departure Hosp.: <span class="underline">{{ $record->time_departure_in_hospital ? \Carbon\Carbon::parse($record->time_departure_in_hospital)->format('H:i') : '' }}</span></div>
                <div class="cb-row">Back to Base: <span class="underline">{{ $record->time_back_to_base ? \Carbon\Carbon::parse($record->time_back_to_base)->format('H:i') : '' }}</span></div>
            </td>
            <td style="width:13%">
                <div class="td-label">MILEAGE</div>
                <div class="cb-row">Before: <span class="ul-sm">{{ $record->mileage_before_run }}</span> km</div>
                <div class="cb-row">Back: <span class="ul-sm">{{ $record->mileage_back_to_base }}</span> km</div>
            </td>
            <td style="width:34%">
                <div class="td-label">CREW ON DUTY</div>
                <div class="cb-row">Dispatcher: <span class="underline">{{ $record->dispatcher }}</span></div>
                <div class="cb-row">Unit: <span class="underline">{{ $record->unit }}</span></div>
                <div class="cb-row">Transport Officer: <span class="underline">{{ $record->transport_officer }}</span></div>
                <div class="cb-row">Team Leader: <span class="underline">{{ $record->team_leader }}</span></div>
                <div class="cb-row">Medics: <span class="ul-lg">{{ $record->medics }}</span></div>
            </td>
        </tr>

        {{-- LOCATION / CASE TYPE / TAG --}}
        <tr>
            <td colspan="2">
                <div class="td-label">LOCATION:</div>
                <div class="td-value" style="min-height:11px;">{{ $record->address }}</div>
            </td>
            <td colspan="2">
                <div class="td-label">CASE TYPE:</div>
                @php
                    $caseTypes = [
                        'medical_case'       => 'Medical Case',
                        'trauma_case'        => 'Trauma Case',
                        'vehicular_accident' => 'Vehicular Accident',
                        'patient_conduction' => 'Patient Conduction',
                        'special_case'       => 'Special Case',
                    ];
                @endphp
                @foreach($caseTypes as $key => $label)
                    <span class="cb-item">{!! cb($record->case_type === $key) !!}{{ $label }}</span>
                @endforeach
            </td>
            <td>
                <div class="td-label">TAG:</div>
                <div class="td-value">{{ $record->tag }}</div>
            </td>
        </tr>

        {{-- PATIENT NAME --}}
        <tr>
            <td colspan="3">
                <div class="td-label">NAME</div>
                <table style="width:100%; border-collapse:collapse; margin-top:2px;">
                    <tr>
                        <td style="width:45%; border-top:1px solid #555; padding-top:0;">
                            <span style="font-size:5pt; color:#555;">Last Name</span><br>
                            {{ $record->last_name }}
                        </td>
                        <td style="width:35%; border-top:1px solid #555; padding-top:0;">
                            <span style="font-size:5pt; color:#555;">First Name</span><br>
                            {{ $record->first_name }}
                        </td>
                        <td style="width:20%; border-top:1px solid #555; padding-top:0;">
                            <span style="font-size:5pt; color:#555;">Middle Name</span><br>
                            {{ $record->middle_name }}
                        </td>
                    </tr>
                </table>
            </td>
            <td colspan="2">
                <div>
                    <span class="td-label">AGE:</span>
                    <span class="ul-sm">{{ $record->age }}</span>
                    &nbsp;
                    <span class="td-label">GENDER:</span>
                    {!! cb($record->gender === 'male') !!} M
                    {!! cb($record->gender === 'female') !!} F
                </div>
                <div style="margin-top:2px;">
                    <span class="td-label">CIVIL STATUS:</span>
                    {!! cb($record->civil_status === 'single') !!} S
                    {!! cb($record->civil_status === 'married') !!} M
                    {!! cb($record->civil_status === 'widowed') !!} W
                </div>

            </td>
        </tr>

        {{-- ADDRESS --}}
        <tr>
            <td colspan="3">
                <div class="td-label">ADDRESS:</div>
                <div class="td-value">{{ $record->address }}</div>
            </td>
            <td colspan="2" rowspan="2" style="vertical-align:top;">
                <div style="margin-top:2px;">
                    <span class="td-label">INFORMANT/ LEGAL GUARDIAN:</span>
                    <span class="td-value" style="display:inline-block; min-height:9px;">{{ $record->informant_legal_guardian }}</span>
                </div>
            </td>
        </tr>

        {{-- DOB / CONTACT / RELIGION --}}
        <tr>
            <td colspan="2">
                <div class="td-label">DATE OF BIRTH:</div>
                <div class="td-value">{{ $record->date_of_birth ? $record->date_of_birth->format('m/d/Y') : '' }}</div>
            </td>
            <td>
                <div class="td-label">CONTACT #:</div>
                <div class="td-value">{{ $record->contact_number }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="td-label">INSURANCE/ HMO PROVIDER:</div>
                <div class="td-value">{{ $record->insurance_hmo_provider }}</div>
            </td>
            <td>
                <div class="td-label">RELIGION:</div>
                <div class="td-value">{{ $record->religion }}</div>
            </td>
            <td>
                <div class="td-label">INSURANCE/ HMO NUMBER:</div>
                <div class="td-value">{{ $record->insurance_hmo_number }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="border-right:none;">&nbsp;</td>
            <td style="border-left:none;">
                <div class="td-label">DNR:</div>
                {!! cb((bool)$record->dnr) !!} Yes
                {!! cb(!$record->dnr) !!} No
            </td>
        </tr>
    </table>

    {{-- PRIMARY ASSESSMENT --}}
    <div class="section-header">PRIMARY ASSESSMENT</div>
    <table class="main-table">
        <tr>
            <td style="width:30%; vertical-align:top;">
                <div class="td-label">MENTAL STATUS</div>
                @php $ms = $record->mental_status ?? []; @endphp
                <div class="cb-row">
                    {!! cb(in_array('alert_and_oriented', $ms)) !!} Alert/Oriented
                    &nbsp;
                    {!! cb(in_array('to_pain', $ms)) !!} To Pain
                </div>
                <div class="cb-row">
                    {!! cb(in_array('to_verbal_stimuli', $ms)) !!} To Verbal
                    &nbsp;
                    {!! cb(in_array('unresponsive', $ms)) !!} Unresponsive
                </div>
            </td>
            <td colspan="8">
                <div class="td-label">CHIEF COMPLAINT:</div>
                <div class="td-value" style="min-height:18px;">{{ $record->chief_complaint }}</div>
            </td>
        </tr>

        {{-- Airway / Breathing / Pulse / Skin / Capillary / Pupil / Stroke --}}
        <tr>
            <td style="vertical-align:top; width:12%;">
                <div class="td-label">AIRWAY</div>
                @foreach(['patent'=>'Patent','aspiration_risk'=>'Aspiration Risk','secretions'=>'Secretions','suctioning_required'=>'Suctioning Req.'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->airway === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:12%;">
                <div class="td-label">BREATHING</div>
                @foreach(['normal'=>'Normal','dyspnea'=>'Dyspnea','retractions'=>'Retractions','accessory_muscle_use'=>'Accessory Muscle'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->breathing === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:10%;">
                <div class="td-label">PULSE</div>
                @foreach(['regular'=>'Regular','irregular'=>'Irregular','strong'=>'Strong','weak'=>'Weak'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->pulse === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:13%;">
                <div class="td-label">SKIN COLOR</div>
                @foreach(['normal'=>'Normal','paled'=>'Paled','flushed'=>'Flushed','cyanotic'=>'Cyanotic','mottled'=>'Mottled'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->skin_color === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:13%;">
                <div class="td-label">SKIN MOISTURE</div>
                @foreach(['dry'=>'Dry','moist'=>'Moist','diaphoretic'=>'Diaphoretic'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->skin_moisture === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:10%;">
                <div class="td-label">SKIN TEMP.</div>
                @foreach(['normal'=>'Normal','cool'=>'Cool','hot'=>'Hot'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->skin_temp === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:12%;">
                <div class="td-label">CAPILLARY REFILL</div>
                @foreach(['<2sec'=>'&lt; 2 sec','>2sec'=>'&gt; 2 sec'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->capillary_refill === $k) !!} {!! $v !!}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:10%;">
                <div class="td-label">PUPIL</div>
                @foreach(['pearl'=>'PEARL','constricted'=>'Constricted','dilated'=>'Dilated','unequal'=>'Unequal'] as $k=>$v)
                    <div class="cb-row">{!! cb($record->pupil === $k) !!} {{ $v }}</div>
                @endforeach
            </td>
            <td style="vertical-align:top; width:8%;">
                <div class="td-label">STROKE</div>
                @php $ss = $record->stroke_signs ?? []; @endphp
                @foreach(['facial_droop'=>'Facial droop','arm_drift'=>'Arm drift','speech'=>'Speech','time'=>'Time'] as $k=>$v)
                    <div class="cb-row">{!! cb(in_array($k, $ss)) !!} {{ $v }}</div>
                @endforeach
                <div class="cb-row" style="font-size:5pt;">Time: <span class="ul-sm">{{ $record->stroke_time }}</span></div>
            </td>
        </tr>

        {{-- Interventions + Transport Priority --}}
        <tr>
            <td colspan="5" style="vertical-align:top;">
                @php $intv = $record->interventions ?? []; @endphp
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:50%; vertical-align:top; border:none; padding:0 2px 0 0;">
                            @foreach([
                                'artificial_airway'       => 'Artificial Airway',
                                'abdominal_thrust'        => 'Abdominal Thrust',
                                'bandaging'               => 'Bandaging',
                                'bleeding_control'        => 'Bleeding Control',
                                'bp_monitoring'           => 'BP Monitoring',
                                'cardiac_monitoring'      => 'Cardiac Monitoring',
                                'cold_hot_application'    => 'Cold/ Hot Application',
                                'cpr'                     => 'CPR',
                                'burn_care'               => 'Burn Care',
                                'cervical_collar'         => 'Cervical Collar',
                                'assisting_on_medication' => 'Assisting on Medication',
                            ] as $k => $v)
                                <div class="cb-row">{!! cb(in_array($k, $intv)) !!} {{ $v }}</div>
                            @endforeach
                        </td>
                        <td style="width:50%; vertical-align:top; border:none; padding:0 0 0 2px;">
                            @foreach([
                                'wound_care'           => 'Wound Care',
                                'suctioning'           => 'Suctioning',
                                'splinting_traction'   => 'Splinting/ Traction',
                                'defibrillation'       => 'Defibrillation',
                                'spine_immobilization' => 'Spine Immobilization',
                                'vs_check'             => 'VS Check',
                                'rescue_breathing'     => 'Rescue Breathing',
                                'oxygenation'          => 'Oxygenation LPM',
                                'bvm'                  => 'BVM',
                                'mask'                 => 'Mask',
                                'nrb'                  => 'NRB',
                                'nc'                   => 'NC',
                                'extrication'          => 'Extrication',
                            ] as $k => $v)
                                <div class="cb-row">{!! cb(in_array($k, $intv)) !!} {{ $v }}</div>
                            @endforeach
                            <div class="cb-row" style="font-size:5.5pt;">O2 LPM: <span class="ul-sm">{{ $record->oxygenation_lpm }}</span></div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- TRANSPORT PRIORITY --}}
            <td colspan="4" style="vertical-align:top;">
                <div class="td-label" style="text-align:center;">TRANSPORT PRIORITY</div>
                <table class="priority-table">
                    <tr>
                        <td style="width:25%;">
                            <div>{!! cb($record->transport_priority === 'priority_1_critical') !!}</div>
                            <div class="priority-title">P1: CRITICAL</div>
                            <div style="font-size:5pt; margin-top:1px;">Transport ASAP. Immediate threat to life and function.</div>
                        </td>
                        <td style="width:25%;">
                            <div>{!! cb($record->transport_priority === 'priority_2_emergent') !!}</div>
                            <div class="priority-title">P2: EMERGENT</div>
                            <div style="font-size:5pt; margin-top:1px;">Transport ASAP. Stable now but potential for deterioration.</div>
                        </td>
                        <td style="width:25%;">
                            <div>{!! cb($record->transport_priority === 'priority_3_urgent') !!}</div>
                            <div class="priority-title">P3: URGENT</div>
                            <div style="font-size:5pt; margin-top:1px;">Stable, no immediate threat. Transport need not be immediate.</div>
                        </td>
                        <td style="width:25%;">
                            <div>{!! cb($record->transport_priority === 'priority_4_non_urgent') !!}</div>
                            <div class="priority-title">P4: NON-URGENT</div>
                            <div style="font-size:5pt; margin-top:1px;">Stable, no immediate threat. E.g. Patient Transport.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- SECONDARY ASSESSMENT --}}
    <div class="section-header">SECONDARY ASSESSMENT</div>
    <table class="main-table">
        <tr>
            {{-- SAMPLE --}}
            <td style="width:30%; vertical-align:top; padding:0;">
                <div style="text-align:center; font-weight:bold; font-size:6.3pt; border-bottom:1px solid #333; padding:1px;">MEDICAL CASE</div>
                <table class="sample-table">
                    @foreach([
                        'S' => ['field'=>'sample_s','desc'=>'Signs & Symptoms'],
                        'A' => ['field'=>'sample_a','desc'=>'Allergies'],
                        'M' => ['field'=>'sample_m','desc'=>'Medications'],
                        'P' => ['field'=>'sample_p','desc'=>'Pertinent Past History'],
                        'L' => ['field'=>'sample_l','desc'=>'Last Oral Intake'],
                        'E' => ['field'=>'sample_e','desc'=>'Events Prior to Illness'],
                    ] as $letter => $info)
                    <tr>
                        <td style="width:12px; border:none; border-bottom:1px solid #ccc; vertical-align:top; padding:1px;">
                            <div class="sample-letter">{{ $letter }}</div>
                        </td>
                        <td style="border:none; border-bottom:1px solid #ccc; vertical-align:top; padding:1px;">
                            <div class="sample-desc">{{ $info['desc'] }}</div>
                            <div class="sample-value">{{ $record->{$info['field']} }}</div>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>

            {{-- Body diagram placeholder --}}
            <td style="width:30%; vertical-align:top; text-align:center;">
                <div class="body-diagram">
                    <div style="font-size:5pt; color:#888; margin-bottom:2px;">[Body Diagram - Rule of Nines]</div>
                    <div style="font-size:5pt;">
                        <div>4.5%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4.5%</div>
                        <div style="margin:1px 0;">9%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;9%</div>
                        <div>4.5%&nbsp;&nbsp;4.5%&nbsp;&nbsp;4.5%&nbsp;&nbsp;4.5%</div>
                        <div style="margin:1px 0;">&nbsp;&nbsp;9%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;9%</div>
                    </div>
                    <div style="margin-top:2px; font-size:5.3pt; text-align:left; padding-left:6px;">
                        @php
                            $dispositionMap = [
                                'transported_to_hospital' => 'Transported to Hospital',
                                'released_with_treatment' => 'Released with Treatment',
                                'endorsed_to_ems'         => 'Endorsed to another EMS',
                                'transported_to_other'    => 'Transported to other',
                            ];
                        @endphp
                        @foreach($dispositionMap as $k => $v)
                            <div>
                                {!! cb($record->disposition === $k) !!}
                                {{ $v }}:
                                @if($record->disposition === $k)
                                    <span class="underline">{{ $record->disposition_remarks }}</span>
                                @else
                                    <span class="underline">&nbsp;</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>

            {{-- TRAUMA CASE --}}
            <td style="width:40%; vertical-align:top; padding:0;">
                <div style="text-align:center; font-weight:bold; font-size:6.3pt; border-bottom:1px solid #333; padding:1px;">TRAUMA CASE</div>
                @php $tt = $record->trauma_type ?? []; @endphp
                <div style="padding:1px 3px;">
                    <div class="cb-row">{!! cb(in_array('vehicular_accident',$tt)) !!} Vehicular Accident</div>
                    <div class="cb-row">{!! cb(in_array('trauma_of_other_cause',$tt)) !!} Trauma of other Cause</div>
                </div>
                <div style="padding:1px 3px; margin-top:1px;">
                    <div class="td-label">DCAPBTLS</div>
                    @php $dc = $record->dcapbtls ?? []; @endphp
                    @php
                        $dcapbtlsOptions = [
                            'deformity'            => 'Deformity',
                            'contusion_concussion' => 'Contusion, Concussion',
                            'abrasion'             => 'Abrasion',
                            'puncture_penetrating' => 'Puncture, Penetrating Wound',
                            'burn'                 => 'Burn',
                            'tenderness'           => 'Tenderness',
                            'laceration'           => 'Laceration',
                            'swelling'             => 'Swelling',
                            'open_fracture'        => 'Open Fracture',
                            'closed_fracture'      => 'Closed Fracture',
                            'dislocation'          => 'Dislocation',
                            'sprain_strain'        => 'Sprain, Strain',
                            'alcohol_intoxication' => 'Alcohol Intoxication',
                            'drowning'             => 'Drowning',
                            'electrocution'        => 'Electrocution',
                            'fall'                 => 'Fall',
                            'gunshot_wound'        => 'Gunshot wound/s',
                            'animal_bite'          => 'Animal Bite',
                            'hit_and_run'          => 'Hit & Run',
                            'mauling'              => 'Mauling',
                            'stab_wound'           => 'Stab wound/s',
                        ];
                    @endphp
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:50%; vertical-align:top; border:none; padding:0 2px 0 0;">
                                @foreach(array_slice($dcapbtlsOptions, 0, 11, true) as $k=>$v)
                                    <div class="cb-row">{!! cb(in_array($k,$dc)) !!} {{ $v }}</div>
                                @endforeach
                            </td>
                            <td style="width:50%; vertical-align:top; border:none; padding:0 0 0 2px;">
                                @foreach(array_slice($dcapbtlsOptions, 11, null, true) as $k=>$v)
                                    <div class="cb-row">{!! cb(in_array($k,$dc)) !!} {{ $v }}</div>
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Hospital / Signatures --}}
    <table class="main-table" style="margin-top:0;">
        <tr>
            <td colspan="3">
                <div class="td-label">Hospital Name:</div>
                <div class="td-value">{{ $record->hospital_name }}</div>
            </td>
            <td>
                <div class="td-label">Department:</div>
                <div class="td-value">{{ $record->hospital_department }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <div class="td-label">Hospital Address:</div>
                <div class="td-value">{{ $record->hospital_address }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="td-label">Accomplished and Endorsed by:</div>
                <div class="td-value" style="min-height:13px; margin-top:3px;">{{ $record->accomplished_endorsed_by }}</div>
                <div style="font-size:5pt; color:#555;">(Signature over Printed Name)</div>
            </td>
            <td>
                <div class="td-label">Advanced Call by:</div>
                <div class="td-value" style="min-height:13px; margin-top:3px;">{{ $record->advanced_call_by }}</div>
            </td>
            <td>
                <div class="td-label">Call Received by:</div>
                <div class="td-value" style="min-height:13px; margin-top:3px;">{{ $record->call_received_by }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="td-label">Noted by:</div>
                <div class="td-value" style="min-height:13px; margin-top:3px;">{{ $record->noted_by }}</div>
            </td>
            <td colspan="2">
                <div class="td-label">Endorsement Received by:</div>
                <div class="td-value" style="min-height:13px; margin-top:3px;">{{ $record->endorsement_received_by }}</div>
            </td>
        </tr>
    </table>

</div>{{-- end page 1 --}}


{{-- PAGE 2 --}}
<div class="page page-break">

    {{-- Page 2 top signature / hospital personnel --}}
    <table style="width:100%; border-collapse:collapse; border:1px solid #333;">
        <tr>
            <td style="width:60%; padding:3px 5px; border-right:1px solid #333;">
                <div class="td-label">(Signature over Printed Name)</div>
                <div style="min-height:22px; border-bottom:1px solid #555; margin-top:10px;"></div>
            </td>
            <td style="width:40%; padding:3px 5px; text-align:right; vertical-align:bottom;">
                <div style="font-size:6.5pt; font-weight:bold;">(Hospital Personnel)</div>
                <div style="font-size:8pt; font-weight:bold;">
                    PATIENT CARE REPORT
                </div>
            </td>
        </tr>
    </table>

    {{-- VITAL SIGNS --}}
    <div class="section-header" style="margin-top:3px;">VITAL SIGNS</div>
    <table class="vitals-table">
        <thead>
            <tr>
                <th style="width:15%;">Time</th>
                <th style="width:15%;">Temp</th>
                <th style="width:15%;">Pulse</th>
                <th style="width:20%;">Respiration</th>
                <th style="width:20%;">BP</th>
                <th style="width:15%;">GCS</th>
            </tr>
        </thead>
        <tbody>
            @php $vsLog = $record->vital_signs_log ?? []; @endphp
            @for($i = 0; $i < 7; $i++)
                @php $row = $vsLog[$i] ?? []; @endphp
                <tr>
                    <td style="height:11px;">{{ $row['time'] ?? '' }}</td>
                    <td>{{ $row['temp'] ?? '' }}</td>
                    <td>{{ $row['pulse'] ?? '' }}</td>
                    <td>{{ $row['respiration'] ?? '' }}</td>
                    <td>{{ $row['bp'] ?? '' }}</td>
                    <td>{{ $row['gcs'] ?? '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- NARRATIVE REPORT --}}
    <div class="section-header" style="margin-top:3px;">NARRATIVE REPORT</div>
    <table class="main-table">
        <tr>
            <td style="min-height:55px; padding:3px 5px; font-size:6.3pt; line-height:1.3;">
                {!! nl2br(e($record->narrative_report)) !!}
            </td>
        </tr>
    </table>

    {{-- GLASGOW COMA SCALE --}}
    <div class="section-header" style="margin-top:3px;">GLASGOW COMA SCALE</div>
    <table class="gcs-table">
        <thead>
            <tr>
                <th colspan="2">Eye Opening</th>
                <th colspan="2">Best Verbal Response</th>
                <th colspan="2">Best Motor Response</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Spontaneous</td><td style="text-align:center;">4</td>
                <td>Alert/Oriented</td><td style="text-align:center;">5</td>
                <td>Obeys Command</td><td style="text-align:center;">6</td>
            </tr>
            <tr>
                <td>To Command</td><td style="text-align:center;">3</td>
                <td>Confused</td><td style="text-align:center;">4</td>
                <td>Localize Pain</td><td style="text-align:center;">5</td>
            </tr>
            <tr>
                <td>To Pain</td><td style="text-align:center;">2</td>
                <td>Inappropriate Words</td><td style="text-align:center;">3</td>
                <td>Withdraws from Pain</td><td style="text-align:center;">4</td>
            </tr>
            <tr>
                <td>No Response</td><td style="text-align:center;">1</td>
                <td>Incomprehensible Sounds</td><td style="text-align:center;">2</td>
                <td>Abnormal Flexion (Decorticate)</td><td style="text-align:center;">3</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td>No Response</td><td style="text-align:center;">1</td>
                <td>Abnormal Extension (Decerebrate)</td><td style="text-align:center;">2</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2">&nbsp;</td>
                <td>No Response</td><td style="text-align:center;">1</td>
            </tr>
        </tbody>
    </table>
    <div style="border:1px solid #333; border-top:none; padding:2px 4px; font-size:6.3pt;">
        Eye: <span class="ul-sm">{{ $record->gcs_eye }}</span>
        &nbsp; Verbal: <span class="ul-sm">{{ $record->gcs_verbal }}</span>
        &nbsp; Motor: <span class="ul-sm">{{ $record->gcs_motor }}</span>
        &nbsp; TOTAL: <span class="ul-sm">{{ $record->gcs_total }}</span>
    </div>
    <div style="border:1px solid #333; border-top:none; padding:1px 4px; text-align:center; font-size:5.3pt; font-style:italic;">
        Best Response = 15, Comatose = 8 and below, Totally Unresponsive = 3
    </div>

    {{-- PATIENT VALUABLES --}}
    <div class="section-header" style="margin-top:3px;">PATIENT VALUABLES</div>
    <table class="main-table">
        <tr>
            <td style="min-height:26px; padding:3px 5px; font-size:6.3pt;">
                {!! nl2br(e($record->patient_valuables)) !!}
            </td>
        </tr>
    </table>

    {{-- SUPPLIES USED --}}
    <div class="section-header" style="margin-top:3px;">SUPPLIES USED</div>
    <table class="main-table">
        <tr>
            <td style="min-height:20px; padding:3px 5px; font-size:6.3pt;">
                {!! nl2br(e($record->supplies_used)) !!}
            </td>
        </tr>
    </table>

    {{-- HUMAN / MECHANICAL ERROR --}}
    <table class="main-table" style="margin-top:0;">
        <tr>
            <td style="width:50%;">
                <div class="td-label">HUMAN ERROR:</div>
                <div class="td-value">{{ $record->human_error }}</div>
            </td>
            <td style="width:50%;">
                <div class="td-label">MECHANICAL ERROR:</div>
                <div class="td-value">{{ $record->mechanical_error }}</div>
            </td>
        </tr>
    </table>

    {{-- TYPES OF VEHICLE INVOLVE --}}
    <div class="section-header" style="margin-top:3px;">TYPES OF VEHICLE INVOLVE</div>
    @php $vt = $record->vehicle_types_involved ?? []; @endphp
    <table class="main-table">
        <thead>
            <tr>
                <th style="text-align:center;">TWO WHEELS</th>
                <th style="text-align:center;">THREE WHEELS</th>
                <th style="text-align:center;">FOUR WHEELS</th>
                <th style="text-align:center;">SIX WHEELS &amp; UP</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height:14px; text-align:center;">{!! cb(in_array('two_wheels',$vt)) !!}</td>
                <td style="text-align:center;">{!! cb(in_array('three_wheels',$vt)) !!}</td>
                <td style="text-align:center;">{!! cb(in_array('four_wheels',$vt)) !!}</td>
                <td style="text-align:center;">{!! cb(in_array('six_wheels_and_up',$vt)) !!}</td>
            </tr>
        </tbody>
    </table>

</div>{{-- end page 2 --}}

</body>
</html>
