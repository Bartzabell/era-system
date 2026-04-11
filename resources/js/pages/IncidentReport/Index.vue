<script setup lang="ts">
import { ref, computed, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import FormModal from './Partials/FormModal.vue'
import { Button } from '@/components/ui/button'
import { PhPencil, PhTrash, PhEye, PhPlus } from '@phosphor-icons/vue'

interface IncidentReport {
    id: number
    incident_code: string
    barangay: { barangay_name: string }
    emergency: { emergency_name: string }
    severity_level: string
    responder_count: number
    priority_level: string
    priority_label: string
    status: string
    reported_at: string
    datetime_arrived: string
}

const props = defineProps<{
    incidentReports: object
    barangays: Array<{ id: number; barangay_name: string }>
    siteLocations: Array<{ id: number; site_name: string }>
    incidents: Array<{ id: number; incident_name: string }>
    emergencies: Array<{ id: number; emergency_name: string }>
    users: Array<{ id: number; full_name: string }>
    hasFullAccess: boolean
    filters: object
    currentUserId: number
}>()

const breadcrumbs = [
    { title: 'Homepage', href: '/landing' },
    { title: 'Incident Reports', href: '/incident-report' },
]

// ── Filters ────────────────────────────────────────────────────────────────
const localFilters = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get('/incident-report', newFilters, { preserveState: true, replace: true })
}

// ── Tabs ───────────────────────────────────────────────────────────────────
type TabKey = 'priority' | 'waiting' | 'assigned' | 'arriving' | 'resolved' | 'cancelled'
const activeTab = ref<TabKey>('priority')

const tabs: { key: TabKey; label: string }[] = [
    { key: 'priority',  label: 'Priority'  },
    { key: 'waiting',   label: 'Waiting'   },
    { key: 'assigned',  label: 'Assigned'  },
    { key: 'arriving',  label: 'Arriving'  },
    { key: 'resolved',  label: 'Resolved'  },
    { key: 'cancelled', label: 'Cancelled' },
]

// Colors grade from orange → amber → gray
const tabColors: Record<TabKey, { bg: string; text: string; }> = {
    priority:  { bg: '#ff8c1a', text: '#fff7ed'},
    waiting:   { bg: '#ffa64d', text: '#fffbeb'},
    assigned:  { bg: '#ff5c33', text: '#fef3c7'},
    arriving:  { bg: '#ff704d', text: '#fafaf9'},
    resolved:  { bg: '#80aaff', text: '#f9fafb'},
    cancelled: { bg: '#9494b8', text: '#f3f4f6'},
}

// ── Data ───────────────────────────────────────────────────────────────────
const allRows = computed<IncidentReport[]>(() => (props.incidentReports as any)?.data ?? [])

const statusCounts = computed(() => {
    const counts: Record<string, number> = {}
    allRows.value.forEach(r => { counts[r.status] = (counts[r.status] ?? 0) + 1 })
    return counts
})

const filteredData = computed(() => {
    const raw = props.incidentReports as any
    if (activeTab.value === 'priority') return props.incidentReports
    const filtered = (raw?.data ?? []).filter((r: IncidentReport) => r.status === activeTab.value)
    return { ...raw, data: filtered, total: filtered.length }
})

// ── Modal state ────────────────────────────────────────────────────────────
const isFormVisible   = ref(false)
const showDeleteModal = ref(false)
const formMode        = ref<'create' | 'edit'>('create')
const currentRecord   = ref<IncidentReport | null>(null)

const openCreateModal = () => { formMode.value = 'create'; currentRecord.value = null; isFormVisible.value = true }
const openEditModal   = (r: IncidentReport) => { formMode.value = 'edit'; currentRecord.value = r; isFormVisible.value = true }
const closeFormModal  = () => { isFormVisible.value = false; currentRecord.value = null }
const openDeleteModal = (r: IncidentReport) => { currentRecord.value = r; showDeleteModal.value = true }
const deleteRecord    = () => router.delete(`/incident-report/${currentRecord.value!.id}`, { onSuccess: () => { showDeleteModal.value = false } })

// ── Helpers ────────────────────────────────────────────────────────────────
const statusClass = (s: string) => ({
    waiting:   'bg-yellow-100 text-yellow-800',
    assigned:  'bg-blue-100 text-blue-800',
    arriving:  'bg-purple-100 text-purple-800',
    resolved:  'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-800',
}[s] ?? 'bg-gray-100 text-gray-700')

const formatDate = (val: string) => val
    ? new Date(val).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })
    : ''

const formatResponseTime = (a: string, b: string) => {
    if (!a || !b) return ''
    const mins = Math.floor((new Date(b).getTime() - new Date(a).getTime()) / 60000)
    if (mins < 0) return ''
    const hh = Math.floor(mins / 60), mm = mins % 60
    return hh > 0 ? `${hh}h ${mm}m` : `${mm}m`
}

// ── Columns ────────────────────────────────────────────────────────────────
const priorityColors: Record<string, string> = {
    P1: 'bg-red-100 text-red-700 border border-red-300',
    P2: 'bg-orange-100 text-orange-700 border border-orange-300',
    P3: 'bg-yellow-100 text-yellow-700 border border-yellow-300',
    P4: 'bg-blue-100 text-blue-700 border border-blue-300',
    P5: 'bg-gray-100 text-gray-600 border border-gray-300',
}

