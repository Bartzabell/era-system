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
    _method:              isEdit.value ? 'PUT' : undefined,
    incident_report_id:   props.record?.incident_report_id   ?? null,
    patient_name:         props.record?.patient_name         ?? '',
    age:                  props.record?.age                  ?? null,
    sex:                  props.record?.sex                  ?? null,
    address:              props.record?.address              ?? '',
    triage_category:      props.record?.triage_category      ?? null,
    chief_complaint:      props.record?.chief_complaint       ?? '',
    diagnosis:            props.record?.diagnosis             ?? '',
    bp:                   props.record?.bp                   ?? '',
    hr:                   props.record?.hr                   ?? null,
    rr:                   props.record?.rr                   ?? null,
    temperature:          props.record?.temperature          ?? null,
    o2_sat:               props.record?.o2_sat               ?? null,
    treatment_given:      props.record?.treatment_given      ?? '',
    disposition:          props.record?.disposition          ?? null,
    disposition_remarks:  props.record?.disposition_remarks  ?? '',
    attending_responder:  props.record?.attending_responder  ?? '',
})

const triageOptions = [
    { value: 'red',    label: 'Red — Immediate'  },
    { value: 'yellow', label: 'Yellow — Delayed'  },
    { value: 'green',  label: 'Green — Minor'     },
    { value: 'black',  label: 'Black — Expectant' },
]

const dispositionOptions = [
    { value: 'admitted',        label: 'Admitted'        },
    { value: 'discharged',      label: 'Discharged'      },
    { value: 'deceased',        label: 'Deceased'        },
    { value: 'referred',        label: 'Referred'        },
    { value: 'treated_on_site', label: 'Treated On Site' },
]

const sexOptions = [
    { value: 'male',   label: 'Male'   },
    { value: 'female', label: 'Female' },
    { value: 'other',  label: 'Other'  },
]

const triageBorderClass = computed(() => ({
    red:    'border-red-300',
    yellow: 'border-yellow-300',
    green:  'border-green-400',
    black:  'border-gray-700',
} as Record<string, string>)[form.triage_category ?? ''] ?? 'border-dashed border-gray-400')

const submitForm = () => {
    const options = { onSuccess: () => emit('success') }
    isEdit.value
        ? form.post(`/patient-record-chart/${props.record.id}`, options)
        : form.post('/patient-record-chart', options)
}

const closeModal = () => { form.reset(); form.clearErrors(); emit('close') }
</script>

