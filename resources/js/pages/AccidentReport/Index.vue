<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { PhDotsThreeOutline } from '@phosphor-icons/vue';
import { ref } from 'vue';

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

const props = defineProps<{
    incidentReports: IncidentReport[];
}>();

const columns = [
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

const handleRowClick = (row: IncidentReport) => {
    console.log('Row clicked:', row);
    // You can navigate to detail page or open modal here
    // router.visit(route('incident-reports.show', row.id));
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
        'in_progress': 'bg-blue-100 text-blue-800',
        'responded': 'bg-purple-100 text-purple-800',
        'resolved': 'bg-green-100 text-green-800',
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Incident Reports</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        View and manage all incident reports
                    </p>
                </div>

                <!-- DataTable -->
                <div class="bg-white rounded-lg shadow">
                    <DataTable
                        :columns="columns"
                        :data="incidentReports"
                        :per-page="10"
                        @row-click="handleRowClick"
                    >
                        <!-- Custom severity cell -->
                        <template #cell-severity_level="{ value }">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
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
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                >
                                    View
                                </button>
                                <button
                                    @click.stop="console.log('Edit', row.id)"
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
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
