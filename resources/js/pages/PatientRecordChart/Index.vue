<script setup lang="ts">
import { ref, computed, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import FormModal from './Partials/FormModal.vue'
import { Button } from '@/components/ui/button'
import { PhPencil, PhTrash, PhPlus } from '@phosphor-icons/vue'

// ── Types ──────────────────────────────────────────────────────────────────

interface IncidentReportOption {
    id: number
    label: string
}

interface PatientRecord {
    id: number
    chart_code: string
    patient_name: string
    age: number | null
    sex: string | null
    address: string | null
    triage_category: 'red' | 'yellow' | 'green' | 'black' | null
    chief_complaint: string | null
    diagnosis: string | null
    bp: string | null
    hr: number | null
    rr: number | null
    temperature: number | null
    o2_sat: number | null
    treatment_given: string | null
    disposition: string | null
    disposition_remarks: string | null
    attending_responder: string | null
    incident_report_id: number | null
    incident_report: { id: number; incident_code: string } | null
    creator: { first_name: string; last_name: string } | null
    created_at: string
}

const props = defineProps<{
    records: object
    incidentReports: IncidentReportOption[]
    hasFullAccess: boolean
    filters: Record<string, any>
}>()

// ── Breadcrumbs ────────────────────────────────────────────────────────────

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Patient Record Charts', href: '/patient-record-chart' },
]

// ── Tabs ───────────────────────────────────────────────────────────────────

type TabKey = 'all' | 'red' | 'yellow' | 'green' | 'black'

const tabs: { key: TabKey; label: string }[] = [
    { key: 'all',    label: 'All'         },
    { key: 'red',    label: 'Immediate'    },
    { key: 'yellow', label: 'Delayed' },
    { key: 'green',  label: 'Minor'  },
    { key: 'black',  label: 'Expectant'  },
]

const tabColors: Record<TabKey, { dot: string; border: string; bg: string }> = {
    all:    { dot: '#6b7280', border: '#6b7280', bg: '#f9fafb' },
    red:    { dot: '#ef4444', border: '#ef4444', bg: '#fef2f2' },
    yellow: { dot: '#f59e0b', border: '#f59e0b', bg: '#fefce8' },
    green:  { dot: '#22c55e', border: '#22c55e', bg: '#f0fdf4' },
    black:  { dot: '#111827', border: '#374151', bg: '#f3f4f6' },
}

const activeTab = ref<TabKey>((props.filters?.tab as TabKey) ?? 'all')

// ── Filters ────────────────────────────────────────────────────────────────

const localFilters = ref(props.filters)

const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get('/patient-record-chart', { ...newFilters, tab: activeTab.value }, {
        preserveState: true, replace: true,
    })
}

const switchTab = (key: TabKey) => {
    activeTab.value = key
    router.get('/patient-record-chart', { ...localFilters.value, tab: key }, {
        preserveState: true, replace: true,
    })
}

// ── Data ───────────────────────────────────────────────────────────────────

const allRows = computed<PatientRecord[]>(() => (props.records as any)?.data ?? [])

const triageCounts = computed(() => {
    const counts: Record<string, number> = {}
    allRows.value.forEach(r => {
        if (r.triage_category) counts[r.triage_category] = (counts[r.triage_category] ?? 0) + 1
    })
    return counts
})

// ── Modal state ────────────────────────────────────────────────────────────

const isFormVisible   = ref(false)
const showDeleteModal = ref(false)
const formMode        = ref<'create' | 'edit'>('create')
const currentRecord   = ref<PatientRecord | null>(null)

const openCreateModal = () => { formMode.value = 'create'; currentRecord.value = null; isFormVisible.value = true }
const openEditModal   = (r: PatientRecord) => { formMode.value = 'edit'; currentRecord.value = r; isFormVisible.value = true }
const closeFormModal  = () => { isFormVisible.value = false; currentRecord.value = null }
const openDeleteModal = (r: PatientRecord) => { currentRecord.value = r; showDeleteModal.value = true }
const deleteRecord    = () => router.delete(`/patient-record-chart/${currentRecord.value!.id}`, {
    onSuccess: () => { showDeleteModal.value = false },
})

