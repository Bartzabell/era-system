<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PhX, PhPlus, PhFloppyDisk } from '@phosphor-icons/vue'
import CustomInput from '@/components/CustomInput.vue'
import Boombox from '@/components/BoomBox.vue'
import ButtonCode from '@/components/ButtonCode.vue'

interface IncidentReportOption {
    id: number
    label: string
}

const props = defineProps<{
    mode: 'create' | 'edit'
    record: any | null
    incidentReports: IncidentReportOption[]
    hasFullAccess: boolean
}>()

const emit = defineEmits(['close', 'success'])

const isEdit = computed(() => props.mode === 'edit')

const form = useForm({

    // Identifiers
    incident_report_id: props.record?.incident_report_id ?? null,
    case_number: props.record?.case_number ?? '',

    // Case header
    case_date: props.record?.case_date ?? '',
    case_type: props.record?.case_type ?? null,
    tag: props.record?.tag ?? '',

    // Times
    time_dispatch: props.record?.time_dispatch ?? '',
    time_arrived_on_scene: props.record?.time_arrived_on_scene ?? '',
    time_enroute_to_hospital: props.record?.time_enroute_to_hospital ?? '',
    time_arrival_in_hospital: props.record?.time_arrival_in_hospital ?? '',
    time_departure_in_hospital: props.record?.time_departure_in_hospital ?? '',
    time_back_to_base: props.record?.time_back_to_base ?? '',

    // Mileage
    mileage_before_run: props.record?.mileage_before_run ?? null,
    mileage_back_to_base: props.record?.mileage_back_to_base ?? null,

    // Crew
    dispatcher: props.record?.dispatcher ?? '',
    unit: props.record?.unit ?? '',
    transport_officer: props.record?.transport_officer ?? '',
    team_leader: props.record?.team_leader ?? '',
    medics: props.record?.medics ?? '',

    // Patient info
    last_name: props.record?.last_name ?? '',
    first_name: props.record?.first_name ?? '',
    middle_name: props.record?.middle_name ?? '',
    patient_name: props.record?.patient_name ?? '',
    age: props.record?.age ?? null,
    gender: props.record?.gender ?? null,
    civil_status: props.record?.civil_status ?? null,
    address: props.record?.address ?? '',
    informant_legal_guardian: props.record?.informant_legal_guardian ?? '',
    date_of_birth: props.record?.date_of_birth ?? '',
    contact_number: props.record?.contact_number ?? '',
    religion: props.record?.religion ?? '',
    insurance_hmo_provider: props.record?.insurance_hmo_provider ?? '',
    insurance_hmo_number: props.record?.insurance_hmo_number ?? '',
    dnr: props.record?.dnr ?? null,

    // Primary assessment
    mental_status: props.record?.mental_status ?? [] as string[],
    chief_complaint: props.record?.chief_complaint ?? '',
    airway: props.record?.airway ?? null,
    breathing: props.record?.breathing ?? null,
    pulse: props.record?.pulse ?? null,
    skin_color: props.record?.skin_color ?? null,
    skin_moisture: props.record?.skin_moisture ?? null,
    skin_temp: props.record?.skin_temp ?? null,
    capillary_refill: props.record?.capillary_refill ?? null,
    pupil: props.record?.pupil ?? null,
    stroke_signs: props.record?.stroke_signs ?? [] as string[],
    stroke_time: props.record?.stroke_time ?? '',
    interventions: props.record?.interventions ?? [] as string[],
    oxygenation_lpm: props.record?.oxygenation_lpm ?? '',
    transport_priority: props.record?.transport_priority ?? null,

    // SAMPLE
    sample_s: props.record?.sample_s ?? '',
    sample_a: props.record?.sample_a ?? '',
    sample_m: props.record?.sample_m ?? '',
    sample_p: props.record?.sample_p ?? '',
    sample_l: props.record?.sample_l ?? '',
    sample_e: props.record?.sample_e ?? '',

    // Trauma
    trauma_type: props.record?.trauma_type ?? [] as string[],
    dcapbtls: props.record?.dcapbtls ?? [] as string[],

    // Vital signs
    vital_signs_log: props.record?.vital_signs_log ?? [] as any[],
    bp: props.record?.bp ?? '',
    hr: props.record?.hr ?? null,
    rr: props.record?.rr ?? null,
    temperature: props.record?.temperature ?? null,
    o2_sat: props.record?.o2_sat ?? null,

    // GCS
    gcs_eye: props.record?.gcs_eye ?? null,
    gcs_verbal: props.record?.gcs_verbal ?? null,
    gcs_motor: props.record?.gcs_motor ?? null,
    gcs_total: props.record?.gcs_total ?? null,

    // Narrative
    narrative_report: props.record?.narrative_report ?? '',

    // Disposition
    disposition: props.record?.disposition ?? null,
    disposition_remarks: props.record?.disposition_remarks ?? '',
    hospital_name: props.record?.hospital_name ?? '',
    hospital_address: props.record?.hospital_address ?? '',
    hospital_department: props.record?.hospital_department ?? '',
    advanced_call_by: props.record?.advanced_call_by ?? '',
    call_received_by: props.record?.call_received_by ?? '',

    // Signatures
    accomplished_endorsed_by: props.record?.accomplished_endorsed_by ?? '',
    noted_by: props.record?.noted_by ?? '',
    endorsement_received_by: props.record?.endorsement_received_by ?? '',

    // Extras
    patient_valuables: props.record?.patient_valuables ?? '',
    supplies_used: props.record?.supplies_used ?? '',
    human_error: props.record?.human_error ?? '',
    mechanical_error: props.record?.mechanical_error ?? '',
    vehicle_types_involved: props.record?.vehicle_types_involved ?? [] as string[],

    // Clinical
    attending_responder: props.record?.attending_responder ?? '',
    diagnosis: props.record?.diagnosis ?? '',
    treatment_given: props.record?.treatment_given ?? '',
    triage_category: props.record?.triage_category ?? null,
})

