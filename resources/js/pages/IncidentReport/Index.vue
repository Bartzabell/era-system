<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import CustomInput from '@/components/CustomInput.vue'
import FormModal from './Partials/FormModal.vue'
import { h } from 'vue'
import { Button } from '@/components/ui/button'
import { PhPencil, PhTrash, PhEye, PhPlus } from '@phosphor-icons/vue'

interface IncidentReport {
    id: number
    user_name: string
    barangay: string
    incident: string
    emergency: string
    severity_level: string
    casualty_count: number
    responder_name: string
    responder_contact_no: string
    plate_no: string
    status: string
    created_at: string
    updated_at: string
}

const props = defineProps<{
    incidentReports: object
    barangays: Array<{ id: number; barangay_name: string }>
    siteLocations: Array<{ id: number; site_name: string }>
    incidents: Array<{ id: number; incident_name: string; severity_level: string }>
    emergencies: Array<{ id: number; emergency_name: string; severity_level: string }>
    users: Array<{ id: number; full_name: string }>
    hasFullAccess: boolean
    filters: object
    currentUserId: number
}>()

const breadcrumbs = [
    { title: 'Homepage', href: '/landing' },
    { title: 'Incident Reports', href: '/incident-report' },
]

const localFilters = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get('/incident-report', newFilters, { preserveState: true, replace: true })
}

// ── Tab state ──────────────────────────────────────────────────────────────
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