// ── Helpers ────────────────────────────────────────────────────────────────

const triageBadge = (cat: string | null) => {
    const map: Record<string, string> = {
        red:    'bg-red-100 text-red-700 border border-red-200',
        yellow: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        green:  'bg-green-100 text-green-700 border border-green-200',
        black:  'bg-gray-900 text-white border border-gray-700',
    }
    return map[cat ?? ''] ?? 'bg-gray-100 text-gray-500 border border-gray-200'
}

const triageLabel = (cat: string | null) => {
    const map: Record<string, string> = {
        red: 'Red — Immediate', yellow: 'Yellow — Delayed',
        green: 'Green — Minor', black: 'Black — Expectant',
    }
    return map[cat ?? ''] ?? '—'
}

const dispositionBadge = (d: string | null) => {
    const map: Record<string, string> = {
        admitted:        'bg-blue-50 text-blue-700',
        discharged:      'bg-green-50 text-green-700',
        deceased:        'bg-gray-800 text-white',
        referred:        'bg-purple-50 text-purple-700',
        treated_on_site: 'bg-orange-50 text-orange-700',
    }
    return map[d ?? ''] ?? 'bg-gray-100 text-gray-500'
}

const dispositionLabel = (d: string | null) =>
    d ? d.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—'

const formatDate = (val: string) => val
    ? new Date(val).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })
    : ''

// ── Columns ────────────────────────────────────────────────────────────────

const columns = computed(() => [
    { accessorKey: 'chart_code',   header: 'Chart Code' },
    { accessorKey: 'patient_name', header: 'Patient' },
    {
        accessorKey: 'triage_category',
        header: 'Triage',
        cell: ({ row }: any) => {
            const cat = row.original.triage_category
            if (!cat) return h('span', { class: 'text-gray-300 text-sm' }, '—')
            return h('span', {
                class: `px-2 py-0.5 text-sm font-medium rounded ${triageBadge(cat)}`,
            }, triageLabel(cat))
        },
    },
    { accessorKey: 'age', header: 'Age', cell: ({ row }: any) => row.original.age ?? '—' },
    {
        accessorKey: 'sex',
        header: 'Sex',
        cell: ({ row }: any) => {
            const s = row.original.sex
            return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—'
        },
    },
    {
        accessorKey: 'chief_complaint',
        header: 'Chief Complaint',
        cell: ({ row }: any) => {
            const val = row.original.chief_complaint
            if (!val) return h('span', { class: 'text-gray-300 text-sm' }, '—')
            return h('span', { title: val }, val.length > 40 ? val.slice(0, 40) + '…' : val)
        },
    },
    {
        accessorKey: 'disposition',
        header: 'Disposition',
        cell: ({ row }: any) => {
            const d = row.original.disposition
            if (!d) return h('span', { class: 'text-gray-300 text-sm' }, '—')
            return h('span', {
                class: `inline-flex items-center px-2 py-0.5 text-sm font-medium rounded ${dispositionBadge(d)}`,
            }, dispositionLabel(d))
        },
    },
    {
        accessorKey: 'incident_report',
        header: 'Incident',
        cell: ({ row }: any) => {
            const ir = row.original.incident_report
            if (!ir) return h('span', { class: 'text-gray-300 text-sm' }, 'Standalone')
            return h('span', { class: 'text-sm text-orange-600 font-medium' }, ir.incident_code)
        },
    },
    {
        accessorKey: 'attending_responder',
        header: 'Attended By',
        cell: ({ row }: any) => row.original.attending_responder ?? '—',
    },
    {
        accessorKey: 'created_at',
        header: 'Created',
        cell: ({ row }: any) => formatDate(row.original.created_at),
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }: any) => h('div', { class: 'flex items-center gap-1.5' }, [
            h(Button, {
                variant: 'ghost', size: 'icon',
                onClick: () => openEditModal(row.original),
                class: 'h-7 w-7 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded',
            }, () => h(PhPencil, { size: 15 })),
            ...(props.hasFullAccess ? [
                h(Button, {
                    variant: 'ghost', size: 'icon',
                    onClick: () => openDeleteModal(row.original),
                    class: 'h-7 w-7 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded',
                }, () => h(PhTrash, { size: 15 })),
            ] : []),
        ]),
    },
])