// ── Option Lists ───────────────────────────────────────────────────────────

const caseTypeOptions = [
    { value: 'medical_case', label: 'Medical Case' },
    { value: 'trauma_case', label: 'Trauma Case' },
    { value: 'vehicular_accident', label: 'Vehicular Accident' },
    { value: 'patient_conduction', label: 'Patient Conduction' },
    { value: 'special_case', label: 'Special Case' },
]

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
]

const civilStatusOptions = [
    { value: 'single', label: 'Single' },
    { value: 'married', label: 'Married' },
    { value: 'widowed', label: 'Widowed' },
]

const mentalStatusOptions = [
    { value: 'alert_and_oriented', label: 'Alert and Oriented' },
    { value: 'to_pain', label: 'To Pain' },
    { value: 'to_verbal_stimuli', label: 'To Verbal Stimuli' },
    { value: 'unresponsive', label: 'Unresponsive' },
]

const airwayOptions = [
    { value: 'patent', label: 'Patent' },
    { value: 'aspiration_risk', label: 'Aspiration Risk' },
    { value: 'secretions', label: 'Secretions' },
    { value: 'suctioning_required', label: 'Suctioning Required' },
]

const breathingOptions = [
    { value: 'normal', label: 'Normal' },
    { value: 'dyspnea', label: 'Dyspnea' },
    { value: 'retractions', label: 'Retractions' },
    { value: 'accessory_muscle_use', label: 'Accessory / Muscle Use' },
]

const pulseOptions = [
    { value: 'regular', label: 'Regular' },
    { value: 'irregular', label: 'Irregular' },
    { value: 'strong', label: 'Strong' },
    { value: 'weak', label: 'Weak' },
]

const skinColorOptions = [
    { value: 'normal', label: 'Normal' },
    { value: 'paled', label: 'Paled' },
    { value: 'flushed', label: 'Flushed' },
    { value: 'cyanotic', label: 'Cyanotic' },
    { value: 'mottled', label: 'Mottled' },
]

const skinMoistureOptions = [
    { value: 'dry', label: 'Dry' },
    { value: 'moist', label: 'Moist' },
    { value: 'diaphoretic', label: 'Diaphoretic' },
]

const skinTempOptions = [
    { value: 'normal', label: 'Normal' },
    { value: 'cool', label: 'Cool' },
    { value: 'hot', label: 'Hot' },
]

const capillaryRefillOptions = [
    { value: '<2sec', label: '< 2 sec' },
    { value: '>2sec', label: '> 2 sec' },
]

const pupilOptions = [
    { value: 'pearl', label: 'PEARL' },
    { value: 'constricted', label: 'Constricted' },
    { value: 'dilated', label: 'Dilated' },
    { value: 'unequal', label: 'Unequal' },
]

const strokeSignOptions = [
    { value: 'facial_droop', label: 'Facial Droop' },
    { value: 'arm_drift', label: 'Arm Drift' },
    { value: 'speech', label: 'Speech' },
    { value: 'time', label: 'Time' },
]

const interventionOptions = [
    { value: 'artificial_airway', label: 'Artificial Airway' },
    { value: 'abdominal_thrust', label: 'Abdominal Thrust' },
    { value: 'bandaging', label: 'Bandaging' },
    { value: 'bleeding_control', label: 'Bleeding Control' },
    { value: 'bp_monitoring', label: 'BP Monitoring' },
    { value: 'cardiac_monitoring', label: 'Cardiac Monitoring' },
    { value: 'cold_hot_application', label: 'Cold/Hot Application' },
    { value: 'cpr', label: 'CPR' },
    { value: 'burn_care', label: 'Burn Care' },
    { value: 'cervical_collar', label: 'Cervical Collar' },
    { value: 'assisting_on_medication', label: 'Assisting on Medication' },
    { value: 'wound_care', label: 'Wound Care' },
    { value: 'suctioning', label: 'Suctioning' },
    { value: 'splinting_traction', label: 'Splinting / Traction' },
    { value: 'defibrillation', label: 'Defibrillation' },
    { value: 'spine_immobilization', label: 'Spine Immobilization' },
    { value: 'vs_check', label: 'VS Check' },
    { value: 'rescue_breathing', label: 'Rescue Breathing' },
    { value: 'oxygenation_lpm', label: 'Oxygenation LPM' },
    { value: 'bvm', label: 'BVM' },
    { value: 'mask', label: 'Mask' },
    { value: 'nrb', label: 'NRB' },
    { value: 'nc', label: 'NC' },
    { value: 'extrication', label: 'Extrication' },
]

