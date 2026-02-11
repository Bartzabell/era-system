<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { PhFireTruck, PhClockCountdown, PhCheckCircle } from '@phosphor-icons/vue'
import { Flame } from 'lucide-vue-next';


interface DashboardStats {
    activeIncidents: number;
    activeResponders: number;
    avgResponseTime: string;
    resolvedToday: number;
}

const props = defineProps<{
    stats: DashboardStats;
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

                <!-- <Map> Content Area -->
                <div class="relative bg-white aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">

                </div>
            </div>

            <!-- Right Sidebar - Spans full height -->
            <div class="col-span-1 lg:col-span-1 h-3/4">
                <div class="relative bg-white px-4 py-3 h-full overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <h1 class="text-2xl font-black my-3">Incident Feed</h1>
                    <div class="grid gap-2 grid-cols-1 h-3/4">
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
