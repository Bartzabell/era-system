<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import IncidentMap from '@/components/IncidentMap.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { PhFireTruck, PhClockCountdown, PhCheckCircle, PhDotsThreeOutline, PhFunnel } from '@phosphor-icons/vue'
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

const props = defineProps<{
    stats: DashboardStats;
    incidentMarkers: IncidentMarker[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4 bg-gray-300">
            <!-- Left Side: Stats + Main Content -->
            <div class="col-span-1 lg:col-span-2 flex flex-col gap-4">
                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Stats cards remain the same -->
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
                                <div class="p-1 bg-blue-200 rounded-md flex items-center justify-center">
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

            <!-- Right Sidebar - Spans full height -->
            <div class="col-span-1 lg:col-span-1 h-3/4">
                <div class="relative bg-white px-4 py-3 h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-black my-3">Incident Feed</h1>
                        <div class="flex">
                            <PhFunnel :size="24" color="#969696" weight="fill"/>
                            <PhDotsThreeOutline :size="24" color="#969696" weight="fill"/>
                        </div>
                    </div>
                    <div class="grid gap-2 grid-cols-1 h-[calc(100%-5rem)]">
                        <div class="border border-orange-700 bg-orange-700 rounded-2xl">
                            <div class="rounded-2xl bg-white h-full ml-5">

                            </div>
                        </div>
                        <div class="border border-orange-500 bg-orange-500 rounded-2xl">
                            <div class="rounded-2xl bg-white h-full ml-5">

                            </div>
                        </div>
                        <div class="border border-yellow-500 bg-yellow-500 rounded-2xl">
                            <div class="rounded-2xl bg-white h-full ml-5">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