const transportPriorityOptions = [
    { value: 'priority_1_critical', label: 'Priority 1 — Critical', color: 'bg-red-600 border-red-600 text-white' },
    { value: 'priority_2_emergent', label: 'Priority 2 — Emergent', color: 'bg-orange-500 border-orange-500 text-white' },
    { value: 'priority_3_urgent', label: 'Priority 3 — Urgent', color: 'bg-yellow-400 border-yellow-400 text-gray-900' },
    { value: 'priority_4_non_urgent', label: 'Priority 4 — Non-Urgent', color: 'bg-green-500 border-green-500 text-white' },
]

const dcapbtlsOptions = [
    { value: 'deformity', label: 'Deformity' },
    { value: 'contusion_concussion', label: 'Contusion / Concussion' },
    { value: 'abrasion', label: 'Abrasion' },
    { value: 'puncture_penetrating_wound', label: 'Puncture / Penetrating Wound' },
    { value: 'burn', label: 'Burn' },
    { value: 'tenderness', label: 'Tenderness' },
    { value: 'laceration', label: 'Laceration' },
    { value: 'swelling', label: 'Swelling' },
    { value: 'open_fracture', label: 'Open Fracture' },
    { value: 'closed_fracture', label: 'Closed Fracture' },
    { value: 'dislocation', label: 'Dislocation' },
    { value: 'sprain_strain', label: 'Sprain / Strain' },
    { value: 'alcohol_intoxication', label: 'Alcohol Intoxication' },
    { value: 'gunshot_wound', label: 'Gunshot Wound' },
    { value: 'animal_bite', label: 'Animal Bite' },
    { value: 'hit_and_run', label: 'Hit & Run' },
    { value: 'drowning', label: 'Drowning' },
    { value: 'electrocution', label: 'Electrocution' },
    { value: 'mauling', label: 'Mauling' },
    { value: 'stab_wound', label: 'Stab Wound' },
    { value: 'fall', label: 'Fall' },
]

const dispositionOptions = [
    { value: 'admitted', label: 'Admitted' },
    { value: 'discharged', label: 'Discharged' },
    { value: 'deceased', label: 'Deceased' },
    { value: 'referred', label: 'Referred' },
    { value: 'treated_on_site', label: 'Treated On Site' },
    { value: 'transported_to_hospital', label: 'Transported to Hospital' },
    { value: 'released_with_treatment', label: 'Released with Treatment' },
    { value: 'endorsed_to_ems', label: 'Endorsed to Another EMS' },
    { value: 'transported_to_other', label: 'Transported to Other' },
]

const triageOptions = [
    { value: 'red', label: 'Red — Immediate' },
    { value: 'yellow', label: 'Yellow — Delayed' },
    { value: 'green', label: 'Green — Minor' },
    { value: 'black', label: 'Black — Expectant' },
]

const vehicleTypeOptions = [
    { value: 'two_wheels', label: 'Two Wheels' },
    { value: 'three_wheels', label: 'Three Wheels' },
    { value: 'four_wheels', label: 'Four Wheels' },
    { value: 'six_wheels_and_up', label: 'Six Wheels & Up' },
]

// ── Helpers ────────────────────────────────────────────────────────────────

function toggleArray(arr: string[], value: string) {
    const idx = arr.indexOf(value)
    if (idx === -1) arr.push(value)
    else arr.splice(idx, 1)
}

const gcsTotal = computed(() => {
    const e = Number(form.gcs_eye ?? 0)
    const v = Number(form.gcs_verbal ?? 0)
    const m = Number(form.gcs_motor ?? 0)
    const total = e + v + m
    return total >= 3 ? total : null
})

// Vital signs log helpers
function addVitalRow() {
    form.vital_signs_log.push({ time: '', temp: '', pulse: '', respiration: '', bp: '', gcs: '' })
}
function removeVitalRow(index: number) {
    form.vital_signs_log.splice(index, 1)
}

const submitForm = () => {
    if (gcsTotal.value !== null) form.gcs_total = gcsTotal.value

    const options = {
        onSuccess: () => {
            emit('success')
        },
        onError: (errors) => {
            console.log('Validation errors:', errors)
        }
    }

    if (isEdit.value) {
        form.put(`/patient-record-chart/${props.record.id}`, options)  // ✅ Use put()
    } else {
        form.post('/patient-record-chart', options)
    }
}