// Tab accent colors
const tabMeta: Record<TabKey, { active: string; badge: string }> = {
    priority:  { active: 'border-orange-500 text-orange-600 dark:text-orange-400',  badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300' },
    waiting:   { active: 'border-yellow-500 text-yellow-600 dark:text-yellow-400',  badge: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' },
    assigned:  { active: 'border-blue-500 text-blue-600 dark:text-blue-400',        badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' },
    arriving:  { active: 'border-purple-500 text-purple-600 dark:text-purple-400',  badge: 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' },
    resolved:  { active: 'border-green-500 text-green-600 dark:text-green-400',     badge: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' },
    cancelled: { active: 'border-gray-500 text-gray-600 dark:text-gray-400',        badge: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' },
}

// Derive all rows from the paginated object
const allRows = computed<IncidentReport[]>(() => {
    const data = props.incidentReports as any
    return data?.data ?? []
})

// Count per status for badges
const statusCounts = computed(() => {
    const counts: Record<string, number> = {}
    allRows.value.forEach(r => {
        counts[r.status] = (counts[r.status] ?? 0) + 1
    })
    return counts
})

// Filtered rows based on active tab
const filteredData = computed(() => {
    const raw = props.incidentReports as any
    if (activeTab.value === 'priority') return props.incidentReports

    // Filter in-memory on the current page rows — preserving pagination wrapper shape
    const filtered = (raw?.data ?? []).filter((r: IncidentReport) => r.status === activeTab.value)
    return { ...raw, data: filtered, total: filtered.length }
})

// Modal state — same pattern as RequestForm
const isFormVisible   = ref(false)
const showDeleteModal = ref(false)
const formMode        = ref<'create' | 'edit'>('create')
const currentRecord   = ref<IncidentReport | null>(null)

const openCreateModal = () => {
    formMode.value      = 'create'
    currentRecord.value = null
    isFormVisible.value = true
}

const openEditModal = (record: IncidentReport) => {
    formMode.value      = 'edit'
    currentRecord.value = record
    isFormVisible.value = true
}

const closeFormModal = () => {
    isFormVisible.value = false
    currentRecord.value = null
}

const openDeleteModal = (record: IncidentReport) => {
    currentRecord.value   = record
    showDeleteModal.value = true
}

const deleteRecord = () => {
    router.delete(`/incident-report/${currentRecord.value!.id}`, {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

// Badge helpers - following leveling colors: green (lightest) → yellow → orange → red
const severityClass = (severity: string) => {
    const map: Record<string, string> = {
        low:    'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high:   'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full ${map[severity] ?? 'bg-gray-100 text-gray-700'}`
}

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        waiting:   'bg-yellow-100 text-yellow-800',
        assigned:  'bg-blue-100 text-blue-800',
        arriving:  'bg-purple-100 text-purple-800',
        resolved:  'bg-green-100 text-green-800',
        cancelled: 'bg-gray-100 text-gray-800',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full ${map[status] ?? 'bg-gray-100 text-gray-700'}`
}

const formatResponseTime = (reportedAt: string | null, datetimeArrived: string | null): string => {
    if (!reportedAt || !datetimeArrived) return ''
    const diff = new Date(datetimeArrived).getTime() - new Date(reportedAt).getTime()
    if (diff < 0) return ''
    const totalMinutes = Math.floor(diff / 60000)
    const hours = Math.floor(totalMinutes / 60)
    const minutes = totalMinutes % 60
    if (hours > 0) return `${hours}h ${minutes}m`
    return `${minutes}m`
}

// Priority-tab extra column: shows priority badge
const priorityColumn = {
    accessorKey: 'priority_level',
    header: 'Priority',
    cell: ({ row }: any) => {
        const level = row.original.priority_level
        const label = row.original.priority_label
        if (!level) return h('span', { class: 'text-gray-400 text-xs' }, '—')
        const colorMap: Record<string, string> = {
            P1: 'bg-red-100 text-red-700 border border-red-300',
            P2: 'bg-orange-100 text-orange-700 border border-orange-300',
            P3: 'bg-yellow-100 text-yellow-700 border border-yellow-300',
            P4: 'bg-blue-100 text-blue-700 border border-blue-300',
            P5: 'bg-gray-100 text-gray-600 border border-gray-300',
        }
        return h('span', {
            class: `px-2 py-0.5 text-xs font-semibold rounded-full ${colorMap[level] ?? 'bg-gray-100 text-gray-600'}`,
        }, `${level} · ${label}`)
    },
}

const statusColumn = {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }: any) => h('span', {
        class: statusClass(row.original.status),
    }, row.original.status),
}

const baseColumns = [
    { accessorKey: 'incident_code', header: 'Code' },
    {
        accessorKey: 'emergency.emergency_name',
        header: 'Type',
        cell: ({ row }: any) => row.original.emergency?.emergency_name ?? '',
    },
    {
        accessorKey: 'barangay.barangay_name',
        header: 'Barangay',
        cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '',
    },
    {
        accessorKey: 'reported_at',
        header: 'Date Reported',
        cell: ({ row }: any) => {
            const val = row.original.reported_at
            if (!val) return ''
            return new Date(val).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true,
            })
        },
    },
    {
        accessorKey: 'datetime_arrived',
        header: 'Response Time',
        cell: ({ row }: any) => formatResponseTime(row.original.reported_at, row.original.datetime_arrived),
    },
    {
        accessorKey: 'responder_count',
        header: 'Responders',
        cell: ({ row }: any) => row.original.responder_count ?? '',
    },
]

const columns = computed(() => {
    const actionCol = {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            h(Button, {
                variant: 'outline', size: 'icon',
                onClick: () => window.open(`/incident-report/${row.original.id}/print`, '_blank'),
                class: 'text-white bg-gray-600 rounded-full hover:bg-gray-700',
            }, () => h(PhEye, { size: 18 })),
            ...(props.hasFullAccess ? [
                h(Button, {
                    variant: 'outline', size: 'icon',
                    onClick: () => openEditModal(row.original),
                    class: 'text-white bg-orange-500 rounded-full hover:bg-orange-600',
                }, () => h(PhPencil, { size: 18 })),
                h(Button, {
                    variant: 'outline', size: 'icon',
                    onClick: () => openDeleteModal(row.original),
                    class: 'text-white bg-red-600 rounded-full hover:bg-red-700',
                }, () => h(PhTrash, { size: 16 })),
            ] : []),
        ]),
    }

    if (activeTab.value === 'priority') {
        return [...baseColumns, priorityColumn, statusColumn, actionCol]
    }
    return [...baseColumns, actionCol]
})
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
                :siteLocations="siteLocations"
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

                <!-- Create button -->
                <ButtonCode
                    @click="openCreateModal"
                    text="Create Incident Report"
                    :icon="PhPlus"
                    color="bg-orange-500 hover:bg-orange-600 text-white"
                />

                <!-- ── Tabs ─────────────────────────────────────────────── -->
                <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'relative flex items-center gap-2 whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 focus:outline-none',
                                activeTab === tab.key
                                    ? tabMeta[tab.key].active + ' border-current'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300'
                            ]"
                        >
                            {{ tab.label }}

                            <!-- Badge showing count (skip for 'priority' tab) -->
                            <template v-if="tab.key !== 'priority'">
                                <span
                                    v-if="statusCounts[tab.key]"
                                    :class="[
                                        'inline-flex items-center justify-center min-w-[1.25rem] h-5 rounded-full px-1.5 text-xs font-semibold',
                                        activeTab === tab.key
                                            ? tabMeta[tab.key].badge
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                                    ]"
                                >
                                    {{ statusCounts[tab.key] }}
                                </span>
                            </template>
                        </button>
                    </nav>
                </div>

                <!-- ── Table ───────────────────────────────────────────── -->
                <div class="mt-4">
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

        <!-- Delete confirm -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">Delete Incident Report</h2>
                <p class="text-sm text-gray-600">
                    Are you sure you want to delete this report? This action cannot be undone.
                </p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false" class="bg-gray-500 hover:bg-gray-600 text-white">Cancel</Button>
                    <Button @click="deleteRecord" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
