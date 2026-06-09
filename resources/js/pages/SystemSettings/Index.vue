<script setup lang="ts">
import { ref, computed, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import { Button } from '@/components/ui/button'
import { PhPencil, PhTrash, PhPlus, PhPhone, PhWarning, PhFirstAid, PhMapPin } from '@phosphor-icons/vue'  // Add PhMapPin

// ── Types ──────────────────────────────────────────────────────────────────
interface Hotline {
    id: number
    hotline_name: string
    hotline_no: string
    description: string | null
}

interface Emergency {
    id: number
    emergency_name: string
    definition: string | null
    severity_level: string | null
}

interface Incident {
    id: number
    incident_name: string
    definition: string | null
    base_severity: number | null
    base_time: number | null
    base_resources: number | null
    base_secondary: number | null
    emergency_id: number
    emergency: { emergency_name: string } | null
}

// Add Barangay interface
interface Barangay {
    id: number
    barangay_name: string
    landmark: string | null
}

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps<{
    hotlines: Hotline[]
    emergencies: Emergency[]
    incidents: Incident[]
    barangays: Barangay[]  // Add this
}>()

// DataTable expects a Laravel paginator shape: { data, links, from, to, total, per_page, current_page }
// Since settings data is not paginated, we wrap the plain arrays into that shape.
const wrapArray = (arr: any[]) => {
    const items = arr ?? []
    return {
        data:         items,
        links:        [],
        from:         items.length ? 1 : 0,
        to:           items.length,
        total:        items.length,
        per_page:     items.length || 10,
        current_page: 1,
    }
}

const hotlines    = computed(() => wrapArray(props.hotlines))
const emergencies = computed(() => wrapArray(props.emergencies))
const incidents   = computed(() => wrapArray(props.incidents))
const barangays   = computed(() => wrapArray(props.barangays))  // Add this

// ── Breadcrumbs ────────────────────────────────────────────────────────────
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'System Settings', href: '/system-settings' },
]

// ── Tabs ───────────────────────────────────────────────────────────────────
type TabKey = 'hotline' | 'emergency' | 'incident' | 'barangay'  // Add 'barangay'
const activeTab = ref<TabKey>('hotline')

const tabs: { key: TabKey; label: string; icon: any }[] = [
    { key: 'hotline',   label: 'Hotline',   icon: PhPhone    },
    { key: 'emergency', label: 'Emergency', icon: PhWarning  },
    { key: 'incident',  label: 'Incident',  icon: PhFirstAid },
    { key: 'barangay',  label: 'Barangay',  icon: PhMapPin   },  // Add this
]

const tabColors: Record<TabKey, { dot: string; border: string; bg: string }> = {
    hotline:   { dot: '#3b82f6', border: '#3b82f6', bg: '#dbeafe' },
    emergency: { dot: '#f97316', border: '#f97316', bg: '#ffedd5' },
    incident:  { dot: '#10b981', border: '#10b981', bg: '#d1fae5' },
    barangay:  { dot: '#8b5cf6', border: '#8b5cf6', bg: '#ede9fe' },  // Add this (purple)
}

const activeColor = computed(() => tabColors[activeTab.value].border)
const activeBg    = computed(() => tabColors[activeTab.value].bg)

// ── Modal state ────────────────────────────────────────────────────────────
type FormMode = 'create' | 'edit'

const isFormVisible   = ref(false)
const showDeleteModal = ref(false)
const formMode        = ref<FormMode>('create')
const currentRecord   = ref<Hotline | Emergency | Incident | Barangay | null>(null)  // Add Barangay type

const closeFormModal  = () => { isFormVisible.value = false; currentRecord.value = null }
const openDeleteModal = (r: any) => { currentRecord.value = r; showDeleteModal.value = true }

const deleteRecord = () => {
    const id  = currentRecord.value!.id
    const url = activeTab.value === 'hotline'
        ? `/system-settings/hotline/${id}`
        : activeTab.value === 'emergency'
            ? `/system-settings/emergency/${id}`
            : activeTab.value === 'incident'
                ? `/system-settings/incident/${id}`
                : `/system-settings/barangay/${id}`  // Add this

    router.delete(url, { onSuccess: () => { showDeleteModal.value = false } })
}

// ── Form fields ────────────────────────────────────────────────────────────
const form = ref<Record<string, any>>({})