<template>
    <div class="w-full lg:w-[75vw] xl:w-[80vw]">

        <!-- Header -->
        <div class="flex items-center justify-between w-full px-8 py-1 bg-form-header border-b border-black dark:border-gray-500 dark:bg-gray-800">
            <h1 class="text-lg lg:text-2xl font-extrabold dark:text-gray-200">
                {{ isEdit ? `Edit Record : ${record?.chart_code ?? ''}` : 'Create Patient Record Chart' }}
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
                    <Boombox
                        :items="incidentReports"
                        :existing-value="form.incident_report_id"
                        label-field="label"
                        placeholder="Search incident code..."
                        @change="(v: any) => form.incident_report_id = v?.id ?? null"
                    />
                    <p v-if="form.errors.incident_report_id" class="text-sm text-red-500 mt-1">
                        {{ form.errors.incident_report_id }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Leave blank to create a standalone patient record.</p>
                </div>
            </section>

            <!-- ── PATIENT INFO ───────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Patient Information</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-3">

                    <div class="lg:col-span-3">
                        <CustomInput name="Patient Name" v-model="form.patient_name" required />
                        <p v-if="form.errors.patient_name" class="text-sm text-red-500 mt-1">{{ form.errors.patient_name }}</p>
                    </div>

                    <div>
                        <CustomInput name="Age" type="number" v-model="form.age" />
                        <p v-if="form.errors.age" class="text-sm text-red-500 mt-1">{{ form.errors.age }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Sex</label>
                        <select v-model="form.sex"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                            <option :value="null">— Select —</option>
                            <option v-for="opt in sexOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <div>
                        <!-- Triage category -->
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Triage Category</label>
                        <div class="flex gap-2 flex-wrap">
                            <button
                                v-for="opt in triageOptions"
                                :key="opt.value"
                                type="button"
                                @click="form.triage_category = (form.triage_category === opt.value ? null : opt.value) as any"
                                :class="[
                                    'px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition-all',
                                    form.triage_category === opt.value
                                        ? {
                                            red:    'bg-red-500 text-white border-red-500',
                                            yellow: 'bg-yellow-400 text-gray-900 border-yellow-400',
                                            green:  'bg-green-500 text-white border-green-500',
                                            black:  'bg-gray-900 text-white border-gray-900',
                                          }[opt.value]
                                        : 'bg-white dark:bg-gray-700 text-gray-500 border-gray-200 dark:border-gray-600 hover:border-gray-400'
                                ]"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                        <p v-if="form.errors.triage_category" class="text-sm text-red-500 mt-1">{{ form.errors.triage_category }}</p>
                    </div>

                    <div class="lg:col-span-3">
                        <CustomInput name="Address" v-model="form.address" />
                    </div>
                </div>
            </section>

            <!-- ── VITAL SIGNS ────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Vital Signs</h2>
                <div class="grid grid-cols-2 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-5">
                    <div>
                        <CustomInput name="BP (mmHg)" placeholder="120/80" v-model="form.bp" />
                        <p v-if="form.errors.bp" class="text-sm text-red-500 mt-1">{{ form.errors.bp }}</p>
                    </div>
                    <div>
                        <CustomInput name="HR (bpm)" type="number" v-model="form.hr" />
                        <p v-if="form.errors.hr" class="text-sm text-red-500 mt-1">{{ form.errors.hr }}</p>
                    </div>
                    <div>
                        <CustomInput name="RR (bpm)" type="number" v-model="form.rr" />
                        <p v-if="form.errors.rr" class="text-sm text-red-500 mt-1">{{ form.errors.rr }}</p>
                    </div>
                    <div>
                        <CustomInput name="Temp (°C)" type="number" step="0.1" v-model="form.temperature" />
                        <p v-if="form.errors.temperature" class="text-sm text-red-500 mt-1">{{ form.errors.temperature }}</p>
                    </div>
                    <div>
                        <CustomInput name="O₂ Sat (%)" type="number" v-model="form.o2_sat" />
                        <p v-if="form.errors.o2_sat" class="text-sm text-red-500 mt-1">{{ form.errors.o2_sat }}</p>
                    </div>
                </div>
            </section>

            <!-- ── CLINICAL ───────────────────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Clinical Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Chief Complaint</label>
                        <textarea
                            v-model="form.chief_complaint"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Describe the chief complaint..."
                        />
                        <p v-if="form.errors.chief_complaint" class="text-sm text-red-500 mt-1">{{ form.errors.chief_complaint }}</p>
                    </div>
                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Diagnosis</label>
                        <textarea
                            v-model="form.diagnosis"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Diagnosis or working impression..."
                        />
                        <p v-if="form.errors.diagnosis" class="text-sm text-red-500 mt-1">{{ form.errors.diagnosis }}</p>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Treatment Given</label>
                        <textarea
                            v-model="form.treatment_given"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Medications and interventions provided..."
                        />
                        <p v-if="form.errors.treatment_given" class="text-sm text-red-500 mt-1">{{ form.errors.treatment_given }}</p>
                    </div>
                </div>
            </section>

            <!-- ── DISPOSITION & RESPONDER ────────────────────────────────── -->
            <section>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Disposition & Responder</h2>
                <div class="grid grid-cols-1 gap-3 p-3 border border-dashed border-gray-400 rounded-md lg:grid-cols-2">

                    <div>
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Disposition</label>
                        <select v-model="form.disposition"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white">
                            <option :value="null">— Select —</option>
                            <option v-for="opt in dispositionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.disposition" class="text-sm text-red-500 mt-1">{{ form.errors.disposition }}</p>
                    </div>

                    <div>
                        <CustomInput name="Attending Responder / Medic" v-model="form.attending_responder" />
                        <p v-if="form.errors.attending_responder" class="text-sm text-red-500 mt-1">{{ form.errors.attending_responder }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block m-1 text-base text-gray-600 dark:text-gray-200">Disposition Remarks</label>
                        <textarea
                            v-model="form.disposition_remarks"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-base dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Referred to which hospital, ward, etc..."
                        />
                        <p v-if="form.errors.disposition_remarks" class="text-sm text-red-500 mt-1">{{ form.errors.disposition_remarks }}</p>
                    </div>
                </div>
            </section>

            <!-- ── SUBMIT ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-center gap-2 mt-4">
                <ButtonCode
                    type="submit"
                    :icon="isEdit ? PhFloppyDisk : PhPlus"
                    color="bg-orange-500 hover:bg-orange-600"
                    :text="isEdit ? 'Update Record' : 'Save Record'"
                />
                <ButtonCode
                    type="button"
                    color="bg-red-500 hover:bg-red-600"
                    text="Cancel"
                    @click="closeModal"
                />
            </div>

        </form>
    </div>
</template>