const activeColor = computed(() => tabColors[activeTab.value].border)
const activeBg    = computed(() => tabColors[activeTab.value].bg)
</script>

<template>
    <Head title="Patient Record Charts" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Form modal -->
        <Modal :show="isFormVisible" @close="closeFormModal" class="fixed inset-0 z-50">
            <FormModal
                v-if="isFormVisible"
                :mode="formMode"
                :record="currentRecord"
                :incident-reports="incidentReports"
                :has-full-access="hasFullAccess"
                @close="closeFormModal"
                @success="closeFormModal"
            />
        </Modal>

        <div class="w-full flex justify-center py-8 px-4">
            <div class="w-full max-w-7xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-7">
                    <div>
                        <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">Patient Record Charts</h1>
                        <p class="text-sm text-gray-400 mt-0.5">Homepage / Patient Record Charts</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700
                               dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white
                               text-white text-base font-medium px-4 py-2 rounded-lg transition-colors"
                    >
                        <PhPlus :size="14" />
                        Create patient record
                    </button>
                </div>

                <!-- Triage summary badges -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <div v-for="cat in ['red','yellow','green','black']" :key="cat"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium border"
                        :class="triageBadge(cat)"
                    >
                        {{ triageLabel(cat) }}
                        <span class="font-bold">{{ triageCounts[cat] ?? 0 }}</span>
                    </div>
                </div>

                <!-- Triage tabs -->
                <div class="flex gap-0.5">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="switchTab(tab.key)"
                        :style="activeTab === tab.key
                            ? { borderColor: tabColors[tab.key].border, borderBottomColor: 'white' }
                            : {}"
                        :class="[
                            'flex items-center gap-1.5 px-3.5 py-1.5 text-base font-medium',
                            'rounded-t-md border-2 border-b-0 transition-all select-none',
                            activeTab === tab.key
                                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 z-10'
                                : 'bg-gray-50 dark:bg-gray-800 border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                        ]"
                    >
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                            :style="{ background: tabColors[tab.key].dot }" />
                        {{ tab.label }}
                        <span
                            v-if="tab.key !== 'all' && triageCounts[tab.key]"
                            class="text-[11px] font-medium bg-gray-100 dark:bg-gray-700
                                   text-gray-400 rounded-full px-1.5 py-px min-w-[18px] text-center leading-4"
                        >
                            {{ triageCounts[tab.key] }}
                        </span>
                    </button>
                </div>

                <!-- Table card -->
                <div
                    class="bg-white p-2 dark:bg-gray-900 rounded-b-lg rounded-tr-lg overflow-hidden transition-colors"
                    :style="{ border: `2px solid ${activeColor}`, backgroundColor: activeBg }"
                >
                    <DataTable
                        :columns="columns"
                        :data="records"
                        :show-per-page="true"
                        :filters="localFilters"
                        @update:filters="updateFilters"
                    />
                </div>

            </div>
        </div>

        <!-- Delete confirmation -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 max-w-sm">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Delete patient record
                </h2>
                <p class="text-base text-gray-400 dark:text-gray-500">
                    This action cannot be undone.
                </p>
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-base font-medium rounded-lg border border-gray-200
                               dark:border-gray-700 text-gray-600 dark:text-gray-300
                               hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteRecord"
                        class="px-4 py-2 text-base font-medium rounded-lg bg-red-600
                               hover:bg-red-700 text-white transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
