<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import IncidentMap from '@/components/IncidentMap.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { PhFireTruck, PhClockCountdown, PhCheckCircle, PhDotsThreeOutline, PhFunnel, PhMapPin } from '@phosphor-icons/vue'
import { Flame } from 'lucide-vue-next';

// Dashboard modals
import AIModal  from '@/components/Dashboard/AIModal.vue'
import ARModal  from '@/components/Dashboard/ARModal.vue'
import ARTModal from '@/components/Dashboard/ARTModal.vue'
import RModal   from '@/components/Dashboard/RModal.vue'

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
    priority_level: string;
    priority_label: string;
    priority_score: number;
    status: string;
    incident_type: string;
    emergency_type: string;
    barangay: string;
    casualty_count: number;
    reported_at: string;
}

interface IncidentFeedItem {
    id: number;
    incident_name: string;
    landmark: string;
    coordinates: { lat: number; lng: number };
    time_ago: string;
    priority_score: number;
    priority_level: string;
    priority_label: string;
    status: string;
    reported_at: string;
}

const props = defineProps<{
    stats: DashboardStats;
    incidentMarkers: IncidentMarker[];
    incidentFeed: IncidentFeedItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

// Modal visibility state
const showAIModal  = ref(false)
const showARModal  = ref(false)
const showARTModal = ref(false)
const showRModal   = ref(false)

// Color by priority_level (P1–P5) - using theme colors
const getPriorityLevelColor = (level: string) => {
    const map: Record<string, string> = {
        P1: 'border-red-700 bg-red-700',      // highest - red
        P2: 'border-orange-600 bg-orange-600', // high - orange
        P3: 'border-yellow-500 bg-yellow-500', // medium - yellow
        P4: 'border-green-400 bg-green-400',   // low - light green
        P5: 'border-gray-400 bg-gray-400',     // lowest - gray
    }
    return map[level] ?? 'border-gray-400 bg-gray-400'
}

const getPriorityScoreColor = (score: number | null) => {
    if (!score) return 'text-gray-500'
    if (score >= 8.5) return 'text-red-700'      // highest - red
    if (score >= 6.5) return 'text-orange-600'    // high - orange
    if (score >= 4.5) return 'text-yellow-600'    // medium - yellow
    if (score >= 2.5) return 'text-green-600'     // low - green
    return 'text-gray-500'                        // lowest - gray
}

const exportMonthlyReport = () => {
    const now   = new Date();
    const month = now.getMonth() + 1;
    const year  = now.getFullYear();
    window.location.href = `/dashboard/export/monthly-report?month=${month}&year=${year}`;
};

const exportCitizenReport = () => {
    const now       = new Date();
    const startDate = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    const endDate   = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
    window.location.href = `/dashboard/export/citizen-report?start_date=${startDate}&end_date=${endDate}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4 bg-gray-100">
            <div class="col-span-1 lg:col-span-2 flex flex-col gap-4">
                <!-- Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Active Incidents -->
                    <button
                        type="button"
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left transition hover:shadow-md hover:scale-[1.02] active:scale-100 cursor-pointer"
                        @click="showAIModal = true"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-gray-700">Active Incidents</p>
                                <div class="p-1 bg-red-100 rounded-md flex items-center justify-center">
                                    <Flame :size="40" color="#dc2626" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-800">{{ stats.activeIncidents }}</p>
                            </div>
                        </div>
                    </button>

                    <!-- Active Responders -->
                    <button
                        type="button"
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left transition hover:shadow-md hover:scale-[1.02] active:scale-100 cursor-pointer"
                        @click="showARModal = true"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-gray-700">Active Responders</p>
                                <div class="p-1 bg-orange-100 rounded-md flex items-center justify-center">
                                    <PhFireTruck :size="40" color="#ea580c" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-800">{{ stats.activeResponders }}</p>
                            </div>
                        </div>
                    </button>

                    <!-- Avg Response Time -->
                    <button
                        type="button"
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left transition hover:shadow-md hover:scale-[1.02] active:scale-100 cursor-pointer"
                        @click="showARTModal = true"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-gray-700">Avg Response Time</p>
                                <div class="p-1 bg-green-100 rounded-md flex items-center justify-center">
                                    <PhClockCountdown :size="40" color="#16a34a" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-800">{{ stats.avgResponseTime }}</p>
                            </div>
                        </div>
                    </button>

                    <!-- Resolved Today -->
                    <button
                        type="button"
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 text-left transition hover:shadow-md hover:scale-[1.02] active:scale-100 cursor-pointer"
                        @click="showRModal = true"
                    >
                        <div class="flex flex-col">
                            <div class="grid grid-cols-3">
                                <p class="text-lg font-black col-span-2 leading-5 text-gray-700">Resolved Today</p>
                                <div class="p-1 bg-gray-100 rounded-md flex items-center justify-center">
                                    <PhCheckCircle :size="40" color="#6b7280" weight="fill" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-800">{{ stats.resolvedToday }}</p>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Map -->
                <div class="relative bg-white overflow-hidden rounded-xl border border-gray-200 isolate z-0">
                    <div class="flex justify-between items-center p-4">
                        <h1 class="text-2xl font-black text-gray-800">Live Incident Map</h1>
                        <div class="flex gap-2 text-sm">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-red-600"></span> P1 Critical
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-orange-600"></span> P2 High
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span> P3 Moderate
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span> P4 Low
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-gray-400"></span> P5
                            </span>
                        </div>
                    </div>
                    <div class="h-[500px] p-4 pt-0">
                        <IncidentMap :incidents="incidentMarkers" />
                    </div>
                </div>
            </div>

            <!-- Incident Feed -->
            <div class="col-span-1 lg:col-span-1 h-full flex flex-col">
                <div class="relative h-[calc(75%-5rem)] bg-white px-4 py-3 overflow-hidden rounded-xl border border-gray-200 flex flex-col">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-black my-3 text-gray-800">Incident Feed</h1>
                        <div class="flex gap-1">
                            <PhFunnel :size="24" color="#6b7280" weight="fill" />
                            <PhDotsThreeOutline :size="24" color="#6b7280" weight="fill" />
                        </div>
                    </div>

                    <div class="grid gap-2 grid-cols-1 h-[calc(75%-5rem)] overflow-y-auto pr-2 bg-gray-50 p-2 rounded-lg">
                        <div
                            v-for="incident in incidentFeed"
                            :key="incident.id"
                            :class="['border rounded-2xl', getPriorityLevelColor(incident.priority_level)]"
                        >
                            <div class="rounded-2xl bg-white h-full ml-5 p-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex justify-between items-start gap-2">
                                        <h3 class="font-bold text-sm line-clamp-1 text-gray-800">{{ incident.incident_name }}</h3>
                                        <span
                                            v-if="incident.priority_level"
                                            class="shrink-0 text-xs font-bold px-1.5 py-0.5 rounded"
                                            :class="{
                                                'bg-red-100 text-red-700':       incident.priority_level === 'P1',
                                                'bg-orange-100 text-orange-700': incident.priority_level === 'P2',
                                                'bg-yellow-100 text-yellow-700': incident.priority_level === 'P3',
                                                'bg-green-100 text-green-700':   incident.priority_level === 'P4',
                                                'bg-gray-100 text-gray-600':     incident.priority_level === 'P5',
                                            }"
                                        >
                                            {{ incident.priority_level }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs font-medium" :class="getPriorityScoreColor(incident.priority_score)">
                                            {{ incident.priority_label ?? '' }}
                                        </span>
                                        <span v-if="incident.priority_score" class="text-xs text-gray-400">
                                            ({{ incident.priority_score }}/10)
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-gray-500">
                                        <PhMapPin :size="15" color="#9ca3af" weight="fill" />
                                        <span class="line-clamp-1">{{ incident.landmark }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-400">{{ incident.time_ago }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 capitalize">
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
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm"
                            @click="exportMonthlyReport"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">Monthly Report</span>
                        </button>
                        <button
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm"
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

    <!-- Dashboard Modals -->
    <AIModal
        :show="showAIModal"
        :active-incidents="stats.activeIncidents"
        :incident-markers="incidentMarkers"
        @close="showAIModal = false"
    />

    <ARModal
        :show="showARModal"
        :active-responders="stats.activeResponders"
        @close="showARModal = false"
    />

    <ARTModal
        :show="showARTModal"
        :avg-response-time="stats.avgResponseTime"
        :incident-feed="incidentFeed"
        @close="showARTModal = false"
    />

    <RModal
        :show="showRModal"
        :resolved-today="stats.resolvedToday"
        @close="showRModal = false"
    />
</template>
