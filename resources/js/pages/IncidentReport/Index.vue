<script setup lang="ts">
import { ref, computed, h } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import FormModal from './Partials/FormModal.vue'
import { Button } from '@/components/ui/button'
import { PhPencil, PhTrash, PhEye, PhPlus } from '@phosphor-icons/vue'

interface IncidentReport {
    id: number
    incident_code: string
    barangay: { barangay_name: string }
    emergency: { emergency_name: string }
    incident: { incident_name: string }
    user: { full_name: string }
    severity_level: string
    responder_count: number
    priority_level: string
    priority_label: string
    status: string
    reported_at: string
    datetime_arrived: string
    ir_responders_count: number
}

const page = usePage();
const userRole = computed(() => (page.props.auth as any)?.user?.role ?? '');

const props = defineProps<{
    incidentReports: object
    barangays: Array<{ id: number; barangay_name: string }>
    siteLocations: Array<{ id: number; site_name: string }>
    incidents: Array<{ id: number; incident_name: string }>
    emergencies: Array<{ id: number; emergency_name: string }>
    users: Array<{ id: number; full_name: string }>
    filters: object
    hasFullAccess: boolean
    currentUserId: number
}>()

const isAdmin = computed(() => userRole.value === 'administrator');
const isAssistantAdmin = computed(() => userRole.value === 'assistant admin');
const isResponder = computed(() => userRole.value === 'responder');

const canEdit = computed(() => isAdmin.value || isAssistantAdmin.value);
const canDelete = computed(() => isAdmin.value);
const canView = computed(() => isAdmin.value || isAssistantAdmin.value || isResponder.value);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
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

const tabColors: Record<TabKey, { dot: string; border: string; bg: string }> = {
    priority:  { dot: '#f97316', border: '#f97316', bg: '#ffedd5' }, // soft orange
    waiting:   { dot: '#f59e0b', border: '#f59e0b', bg: '#fef3c7' }, // soft amber
    assigned:  { dot: '#3b82f6', border: '#3b82f6', bg: '#dbeafe' }, // soft blue
    arriving:  { dot: '#8b5cf6', border: '#8b5cf6', bg: '#ede9fe' }, // soft violet
    resolved:  { dot: '#10b981', border: '#10b981', bg: '#d1fae5' }, // soft green
    cancelled: { dot: '#9ca3af', border: '#9ca3af', bg: '#f3f4f6' }, // soft gray
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
    waiting:   'bg-yellow-50 text-yellow-700',
    assigned:  'bg-blue-50 text-blue-700',
    arriving:  'bg-purple-50 text-purple-700',
    resolved:  'bg-green-50 text-green-700',
    cancelled: 'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-500')

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
    P1: 'bg-red-50 text-red-700 border border-red-200',
    P2: 'bg-orange-50 text-orange-700 border border-orange-200',
    P3: 'bg-yellow-50 text-yellow-700 border border-yellow-200',
    P4: 'bg-blue-50 text-blue-700 border border-blue-200',
    P5: 'bg-gray-100 text-gray-500 border border-gray-200',
}

const baseColumns = [
    { accessorKey: 'incident_code',            header: 'Code' },
    { accessorKey: 'emergency.emergency_name', header: 'Emergency', cell: ({ row }: any) => row.original.emergency?.emergency_name ?? '' },
    { accessorKey: 'incident.incident_name',   header: 'Incident',  cell: ({ row }: any) => row.original.incident?.incident_name ?? '' },
    { accessorKey: 'barangay.barangay_name',   header: 'Location',  cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '' },
    { accessorKey: 'reported_at',              header: 'Reported',  cell: ({ row }: any) => formatDate(row.original.reported_at) },
    { accessorKey: 'datetime_arrived',         header: 'Response',  cell: ({ row }: any) => formatResponseTime(row.original.reported_at, row.original.datetime_arrived) },
    { accessorKey: 'user.full_name',           header: 'Reporter',  cell: ({ row }: any) => row.original.user?.full_name ?? '' },
    { accessorKey: 'ir_responders_count',      header: 'Responders', cell: ({ row }: any) => row.original.ir_responders_count ?? 0 },
]