const initForm = (record: any = null) => {
    if (activeTab.value === 'hotline') {
        form.value = record
            ? { hotline_name: record.hotline_name, hotline_no: record.hotline_no, description: record.description ?? '' }
            : { hotline_name: '', hotline_no: '', description: '' }
    } else if (activeTab.value === 'emergency') {
        form.value = record
            ? { emergency_name: record.emergency_name, definition: record.definition ?? '', severity_level: record.severity_level ?? '' }
            : { emergency_name: '', definition: '', severity_level: '' }
    } else if (activeTab.value === 'incident') {
        form.value = record
            ? { incident_name: record.incident_name, definition: record.definition ?? '', base_severity: record.base_severity ?? '', base_time: record.base_time ?? '', base_resources: record.base_resources ?? '', base_secondary: record.base_secondary ?? '', emergency_id: record.emergency_id }
            : { incident_name: '', definition: '', base_severity: '', base_time: '', base_resources: '', base_secondary: '', emergency_id: '' }
    } else {  // Add barangay form
        form.value = record
            ? { barangay_name: record.barangay_name, landmark: record.landmark ?? '' }
            : { barangay_name: '', landmark: '' }
    }
}

const handleOpenCreate = () => {
    formMode.value = 'create'
    currentRecord.value = null
    initForm(null)
    isFormVisible.value = true
}

const handleOpenEdit = (r: any) => {
    formMode.value = 'edit'
    currentRecord.value = r
    initForm(r)
    isFormVisible.value = true
}

const submitForm = () => {
    if (formMode.value === 'create') {
        const url = activeTab.value === 'hotline'   ? '/system-settings/hotline'
                  : activeTab.value === 'emergency' ? '/system-settings/emergency'
                  : activeTab.value === 'incident'  ? '/system-settings/incident'
                  :                                   '/system-settings/barangay'  // Add this
        router.post(url, form.value, { onSuccess: closeFormModal })
    } else {
        const id  = currentRecord.value!.id
        const url = activeTab.value === 'hotline'   ? `/system-settings/hotline/${id}`
                  : activeTab.value === 'emergency' ? `/system-settings/emergency/${id}`
                  : activeTab.value === 'incident'  ? `/system-settings/incident/${id}`
                  :                                   `/system-settings/barangay/${id}`  // Add this
        router.put(url, form.value, { onSuccess: closeFormModal })
    }
}

// ── Columns ────────────────────────────────────────────────────────────────
const makeActionCol = (row: any) =>
    h('div', { class: 'flex items-center gap-1.5' }, [
        h(Button, {
            variant: 'ghost', size: 'icon',
            onClick: () => handleOpenEdit(row.original),
            class: 'h-7 w-7 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded'
        }, () => h(PhPencil, { size: 15 })),
        h(Button, {
            variant: 'ghost', size: 'icon',
            onClick: () => openDeleteModal(row.original),
            class: 'h-7 w-7 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded'
        }, () => h(PhTrash, { size: 15 })),
    ])

const hotlineColumns = [
    { accessorKey: 'hotline_name', header: 'Name' },
    { accessorKey: 'hotline_no',   header: 'Number' },
    { accessorKey: 'description',  header: 'Description', cell: ({ row }: any) => row.original.description ?? '—' },
    { id: 'actions', header: '', cell: ({ row }: any) => makeActionCol(row) },
]

const emergencyColumns = [
    { accessorKey: 'emergency_name', header: 'Name' },
    { accessorKey: 'severity_level', header: 'Severity',   cell: ({ row }: any) => row.original.severity_level ?? '—' },
    { accessorKey: 'definition',     header: 'Definition', cell: ({ row }: any) => row.original.definition ?? '—' },
    { id: 'actions', header: '', cell: ({ row }: any) => makeActionCol(row) },
]

const incidentColumns = [
    { accessorKey: 'incident_name',            header: 'Name' },
    { accessorKey: 'emergency.emergency_name', header: 'Emergency', cell: ({ row }: any) => row.original.emergency?.emergency_name ?? '—' },
    { accessorKey: 'base_severity',            header: 'Severity',  cell: ({ row }: any) => row.original.base_severity ?? '—' },
    { accessorKey: 'base_time',                header: 'Base Time', cell: ({ row }: any) => row.original.base_time ?? '—' },
    { accessorKey: 'base_resources',           header: 'Resources', cell: ({ row }: any) => row.original.base_resources ?? '—' },
    { accessorKey: 'definition',               header: 'Definition', cell: ({ row }: any) => row.original.definition ?? '—' },
    { id: 'actions', header: '', cell: ({ row }: any) => makeActionCol(row) },
]

// Add barangay columns
const barangayColumns = [
    { accessorKey: 'barangay_name', header: 'Barangay Name' },
    { accessorKey: 'landmark',      header: 'Landmark', cell: ({ row }: any) => row.original.landmark ?? '—' },
    { id: 'actions', header: '', cell: ({ row }: any) => makeActionCol(row) },
]

const activeColumns = computed(() =>
    activeTab.value === 'hotline'   ? hotlineColumns
  : activeTab.value === 'emergency' ? emergencyColumns
  : activeTab.value === 'incident'  ? incidentColumns
  : barangayColumns  // Add this
)

