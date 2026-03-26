<script setup lang="ts">
import { ref } from 'vue'
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
    estimated_arrival: string | null
    datetime_arrived: string | null
    created_at: string
    updated_at: string
}

const props = defineProps<{
    incidentReports: object
    barangays: Array<{ id: number; barangay_name: string }>
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

// Badge helpers
const severityClass = (severity: string) => {
    const map: Record<string, string> = {
        low:    'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high:   'bg-orange-100 text-orange-800',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full ${map[severity] ?? 'bg-gray-100 text-gray-700'}`
}

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        pending:   'bg-yellow-100 text-yellow-800',
        assigned:  'bg-orange-100 text-orange-800',
        arrival:   'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-gray-100 text-gray-800',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full ${map[status] ?? 'bg-gray-100 text-gray-700'}`
}

const columns = [
   { accessorKey: 'id', header: 'ID' },
    {
        accessorKey: 'user.full_name',
        header: 'Reporter',
        cell: ({ row }: any) => row.original.user?.full_name ?? '—',
    },
    {
        accessorKey: 'barangay.barangay_name',
        header: 'Barangay',
        cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '—',
    },
    {
        accessorKey: 'emergency.emergency_name',
        header: 'Type',
        cell: ({ row }: any) => row.original.emergency?.emergency_name ?? '—',
    },
    {
        accessorKey: 'severity_level',
        header: 'Severity',
        cell: ({ row }: any) => h('span', { class: severityClass(row.original.severity_level) },
            row.original.severity_level?.toUpperCase() ?? 'N/A'
        ),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }: any) => h('span', { class: statusClass(row.original.status) },
            row.original.status?.charAt(0).toUpperCase() + row.original.status?.slice(1)
        ),
    },
    { accessorKey: 'responder_name', header: 'Responder' },
    { accessorKey: 'created_at',     header: 'Reported At' },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            h(Button, {
                variant: 'outline', size: 'icon',
                onClick: () => router.get(`/incident-report/${row.original.id}`),
                class: 'text-black bg-slate-500 rounded-full hover:bg-slate-400',
            }, () => h(PhEye, { size: 18 })),
            ...(props.hasFullAccess ? [
                h(Button, {
                    variant: 'outline', size: 'icon',
                    onClick: () => openEditModal(row.original),
                    class: 'text-black bg-sky-500 rounded-full hover:bg-sky-400',
                }, () => h(PhPencil, { size: 18 })),
                h(Button, {
                    variant: 'outline', size: 'icon',
                    onClick: () => openDeleteModal(row.original),
                    class: 'text-black bg-red-500 rounded-full hover:bg-red-400',
                }, () => h(PhTrash, { size: 16 })),
            ] : []),
        ]),
    },
]
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
                    color="bg-orange-500 hover:bg-orange-600"
                />
                <DataTable
                    :columns="columns"
                    :data="incidentReports"
                    :show-per-page="true"
                    :filters="localFilters"
                    @update:filters="updateFilters"
                />
            </div>
        </div>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">Delete Incident Report</h2>
                <p class="text-sm text-gray-600">
                    Are you sure you want to delete this report? This action cannot be undone.
                </p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false">Cancel</Button>
                    <Button @click="deleteRecord" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
