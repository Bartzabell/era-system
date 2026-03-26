<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { PhFirstAid } from '@phosphor-icons/vue'

interface ReportRow {
    no: number
    barangay_name: string
    landmark: string
    minor: number
    serious: number
    dead: number
    total_incidents: number
    top_incidents: string
}

const props = defineProps<{
    reportData: ReportRow[]
    filters: { date_from?: string; date_to?: string }
}>()

const breadcrumbs = [
    { title: 'Homepage', href: '/landing' },
    { title: 'Monthly Report', href: '/monthly-report' },
]

const dateFrom = ref(props.filters.date_from ?? '')
const dateTo   = ref(props.filters.date_to   ?? '')
const searchQuery = ref('')

const filteredData = computed(() => {
    const q = searchQuery.value.trim().toLowerCase()
    if (!q) return props.reportData
    return props.reportData.filter(row =>
        row.barangay_name.toLowerCase().includes(q) ||
        row.landmark.toLowerCase().includes(q) ||
        row.top_incidents.toLowerCase().includes(q)
    )
})

const applyFilter = () => {
    router.get(('/monthly-report'), {
        date_from: dateFrom.value,
        date_to:   dateTo.value,
    }, { preserveState: true, replace: true })
}

const exportCsv = () => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.set('date_from', dateFrom.value)
    if (dateTo.value)   params.set('date_to',   dateTo.value)
    window.location.href = '/monthly-report/export-csv' + '?' + params.toString()
}

const printPdf = () => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.set('date_from', dateFrom.value)
    if (dateTo.value)   params.set('date_to',   dateTo.value)
    window.open(('/monthly-report/export-pdf') + '?' + params.toString(), '_blank')
}
</script>

<template>
    <Head title="Monthly Report" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="w-full flex justify-center mt-10">
            <div class="w-[100%]">

                <!-- Header -->
                <div class="flex flex-col p-4 sm:flex-row sm:items-center sm:justify-between gap-4 bg-sky-900">
                    <h1 class="flex justify-center items-center font-black text-xl text-white"><span><PhFirstAid :size="25" weight="fill" class="text-gray-500 bg-white rounded-full m-3"/></span>GEARS</h1>
                    <h1 class="text-2xl font-bold text-white">MONTHLY REPORT</h1>

                    <div></div>
                </div>

                <!-- Date Range Filter -->
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6 p-4 rounded-lg">
                    <div class="flex flex-wrap items-end gap-4 ">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-black">Date From:</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="border bg-white border-gray-900 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600">Date To</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="border bg-white border-gray-900 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>
                        <button
                            @click="applyFilter"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition border border-black"
                        >
                            Apply Filter
                        </button>
                    </div>
                    <div class="flex flex-wrap items-end gap-4 ">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Barangay, landmark, incident..."
                                class="border bg-white border-gray-900 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-orange-400"
                            />
                        </div>
                        <button
                            @click="exportCsv"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition"
                        >
                             Export CSV
                        </button>
                        <button
                            @click="printPdf"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition"
                        >
                             Print PDF
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto shadow-sm px-4">
                    <table class="min-w-full text-sm text-gray-700 border-2 border-black">
                        <thead>
                            <!-- Row 1: main headers -->
                            <tr class="bg-gray-300 text-black text-center font-semibold uppercase tracking-wide">
                                <th rowspan="2" class="px-3 py-3 border border-black">No.</th>
                                <th rowspan="2" class="px-3 py-3 items-center border border-black text-center">Barangay</th>
                                <th rowspan="2" class="px-3 py-3 border border-black">Landmark</th>
                                <th colspan="3" class="px-3 py-3 border border-black">
                                    Medical Condition
                                </th>
                                <th rowspan="2" class="px-3 py-3 border border-black">Total No. of<br>Incidents</th>
                                <th rowspan="2" class="px-3 py-3 border border-black ">Incidents</th>
                            </tr>
                            <!-- Row 2: sub-headers for medical condition -->
                            <tr class="bg-gray-400 text-black text-center text-xs font-semibold uppercase">
                                <th class="px-3 py-2 border border-black">Minor</th>
                                <th class="px-3 py-2 border border-black">Serious</th>
                                <th class="px-3 py-2 border border-black">DECEASED</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="reportData.length > 0" class="border border-black">
                                <tr
                                    v-for="row in filteredData"
                                    :key="row.no"
                                    class="border border-black even:bg-gray-50 hover:bg-orange-50 transition text-black"
                                >
                                    <td class="px-3 py-2 text-center ">{{ row.no }}</td>
                                    <td class="px-3 py-2 border-r border-black font-medium">{{ row.barangay_name }}</td>
                                    <td class="px-3 py-2 border-r border-black ">{{ row.landmark }}</td>
                                    <td class="px-3 py-2 text-center border-r border-black">
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ row.minor }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border-r border-black">
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ row.serious }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border-r border-black">
                                        <span class="px-2 py-0.5 bg-gray-100  rounded-full text-xs font-semibold">{{ row.dead }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border-r border-black font-bold ">{{ row.total_incidents }}</td>
                                    <td class="px-3 py-2">{{ row.top_incidents }}</td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                    No records found for the selected date range.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    * Minor, Serious, and Deceased counts are summed from
                    <code>minor_casualty_count</code>, <code>serious_casualty_count</code>,
                    and <code>deceased_casualty_count</code> per barangay.
                </p>

            </div>
        </div>

    </AppLayout>
</template>