const closeModal = () => { form.reset(); form.clearErrors(); emit('close') }
</script>

<template>
    <div class="w-full lg:w-[85vw] xl:w-[90vw]">

        <!-- Header -->
        <div
            class="flex items-center justify-between w-full px-8 py-1 bg-form-header border-b border-black dark:border-gray-500 dark:bg-gray-800">
            <h1 class="text-lg lg:text-2xl font-extrabold dark:text-gray-200">
                {{ isEdit ? `Edit PCR : ${record?.chart_code ?? ''}` : 'Create Patient Care Report' }}
            </h1>
            <button @click="closeModal" class="p-3 text-white rounded-full bg-red-500 hover:bg-red-600">
                <PhX :size="16" />
            </button>
        </div>

        <form @submit.prevent="submitForm" class="p-4 bg-form-body dark:bg-gray-800 max-h-[85vh] space-y-6">

            <!-- ── INCIDENT LINK ──────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Linked Incident Report</h2>
                <div class="p-3 border border-dashed border-gray-400 rounded-md">
                    <label class="block m-1 text-base text-gray-600 dark:text-gray-200">
                        Incident Report
                        <span class="text-gray-400 font-normal text-sm"> — optional</span>
                    </label>
                    <Boombox :items="incidentReports" :existing-value="form.incident_report_id" label-field="label"
                        placeholder="Search incident code..."
                        @change="(v: any) => form.incident_report_id = v?.id ?? null" />
                    <p v-if="form.errors.incident_report_id" class="text-sm text-red-500 mt-1">{{
                        form.errors.incident_report_id }}</p>
                    <p class="text-xs text-gray-400 mt-1">Leave blank to create a standalone patient record.</p>
                </div>
            </section>

            <!-- ── CASE HEADER ────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Case Information</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-3">

                    <div>
                        <CustomInput name="Case #" v-model="form.case_number" placeholder="e.g. 2024-001" />
                    </div>
                    <div>
                        <CustomInput name="Date" type="date" v-model="form.case_date" />
                    </div>
                    <div>
                        <CustomInput name="Tag" v-model="form.tag" placeholder="Tag" />
                    </div>

                    <!-- Case Type -->
                    <div class="lg:col-span-3">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Case Type</label>
                        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-2">
                            <button v-for="opt in caseTypeOptions" :key="opt.value" type="button"
                                @click="form.case_type = form.case_type === opt.value ? null : opt.value as any" :class="[
                                    'px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition-all',
                                    form.case_type === opt.value
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white dark:bg-gray-700 text-gray-500 border-gray-200 dark:border-gray-600 hover:border-gray-400'
                                ]">{{ opt.label }}</button>
                        </div>
                        <p v-if="form.errors.case_type" class="text-sm text-red-500 mt-1">{{ form.errors.case_type }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ── TIMES & MILEAGE ────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Times & Mileage</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-4">
                    <div>
                        <CustomInput name="Time Dispatch" type="time" v-model="form.time_dispatch" />
                    </div>
                    <div>
                        <CustomInput name="Arrived on Scene" type="time" v-model="form.time_arrived_on_scene" />
                    </div>
                    <div>
                        <CustomInput name="En-route to Hospital" type="time" v-model="form.time_enroute_to_hospital" />
                    </div>
                    <div>
                        <CustomInput name="Arrival in Hospital" type="time" v-model="form.time_arrival_in_hospital" />
                    </div>
                    <div>
                        <CustomInput name="Departure from Hospital" type="time"
                            v-model="form.time_departure_in_hospital" />
                    </div>
                    <div>
                        <CustomInput name="Back to Base" type="time" v-model="form.time_back_to_base" />
                    </div>
                    <div>
                        <CustomInput name="Mileage Before Run (km)" type="number" step="0.01"
                            v-model="form.mileage_before_run" />
                    </div>
                    <div>
                        <CustomInput name="Mileage Back to Base (km)" type="number" step="0.01"
                            v-model="form.mileage_back_to_base" />
                    </div>
                </div>
            </section>

            <!-- ── CREW ON DUTY ───────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Crew on Duty</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-3">
                    <div>
                        <CustomInput name="Dispatcher" v-model="form.dispatcher" />
                    </div>
                    <div>
                        <CustomInput name="Unit" v-model="form.unit" />
                    </div>
                    <div>
                        <CustomInput name="Transport Officer" v-model="form.transport_officer" />
                    </div>
                    <div>
                        <CustomInput name="Team Leader" v-model="form.team_leader" />
                    </div>
                    <div class="lg:col-span-2">
                        <CustomInput name="Medics" v-model="form.medics" />
                    </div>
                </div>
            </section>

            <!-- ── PATIENT INFO ───────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Patient Information</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-3">

                    <div>
                        <CustomInput name="Last Name" v-model="form.last_name" required />
                    </div>
                    <div>
                        <CustomInput name="First Name" v-model="form.first_name" required />
                    </div>
                    <div>
                        <CustomInput name="Middle Name" v-model="form.middle_name" />
                    </div>

                    <div>
                        <CustomInput name="Age" type="number" v-model="form.age" />
                        <p v-if="form.errors.age" class="text-sm text-red-500 mt-1">{{ form.errors.age }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Gender</label>
                        <select v-model="form.gender"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                            <option :value="null">— Select —</option>
                            <option v-for="opt in genderOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Civil Status</label>
                        <select v-model="form.civil_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                            <option :value="null">— Select —</option>
                            <option v-for="opt in civilStatusOptions" :key="opt.value" :value="opt.value">{{ opt.label
                                }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <CustomInput name="Address" v-model="form.address" />
                    </div>

                    <div>
                        <CustomInput name="Informant / Legal Guardian" v-model="form.informant_legal_guardian" />
                    </div>

                    <div>
                        <CustomInput name="Date of Birth" type="date" v-model="form.date_of_birth" />
                    </div>
                    <div>
                        <CustomInput name="Contact #" v-model="form.contact_number" />
                    </div>
                    <div>
                        <CustomInput name="Religion" v-model="form.religion" />
                    </div>
                    <div>
                        <CustomInput name="Insurance / HMO Provider" v-model="form.insurance_hmo_provider" />
                    </div>
                    <div>
                        <CustomInput name="Insurance / HMO Number" v-model="form.insurance_hmo_number" />
                    </div>

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">DNR</label>
                        <div class="flex gap-4 mt-1">
                            <label class="flex items-center gap-2 text-gray-600 dark:text-gray-200">
                                <input type="radio" :value="true" v-model="form.dnr" /> Yes
                            </label>
                            <label class="flex items-center gap-2 text-gray-600 dark:text-gray-200">
                                <input type="radio" :value="false" v-model="form.dnr" /> No
                            </label>
                        </div>
                    </div>

                    <!-- Triage (internal MDRRMO category) -->
                    <div class="lg:col-span-3">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Triage Category</label>
                        <div class="flex gap-2 flex-col lg:flex-row lg:flex-wrap">
                            <button v-for="opt in triageOptions" :key="opt.value" type="button"
                                @click="form.triage_category = (form.triage_category === opt.value ? null : opt.value) as any"
                                :class="[
                                    'px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition-all',
                                    form.triage_category === opt.value
                                        ? { red: 'bg-red-500 text-white border-red-500', yellow: 'bg-yellow-400 text-gray-900 border-yellow-400', green: 'bg-green-500 text-white border-green-500', black: 'bg-gray-900 text-white border-gray-900' }[opt.value]
                                        : 'bg-white dark:bg-gray-700 text-gray-500 border-gray-200 dark:border-gray-600 hover:border-gray-400'
                                ]">{{ opt.label }}</button>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ── PRIMARY ASSESSMENT ─────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Primary Assessment</h2>
                <div class="p-3 border border-dashed border-gray-400 rounded-md space-y-4">

                    <!-- Mental Status -->
                    <div>
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Mental
                            Status</label>
                        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-2">
                            <label v-for="opt in mentalStatusOptions" :key="opt.value"
                                class="flex items-center gap-1 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" :value="opt.value" v-model="form.mental_status"
                                    class="rounded" />
                                {{ opt.label }}
                            </label>
                        </div>
                    </div>

                    <!-- Chief Complaint -->
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Chief Complaint</label>
                        <textarea v-model="form.chief_complaint" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Describe the chief complaint..." />
                        <p v-if="form.errors.chief_complaint" class="text-sm text-red-500 mt-1">{{
                            form.errors.chief_complaint }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <!-- Airway -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Airway</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in airwayOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.airway" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Breathing -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Breathing</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in breathingOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.breathing" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Pulse -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Pulse</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in pulseOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.pulse" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                        <!-- Skin Color -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Skin Color</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in skinColorOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.skin_color" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Skin Moisture -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Skin Moisture</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in skinMoistureOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.skin_moisture" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Skin Temp -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Skin Temp.</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in skinTempOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.skin_temp" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Capillary Refill -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Capillary Refill</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in capillaryRefillOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.capillary_refill" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>

                        <!-- Pupil -->
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Pupil</label>
                            <div class="flex flex-col gap-1">
                                <label v-for="opt in pupilOptions" :key="opt.value"
                                    class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" :value="opt.value" v-model="form.pupil" />
                                    {{ opt.label }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Stroke Signs -->
                    <div>
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Stroke
                            Signs</label>
                        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-3">
                            <label v-for="opt in strokeSignOptions" :key="opt.value"
                                class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" :value="opt.value" v-model="form.stroke_signs" class="rounded" />
                                {{ opt.label }}
                            </label>
                            <div v-if="form.stroke_signs.includes('time')" class="w-32">
                                <CustomInput name="Stroke Time" v-model="form.stroke_time" placeholder="HH:MM" />
                            </div>
                        </div>
                    </div>

                    <!-- Interventions -->
                    <div>
                        <label
                            class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Interventions</label>
                        <div class="grid grid-cols-1 gap-1 lg:grid-cols-4">
                            <label v-for="opt in interventionOptions" :key="opt.value"
                                class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" :value="opt.value" v-model="form.interventions"
                                    class="rounded" />
                                {{ opt.label }}
                            </label>
                        </div>
                        <div v-if="form.interventions.includes('oxygenation_lpm')" class="mt-2 w-40">
                            <CustomInput name="LPM" v-model="form.oxygenation_lpm" placeholder="e.g. 4" />
                        </div>
                    </div>

                    <!-- Transport Priority -->
                    <div>
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Transport
                            Priority</label>
                        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-2">
                            <button v-for="opt in transportPriorityOptions" :key="opt.value" type="button"
                                @click="form.transport_priority = form.transport_priority === opt.value ? null : opt.value as any"
                                :class="[
                                    'px-3 py-1.5 text-base font-semibold rounded-full border-2 transition-all',
                                    form.transport_priority === opt.value
                                        ? opt.color
                                        : 'bg-white dark:bg-gray-700 text-gray-500 border-gray-200 dark:border-gray-600 hover:border-gray-400'
                                ]">{{ opt.label }}</button>
                        </div>
                        <p v-if="form.errors.transport_priority" class="text-base text-red-500 mt-1">{{
                            form.errors.transport_priority }}</p>
                    </div>

                </div>
            </section>

            <!-- ── SECONDARY ASSESSMENT — SAMPLE ──────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Secondary Assessment — SAMPLE</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">S — Signs &amp;
                            Symptoms</label>
                        <textarea v-model="form.sample_s" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">A — Allergies</label>
                        <textarea v-model="form.sample_a" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">M — Medications</label>
                        <textarea v-model="form.sample_m" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">P — Pertinent Past
                            History</label>
                        <textarea v-model="form.sample_p" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">L — Last Oral Intake</label>
                        <textarea v-model="form.sample_l" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">E — Events Leading to
                            Illness / Injury</label>
                        <textarea v-model="form.sample_e" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>

                    <!-- Trauma sub-type -->
                    <div>
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Trauma Case
                            Type</label>
                        <div class="flex flex-col gap-1">
                            <label
                                class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" value="vehicular_accident" v-model="form.trauma_type"
                                    class="rounded" /> Vehicular Accident
                            </label>
                            <label
                                class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" value="trauma_of_other_cause" v-model="form.trauma_type"
                                    class="rounded" /> Trauma of Other Cause
                            </label>
                        </div>
                    </div>

                    <!-- DCAPBTLS -->
                    <div>
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">DCAPBTLS
                            Findings</label>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-1">
                            <label v-for="opt in dcapbtlsOptions" :key="opt.value"
                                class="flex items-center gap-2 text-base text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" :value="opt.value" v-model="form.dcapbtls" class="rounded" />
                                {{ opt.label }}
                            </label>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ── VITAL SIGNS ────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Vital Signs</h2>
                <div class="p-3 border border-dashed border-gray-400 rounded-md space-y-3">

                    <!-- Snapshot vitals -->
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                        <div>
                            <CustomInput name="BP (mmHg)" placeholder="120/80" v-model="form.bp" />
                            <p v-if="form.errors.bp" class="text-sm text-red-500 mt-1">{{ form.errors.bp }}</p>
                        </div>
                        <div>
                            <CustomInput name="HR (bpm)" type="number" v-model="form.hr" />
                            <p v-if="form.errors.hr" class="text-sm text-red-500 mt-1">{{ form.errors.hr }}</p>
                        </div>
                        <div>
                            <CustomInput name="RR (breaths/min)" type="number" v-model="form.rr" />
                            <p v-if="form.errors.rr" class="text-sm text-red-500 mt-1">{{ form.errors.rr }}</p>
                        </div>
                        <div>
                            <CustomInput name="Temp (°C)" type="number" step="0.1" v-model="form.temperature" />
                            <p v-if="form.errors.temperature" class="text-sm text-red-500 mt-1">{{
                                form.errors.temperature }}</p>
                        </div>
                        <div>
                            <CustomInput name="O₂ Sat (%)" type="number" v-model="form.o2_sat" />
                            <p v-if="form.errors.o2_sat" class="text-sm text-red-500 mt-1">{{ form.errors.o2_sat }}</p>
                        </div>
                    </div>

                    <!-- Time-series log -->
                    <div>
                        <div class="flex flex-col lg:flex-row gap-3 lg:gap-0 items-center justify-between mb-1">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Vital Signs Log
                                (time-series)</label>
                            <button type="button" @click="addVitalRow"
                                class="px-2 py-1 text-base lg:text-xs bg-blue-500 hover:bg-blue-600 text-white rounded">
                                + Add Row
                            </button>
                        </div>

                        <div v-if="form.vital_signs_log.length > 0">

                            <!-- Desktop / lg+: table layout -->
                            <div class="hidden lg:block overflow-x-auto">
                                <table class="w-full text-sm text-left border border-gray-300 rounded">
                                    <thead class="bg-gray-100 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">Time
                                            </th>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">Temp
                                            </th>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">Pulse
                                            </th>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">
                                                Respiration</th>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">BP</th>
                                            <th class="px-2 py-1 font-semibold text-gray-600 dark:text-gray-200">GCS
                                            </th>
                                            <th class="px-2 py-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, i) in form.vital_signs_log" :key="'table-' + i"
                                            class="border-t border-gray-200">
                                            <td class="px-1 py-1"><input type="time" v-model="row.time"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" v-model="row.temp"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white"
                                                    step="0.1" placeholder="°C" /></td>
                                            <td class="px-1 py-1"><input type="number" v-model="row.pulse"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white"
                                                    placeholder="bpm" /></td>
                                            <td class="px-1 py-1"><input type="number" v-model="row.respiration"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white"
                                                    placeholder="br/min" /></td>
                                            <td class="px-1 py-1"><input type="text" v-model="row.bp"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white"
                                                    placeholder="120/80" /></td>
                                            <td class="px-1 py-1"><input type="number" v-model="row.gcs"
                                                    class="w-full px-1 py-0.5 border border-gray-300 rounded text-sm dark:bg-gray-700 dark:text-white"
                                                    min="3" max="15" /></td>
                                            <td class="px-1 py-1 text-center">
                                                <button type="button" @click="removeVitalRow(i)"
                                                    class="text-red-500 hover:text-red-700 text-xs font-bold">✕</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile / below lg: stacked field-per-row layout -->
                            <div class="lg:hidden space-y-3">
                                <div v-for="(row, i) in form.vital_signs_log" :key="'card-' + i"
                                    class="border border-gray-300 rounded p-3 dark:bg-gray-800">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-200">Entry {{ i
                                            + 1 }}</span>
                                        <button type="button" @click="removeVitalRow(i)"
                                            class="text-red-500 hover:text-red-700 text-sm font-bold">✕ Remove</button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-2">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Time</label>
                                            <input type="time" v-model="row.time"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Temp</label>
                                            <input type="number" v-model="row.temp" step="0.1" placeholder="°C"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pulse</label>
                                            <input type="number" v-model="row.pulse" placeholder="bpm"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Respiration</label>
                                            <input type="number" v-model="row.respiration" placeholder="br/min"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">BP</label>
                                            <input type="text" v-model="row.bp" placeholder="120/80"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">GCS</label>
                                            <input type="number" v-model="row.gcs" min="3" max="15"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-base dark:bg-gray-700 dark:text-white" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <p v-else class="text-xs text-gray-400 mt-1">No rows yet. Click "+ Add Row" to record serial
                            vitals.</p>
                    </div>
                </div>
            </section>

            <!-- ── GLASGOW COMA SCALE ──────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Glasgow Coma Scale</h2>
                <div class="p-3 border border-dashed border-gray-400 rounded-md">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-4 items-end">
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Eye Opening <span
                                    class="text-xs text-gray-400">(1-4)</span></label>
                            <select v-model="form.gcs_eye"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                                <option :value="null">—</option>
                                <option :value="4">4 — Spontaneous</option>
                                <option :value="3">3 — To Command</option>
                                <option :value="2">2 — To Pain</option>
                                <option :value="1">1 — No Response</option>
                            </select>
                        </div>
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Best Verbal <span
                                    class="text-xs text-gray-400">(1-5)</span></label>
                            <select v-model="form.gcs_verbal"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                                <option :value="null">—</option>
                                <option :value="5">5 — Alert &amp; Oriented</option>
                                <option :value="4">4 — Confused</option>
                                <option :value="3">3 — Inappropriate Words</option>
                                <option :value="2">2 — Incomprehensible Sounds</option>
                                <option :value="1">1 — No Response</option>
                            </select>
                        </div>
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Best Motor <span
                                    class="text-xs text-gray-400">(1-6)</span></label>
                            <select v-model="form.gcs_motor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                                <option :value="null">—</option>
                                <option :value="6">6 — Obeys Command</option>
                                <option :value="5">5 — Localize Pain</option>
                                <option :value="4">4 — Withdraws from Pain</option>
                                <option :value="3">3 — Abnormal Flexion</option>
                                <option :value="2">2 — Abnormal Extension</option>
                                <option :value="1">1 — No Response</option>
                            </select>
                        </div>
                        <div>
                            <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Total</label>
                            <div class="w-full px-3 py-2 border border-gray-200 rounded-md text-base font-bold text-center dark:bg-gray-700 dark:text-white"
                                :class="{
                                    'text-green-600': (gcsTotal ?? 0) >= 13,
                                    'text-yellow-600': (gcsTotal ?? 0) >= 9 && (gcsTotal ?? 0) <= 12,
                                    'text-red-600': (gcsTotal ?? 0) > 0 && (gcsTotal ?? 0) <= 8,
                                }">
                                {{ gcsTotal ?? '—' }}
                            </div>
                            <p class="text-xs text-gray-400 mt-1 text-center">15 = Best · ≤8 = Comatose · 3 =
                                Unresponsive</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── NARRATIVE ───────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Narrative Report</h2>
                <div class="p-3 border border-dashed border-gray-400 rounded-md">
                    <textarea v-model="form.narrative_report" rows="5"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                        placeholder="Detailed narrative of the incident, patient condition, and interventions..." />
                </div>
            </section>

            <!-- ── CLINICAL DETAILS ────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Clinical Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Diagnosis / Working
                            Impression</label>
                        <textarea v-model="form.diagnosis" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Diagnosis or working impression..." />
                        <p v-if="form.errors.diagnosis" class="text-sm text-red-500 mt-1">{{ form.errors.diagnosis }}
                        </p>
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Treatment Given</label>
                        <textarea v-model="form.treatment_given" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Medications and interventions provided..." />
                        <p v-if="form.errors.treatment_given" class="text-sm text-red-500 mt-1">{{
                            form.errors.treatment_given }}</p>
                    </div>
                </div>
            </section>

            <!-- ── DISPOSITION ────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Disposition &amp; Hospital</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Disposition</label>
                        <select v-model="form.disposition"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                            <option :value="null">— Select —</option>
                            <option v-for="opt in dispositionOptions" :key="opt.value" :value="opt.value">{{ opt.label
                                }}</option>
                        </select>
                        <p v-if="form.errors.disposition" class="text-sm text-red-500 mt-1">{{ form.errors.disposition
                            }}</p>
                    </div>

                    <div>
                        <CustomInput name="Attending Responder / Medic" v-model="form.attending_responder" />
                        <p v-if="form.errors.attending_responder" class="text-sm text-red-500 mt-1">{{
                            form.errors.attending_responder }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Disposition Remarks</label>
                        <textarea v-model="form.disposition_remarks" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="e.g. Referred to which hospital, ward, etc..." />
                    </div>

                    <div>
                        <CustomInput name="Hospital Name" v-model="form.hospital_name" />
                    </div>
                    <div>
                        <CustomInput name="Hospital Department" v-model="form.hospital_department" />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Hospital Address</label>
                        <textarea v-model="form.hospital_address" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <CustomInput name="Advanced Call By" v-model="form.advanced_call_by" />
                    </div>
                    <div>
                        <CustomInput name="Call Received By" v-model="form.call_received_by" />
                    </div>
                </div>
            </section>

            <!-- ── PATIENT VALUABLES & SUPPLIES ───────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Patient Valuables &amp; Supplies</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Patient Valuables</label>
                        <textarea v-model="form.patient_valuables" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="List of patient valuables..." />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Supplies Used</label>
                        <textarea v-model="form.supplies_used" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="List of supplies used..." />
                    </div>
                </div>
            </section>

            <!-- ── ERRORS & VEHICLE ───────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Errors &amp; Vehicle</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Human Error</label>
                        <textarea v-model="form.human_error" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Mechanical Error</label>
                        <textarea v-model="form.mechanical_error" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none" />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block m-1 text-base font-medium text-gray-600 dark:text-gray-200">Types of Vehicle
                            Involved</label>
                        <div class="flex flex-wrap gap-4">
                            <label v-for="opt in vehicleTypeOptions" :key="opt.value"
                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" :value="opt.value" v-model="form.vehicle_types_involved"
                                    class="rounded" />
                                {{ opt.label }}
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── SIGNATURES ─────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Signatures</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-3">
                    <div>
                        <CustomInput name="Accomplished &amp; Endorsed by" v-model="form.accomplished_endorsed_by" />
                    </div>
                    <div>
                        <CustomInput name="Noted by" v-model="form.noted_by" />
                    </div>
                    <div>
                        <CustomInput name="Endorsement Received by" v-model="form.endorsement_received_by" />
                    </div>
                </div>
            </section>

            <!-- ── SUBMIT ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-center gap-2 mt-4 pb-4">
                <ButtonCode type="submit" :icon="isEdit ? PhFloppyDisk : PhPlus"
                    color="bg-orange-500 hover:bg-orange-600" :text="isEdit ? 'Update Record' : 'Save Record'" />
                <ButtonCode type="button" color="bg-red-500 hover:bg-red-600" text="Cancel" @click="closeModal" />
            </div>

        </form>
    </div>
</template>