const activeData = computed<any[]>(() =>
    activeTab.value === 'hotline'   ? hotlines.value
  : activeTab.value === 'emergency' ? emergencies.value
  : activeTab.value === 'incident'  ? incidents.value
  : barangays.value  // Add this
)

const modalTitle = computed(() => {
    const action = formMode.value === 'create' ? 'Create' : 'Edit'
    const label  = activeTab.value.charAt(0).toUpperCase() + activeTab.value.slice(1)
    return `${action} ${label}`
})
</script>

<template>
    <Head title="System Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Form Modal -->
        <Modal :show="isFormVisible" @close="closeFormModal" class="fixed inset-0 z-50">
            <div v-if="isFormVisible" class="p-6 w-full max-w-lg">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ modalTitle }}</h2>

                <!-- Hotline Form -->
                <template v-if="activeTab === 'hotline'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Hotline Name <span class="text-red-500">*</span></label>
                            <input v-model="form.hotline_name" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Hotline Number <span class="text-red-500">*</span></label>
                            <input v-model="form.hotline_no" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                </template>

                <!-- Emergency Form -->
                <template v-else-if="activeTab === 'emergency'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Emergency Name <span class="text-red-500">*</span></label>
                            <input v-model="form.emergency_name" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Severity Level</label>
                            <input v-model="form.severity_level" type="text" placeholder="e.g. high, medium, low" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Definition</label>
                            <textarea v-model="form.definition" rows="3" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500" />
                        </div>
                    </div>
                </template>

                <!-- Incident Form -->
                <template v-else-if="activeTab === 'incident'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Incident Name <span class="text-red-500">*</span></label>
                            <input v-model="form.incident_name" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Emergency <span class="text-red-500">*</span></label>
                            <select v-model="form.emergency_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select emergency</option>
                                <option v-for="e in emergencies" :key="e.id" :value="e.id">{{ e.emergency_name }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Base Severity</label>
                                <input v-model="form.base_severity" type="number" min="0" max="10" step="0.1" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Base Time</label>
                                <input v-model="form.base_time" type="number" min="0" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Base Resources</label>
                                <input v-model="form.base_resources" type="number" min="0" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Base Secondary</label>
                                <input v-model="form.base_secondary" type="number" min="0" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Definition</label>
                            <textarea v-model="form.definition" rows="3" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                    </div>
                </template>

                <!-- Barangay Form -->  <!-- Add this -->
                <template v-else>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Barangay Name <span class="text-red-500">*</span></label>
                            <input v-model="form.barangay_name" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Landmark</label>
                            <input v-model="form.landmark" type="text" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-base bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="closeFormModal"
                        class="px-4 py-2 text-base font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >Cancel</button>
                    <button
                        @click="submitForm"
                        class="px-4 py-2 text-base font-medium rounded-lg bg-gray-900 hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white text-white transition-colors"
                    >{{ formMode === 'create' ? 'Create' : 'Save changes' }}</button>
                </div>
            </div>
        </Modal>

        <div class="w-full flex justify-center py-8 px-4">
            <div class="w-full max-w-7xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-7">
                    <div>
                        <h1 class="text-xl font-medium text-gray-900 dark:text-gray-100">System Settings</h1>
                        <p class="text-sm text-gray-400 mt-0.5">Homepage / System Settings</p>
                    </div>
                    <button
                        @click="handleOpenCreate"
                        class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700
                               dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white
                               text-white text-base font-medium px-4 py-2 rounded-lg transition-colors"
                    >
                        <PhPlus :size="14" />
                        Add {{ activeTab }}
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
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :style="{ background: tabColors[tab.key].dot }" />
                        <component :is="tab.icon" :size="14" />
                        {{ tab.label }}
                        <span class="text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-full px-1.5 py-px min-w-[18px] text-center leading-4">
                            {{ tab.key === 'hotline' ? hotlines.total : tab.key === 'emergency' ? emergencies.total : tab.key === 'incident' ? incidents.total : barangays.total }}
                        </span>
                    </button>
                </div>

                <!-- Table card -->
                <div
                    class="bg-white p-2 dark:bg-gray-900 rounded-b-lg rounded-tr-lg overflow-hidden transition-colors"
                    :style="{ border: `2px solid ${activeColor}`, backgroundColor: activeBg }"
                >
                    <DataTable
                        :columns="activeColumns"
                        :data="activeData"
                        :show-per-page="true"
                    />
                </div>

            </div>
        </div>

        <!-- Delete confirmation -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 max-w-sm">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Delete {{ activeTab }}
                </h2>
                <p class="text-base text-gray-400 dark:text-gray-500">This action cannot be undone.</p>
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-base font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >Cancel</button>
                    <button
                        @click="deleteRecord"
                        class="px-4 py-2 text-base font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors"
                    >Delete</button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