const priorityCol = {
    accessorKey: 'priority_level',
    header: 'Severity',
    cell: ({ row }: any) => {
        const { priority_level: lvl, priority_label: lbl } = row.original
        if (!lvl) return h('span', { class: 'text-gray-300 text-sm' }, '—')
        return h('span', { class: `px-2 py-0.5 text-sm font-medium rounded ${priorityColors[lvl] ?? 'bg-gray-100 text-gray-500'}` }, `${lvl} · ${lbl}`)
    },
}

const statusCol = {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }: any) => {
        const s = row.original.status
        return h('span', { class: `inline-flex items-center gap-1.5 text-sm font-medium ${statusClass(s)}` }, [
            h('span', { class: 'w-1.5 h-1.5 rounded-full', style: `background: currentColor; opacity: 0.7` }),
            s,
        ])
    },
}

const columns = computed(() => {
    const actionCol = {
        id: 'actions',
        header: '',
        cell: ({ row }: any) => h('div', { class: 'flex items-center gap-1.5' }, [
            h(Button, {
                variant: 'ghost', size: 'icon',
                onClick: () => window.open(`/incident-report/${row.original.id}/print`, '_blank'),
                class: 'h-7 w-7 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded'
            }, () => h(PhEye, { size: 15 })), //admin and assistant_admin, responder

                h(Button, {
                    variant: 'ghost', size: 'icon',
                    onClick: () => openEditModal(row.original),
                    class: 'h-7 w-7 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded'
                }, () => h(PhPencil, { size: 15 })), //admin and assistant_admin
            ...(props.hasFullAccess ? [
                h(Button, {
                    variant: 'ghost', size: 'icon',
                    onClick: () => openDeleteModal(row.original),
                    class: 'h-7 w-7 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded'
                }, () => h(PhTrash, { size: 15 })), //admin
            ] : []),
        ]),
    }
    return activeTab.value === 'priority'
        ? [baseColumns[0], priorityCol, ...baseColumns.slice(1), statusCol, actionCol]
        : [...baseColumns, actionCol]
})

const activeColor = computed(() => tabColors[activeTab.value].border)
const activeBg = computed(() => tabColors[activeTab.value].bg)
</script>

<template>
    <Head title="Incident Reports" />
    <AppLayout :breadcrumbs="breadcrumbs">

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

        <div class="w-full flex justify-center py-8 px-4">
            <div class="w-full max-w-7xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-7">
                    <div>
                        <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">Incident Reports</h1>
                        <p class="text-sm text-gray-400 mt-0.5">Homepage / Incident Reports</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700
                               dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white
                               text-white text-base font-medium px-4 py-2 rounded-lg transition-colors"
                    >
                        <PhPlus :size="14" />
                        Create incident report
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex gap-0.5">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
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
                        <span
                            class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                            :style="{ background: tabColors[tab.key].dot }"
                        />
                        {{ tab.label }}
                        <span
                            v-if="tab.key !== 'priority' && statusCounts[tab.key]"
                            class="text-[11px] font-medium bg-gray-100 dark:bg-gray-700
                                   text-gray-400 rounded-full px-1.5 py-px min-w-[18px] text-center leading-4"
                        >
                            {{ statusCounts[tab.key] }}
                        </span>
                    </button>
                </div>

                <!-- Table card — border color tracks active tab -->
                <div
                    class="bg-white p-2 dark:bg-gray-900 rounded-b-lg rounded-tr-lg overflow-hidden transition-colors"
                    :style="{ border: `2px solid ${activeColor}`, backgroundColor: activeBg}"
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

        <!-- Delete confirmation -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 max-w-sm">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Delete incident report
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
