<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import IncidentMap from '@/components/IncidentMap.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { PhFireTruck, PhClockCountdown, PhCheckCircle, PhDotsThreeOutline, PhFunnel, PhMapPin } from '@phosphor-icons/vue'
import { Flame } from 'lucide-vue-next';

interface DashboardStats {
    activeIncidents: number;
    activeResponders: number;
    avgResponseTime: string;
    resolvedToday: number;
}

interface IncidentMarker {
    id: number;
    lat: number;
    lng: number;
    severity: string;
    status: string;
    incident_type: string;
    emergency_type: string;
    barangay: string;
    casualty_count: number;
    created_at: string;
}

interface IncidentFeedItem {
    id: number;
    incident_name: string;
    landmark: string;
    coordinates: {
        lat: number;
        lng: number;
    };
    time_ago: string;
    priority_score: number;
    severity_level: string;
    status: string;
    created_at: string;
}

const props = defineProps<{
    stats: DashboardStats;
    incidentMarkers: IncidentMarker[];
    incidentFeed: IncidentFeedItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const getSeverityColor = (severity: string) => {
    switch (severity?.toLowerCase()) {
        case 'critical':
            return 'border-red-700 bg-red-700';
        case 'high':
            return 'border-orange-700 bg-orange-700';
        case 'medium':
            return 'border-orange-500 bg-orange-500';
        case 'low':
            return 'border-yellow-500 bg-yellow-500';
        default:
            return 'border-gray-500 bg-gray-500';
    }
};

const getPriorityColor = (score: number) => {
    if (score >= 75) return 'text-red-700';
    if (score >= 50) return 'text-orange-700';
    if (score >= 25) return 'text-yellow-600';
    return 'text-gray-600';
};

const exportMonthlyReport = () => {
    const now = new Date();
    const month = now.getMonth() + 1;
    const year = now.getFullYear();

    window.location.href = `/dashboard/export/monthly-report?month=${month}&year=${year}`;
};

const exportCitizenReport = () => {
    const now = new Date();
    const startDate = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    const endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];

    window.location.href = `/dashboard/export/citizen-report?start_date=${startDate}&end_date=${endDate}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4 bg-gray-300">
            <div class="col-span-1 lg:col-span-2 flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-muted-foreground">Active Incidents</p>
                                <div class="p-1 bg-red-200 rounded-md flex items-center justify-center">
                                    <Flame :size="40" color="#e60000" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold">{{ stats.activeIncidents }}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-muted-foreground">Active Responders</p>
                                <div class="p-1 bg-orange-200 rounded-md flex items-center justify-center">
                                    <PhFireTruck :size="40" color="#0e6acd" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold">{{ stats.activeResponders }}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-muted-foreground">Avg response Time</p>
                                <div class="p-1 bg-green-200 rounded-md flex items-center justify-center">
                                    <PhClockCountdown :size="40" color="#00a814" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold">{{ stats.avgResponseTime }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-muted-foreground">Resolved Today</p>
                                <div class="p-1 bg-slate-200 rounded-md flex items-center justify-center">
                                    <PhCheckCircle :size="40" color="#404040" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold">{{ stats.resolvedToday }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Content Area -->
                <div class="relative bg-white overflow-hidden rounded-xl dark:border-sidebar-border">
                    <div class="flex justify-between items-center p-4">
                        <h1 class="text-2xl font-black">Live Incident Map</h1>
                        <div class="flex gap-2 text-sm">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-red-600"></span>
                                Critical
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-orange-600"></span>
                                High
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                Medium
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                Low
                            </span>
                        </div>
                    </div>
                    <div class="h-[500px] p-4 pt-0">
                        <IncidentMap :incidents="incidentMarkers" />
                    </div>
                </div>
            </div>

            <div class="col-span-1 lg:col-span-1 h-3/4 flex flex-col">
                <div class="relative h-[calc(75%-5rem)] bg-white px-4 py-3 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex flex-col">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-black my-3">Incident Feed</h1>
                        <div class="flex">
                            <PhFunnel :size="24" color="#969696" weight="fill"/>
                            <PhDotsThreeOutline :size="24" color="#969696" weight="fill"/>
                        </div>
                    </div>

                    <!-- Incident Feed List -->
                    <!-- <div class="flex-1 grid gap-2 grid-cols-1 rounded-2xl overflow-y-auto pr-2 bg-gray-200 p-2 mb-3"> -->
                    <div class="grid gap-2 grid-cols-1 h-[calc(75%-5rem)] overflow-y-auto pr-2 bg-gray-200 p-2">
                        <div
                            v-for="incident in incidentFeed"
                            :key="incident.id"
                            :class="['border rounded-2xl', getSeverityColor(incident.severity_level)]"
                        >
                            <div class="rounded-2xl bg-white h-full ml-5 p-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex justify-between items-start">
                                        <h3 class="font-bold text-sm line-clamp-1">{{ incident.incident_name }}</h3>
                                        <span :class="['text-xs font-semibold', getPriorityColor(incident.priority_score)]">
                                            {{ incident.priority_score }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-gray-600">
                                        <PhMapPin :size="15" color="#B0b0b0" weight="fill" />
                                        <span class="line-clamp-1">{{ incident.landmark }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">{{ incident.time_ago }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 capitalize">
                                            {{ incident.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="incidentFeed.length === 0" class="flex items-center justify-center h-full text-gray-400">
                            <p>No active incidents</p>
                        </div>
                    </div>

                    <!-- Report Buttons -->
                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <button
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm"
                            @click="exportMonthlyReport"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">Monthly Report</span>
                        </button>
                        <button
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm"
                            @click="exportCitizenReport"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="text-sm">Citizen Report</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
