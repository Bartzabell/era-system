<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import CreateIncidentReportForm from './Partials/Create.vue';
import EditIncidentReportForm from './Partials/Edit.vue';
import { PhDotsThreeOutline, PhPlus } from '@phosphor-icons/vue';
import { ref, computed } from 'vue';

interface IncidentReport {
    id: number;
    user_name: string;
    barangay: string;
    incident: string;
    emergency: string;
    severity_level: string;
    casualty_count: number;
    responder_name: string;
    responder_contact_no: string;
    plate_no: string;
    status: string;
    estimated_arrival: string | null;
    datetime_arrived: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    incidentReports: IncidentReport[];
    barangays: Array<{ id: number; barangay_name: string }>;
    incidents: Array<{ id: number; incident_name: string; severity_level: string }>;
    emergencies: Array<{ id: number; emergency_name: string; severity_level: string }>;
    users: Array<{ id: number; full_name: string }>;
    hasFullAccess: boolean;
}

const props = defineProps<Props>();

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingReportId = ref<number | null>(null);

const columns = computed(() => {
    const baseColumns = [
        {
            key: 'id',
            label: 'ID',
            sortable: true
        },
        {
            key: 'user_name',
            label: 'Reporter',
            sortable: true
        },
        {
            key: 'barangay',
            label: 'Barangay',
            sortable: true
        },
        {
            key: 'incident',
            label: 'Incident Type',
            sortable: true
        },
        {
            key: 'emergency',
            label: 'Emergency Type',
            sortable: true
        },
        {
            key: 'severity_level',
            label: 'Severity',
            sortable: true
        },
        {
            key: 'status',
            label: 'Status',
            sortable: true
        },
        {
            key: 'responder_name',
            label: 'Responder',
            sortable: true
        },
        {
            key: 'created_at',
            label: 'Reported At',
            sortable: true
        },
        {
            key: 'actions',
            label: 'Actions',
            sortable: false
        }
    ];

    return baseColumns;
});

const handleRowClick = (row: IncidentReport) => {
    console.log('Row clicked:', row);
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const openEditModal = (reportId: number) => {
    editingReportId.value = reportId;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingReportId.value = null;
};

const getSeverityColor = (severity: string) => {
    const colors: Record<string, string> = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'critical': 'bg-red-100 text-red-800',
    };
    return colors[severity?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'assigned': 'bg-orange-100 text-orange-800',
        'arrival': 'bg-purple-100 text-purple-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800',
    };
    return colors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status: string) => {
    return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'N/A';
};
</script>

<template>
    <Head title="Incident Reports" />

    <AppLayout>
        <div class="py-6">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Incident Reports</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ hasFullAccess ? 'View and manage all incident reports' : 'View your incident reports' }}
                        </p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                        <PhPlus :size="20" weight="bold" />
                        Create Report
                    </button>
                </div>

                <!-- DataTable -->
                <div class="bg-white rounded-lg shadow ">
                    <DataTable :columns="columns" :data="incidentReports" :per-page="10" @row-click="handleRowClick">
                        <!-- Custom severity cell -->
                        <template #cell-severity_level="{ value }">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full w-full"
                                :class="getSeverityColor(value)"
                            >
                                {{ value ? value.toUpperCase() : 'N/A' }}
                            </span>
                        </template>

                        <!-- Custom status cell -->
                        <template #cell-status="{ value }">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getStatusColor(value)"
                            >
                                {{ formatStatus(value) }}
                            </span>
                        </template>

                        <!-- Actions column -->
                        <template #cell-actions="{ row }">
                            <div class="flex items-center gap-2">
                                <button
                                    @click.stop="console.log('View', row.id)"
                                    class="text-orange-600 hover:text-orange-800 font-medium text-sm"
                                >
                                    View
                                </button>
                                <template v-if="hasFullAccess">
                                    <button
                                        @click.stop="openEditModal(row.id)"
                                        class="text-gray-600 hover:text-gray-800 font-medium text-sm"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click.stop="console.log('More actions', row.id)"
                                        class="text-gray-400 hover:text-gray-600"
                                    >
                                        <PhDotsThreeOutline :size="20" />
                                    </button>
                                </template>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <CreateIncidentReportForm
            :show="showCreateModal"
            :barangays="barangays"
            :incidents="incidents"
            :emergencies="emergencies"
            @close="closeCreateModal"
        />

        <!-- Edit Modal (only shown if user has full access) -->
        <EditIncidentReportForm
            v-if="hasFullAccess"
            :show="showEditModal"
            :report-id="editingReportId"
            :barangays="barangays"
            :incidents="incidents"
            :emergencies="emergencies"
            :users="users"
            @close="closeEditModal"
        />
    </AppLayout>
</template>