const baseColumns = [
    { accessorKey: 'incident_code',            header: 'Code' },
    { accessorKey: 'emergency.emergency_name', header: 'Emergency', cell: ({ row }: any) => row.original.emergency?.emergency_name ?? '' },
    { accessorKey: 'incident.incident_name',   header: 'Incident',  cell: ({ row }: any) => row.original.incident?.incident_name ?? '' },
    { accessorKey: 'barangay.barangay_name',   header: 'Location',  cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '' },
    { accessorKey: 'reported_at',              header: 'Reported',  cell: ({ row }: any) => formatDate(row.original.reported_at) },
    { accessorKey: 'datetime_arrived',         header: 'Response',  cell: ({ row }: any) => formatResponseTime(row.original.reported_at, row.original.datetime_arrived) },
    { accessorKey: 'user.full_name',           header: 'Reporter',  cell: ({ row }: any) => row.original.user.full_name ?? '' },
    {
        accessorKey: 'ir_responders_count',
        header: 'Responders',
        cell: ({ row }: any) => row.original.ir_responders_count ?? 0
    },
]

const priorityCol = {
    accessorKey: 'priority_level',
    header: 'Severity',
    cell: ({ row }: any) => {
        const { priority_level: lvl, priority_label: lbl } = row.original
        if (!lvl) return h('span', { class: 'text-gray-400 text-xs' }, '—')
        return h('span', { class: `px-2 py-0.5 text-xs font-semibold rounded-full ${priorityColors[lvl] ?? 'bg-gray-100 text-gray-600'}` }, `${lvl} · ${lbl}`)
    },
}

const statusCol = {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }: any) => h('span', { class: `px-2 py-1 text-xs font-semibold rounded-full ${statusClass(row.original.status)}` }, row.original.status),
}

const columns = computed(() => {
    const actionCol = {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            h(Button, { variant: 'outline', size: 'icon', onClick: () => window.open(`/incident-report/${row.original.id}/print`, '_blank'), class: 'text-white bg-gray-600 rounded-full hover:bg-gray-700' }, () => h(PhEye, { size: 18 })),
            ...(props.hasFullAccess ? [
                h(Button, { variant: 'outline', size: 'icon', onClick: () => openEditModal(row.original), class: 'text-white bg-orange-500 rounded-full hover:bg-orange-600' }, () => h(PhPencil, { size: 18 })),
                h(Button, { variant: 'outline', size: 'icon', onClick: () => openDeleteModal(row.original), class: 'text-white bg-red-600 rounded-full hover:bg-red-700' }, () => h(PhTrash, { size: 16 })),
            ] : []),
        ]),
    }
    return activeTab.value === 'priority'
        ? [baseColumns[0], priorityCol, ...baseColumns.slice(1), statusCol, actionCol]
        : [...baseColumns, actionCol]
})

const activeColor = computed(() => tabColors[activeTab.value].bg)
</script>

<template>
    <Head title="Incident Reports" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Form Modal -->
        <Modal :show="isFormVisible" @close="closeFormModal" class="fixed inset-0 z-50">
            <FormModal
                v-if="isFormVisible"
                :mode="formMode"
                :record="currentRecord"
                :barangays="barangays"
                :incidents="incidents"
                :site-locations="siteLocations"
                :emergencies="emergencies"
                :users="users"
                :has-full-access="hasFullAccess"
                :current-user-id="currentUserId"
                @close="closeFormModal"
                @success="closeFormModal"
            />
        </Modal>

        <div class="w-full flex justify-center mt-10">
            <div class="w-[92%]">

                <ButtonCode
                    @click="openCreateModal"
                    text="Create Incident Report"
                    :icon="PhPlus"
                    color="bg-orange-500 hover:bg-orange-600 text-white"
                />

                <div class="relative mt-6 flex items-end gap-0" style="height: 42px;">
                    <button
                        v-for="(tab, index) in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :style="{
                            height: activeTab === tab.key ? '42px' : '34px',
                            backgroundColor: tabColors[tab.key].bg,
                            color: tabColors[tab.key].text,
                            zIndex: activeTab === tab.key ? 20 : tabs.length - index,
                            boxShadow: activeTab === tab.key
                                ? `2px -3px 8px rgba(0,0,0,0.2)`
                                : 'none',
                            marginRight: '-1px',
                        }"
                        class="flex items-center gap-1.5 px-4 text-sm font-medium
                            rounded-t-xl border-t-2 border-l-2 border-r-2
                            transition-all duration-150 focus:outline-none
                            whitespace-nowrap hover:brightness-110 self-end"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.key !== 'priority' && statusCounts[tab.key]"
                            class="inline-flex items-center justify-center min-w-[18px] h-[18px]
                                rounded-full px-1 text-[11px] font-semibold bg-white/25"
                        >
                            {{ statusCounts[tab.key] }}
                        </span>
                    </button>
                </div>

                <!-- Table — top border color tracks the active tab -->
                <div
                    class="rounded-b-lg rounded-tr-lg overflow-hidden
                           border border-gray-200 dark:border-gray-700 p-2"
                    :style="{ borderTop: `3px solid ${activeColor}`, backgroundColor: `${activeColor}` }"
                >
                    <DataTable
                        :columns="columns"
                        :data="filteredData"
                        :show-per-page="true"
                        :filters="localFilters"
                        @update:filters="updateFilters"
                    />
                </div>

            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-2 text-lg font-medium text-gray-900">Delete Incident Report</h2>
                <p class="text-sm text-gray-600">Are you sure? This action cannot be undone.</p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false" class="bg-gray-500 hover:bg-gray-600 text-white">Cancel</Button>
                    <Button @click="deleteRecord" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
