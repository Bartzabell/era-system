<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface ReportRow {
    no: number
    barangay_name: string
    landmark: string
    medical_condition_raw: string
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

const applyFilter = () => {
    router.get(route('monthly-report.index'), {
        date_from: dateFrom.value,
        date_to:   dateTo.value,
    }, { preserveState: true, replace: true })
}

const exportCsv = () => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.set('date_from', dateFrom.value)
    if (dateTo.value)   params.set('date_to',   dateTo.value)
    window.location.href = route('monthly-report.export-csv') + '?' + params.toString()
}

const printPdf = () => {
    const params = new URLSearchParams()
    if (dateFrom.value) params.set('date_from', dateFrom.value)
    if (dateTo.value)   params.set('date_to',   dateTo.value)
    window.open(route('monthly-report.export-pdf') + '?' + params.toString(), '_blank')
}
</script>

<template>
    <Head title="Monthly Report" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="w-full flex justify-center mt-10">
            <div class="w-[95%]">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <h1 class="text-2xl font-bold text-gray-800">Monthly Incident Report</h1>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button
                            @click="printPdf"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700 transition"
                        >
                            🖨 Print PDF
                        </button>
                        <button
                            @click="exportCsv"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700 transition"
                        >
                            📥 Export CSV
                        </button>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="flex flex-wrap items-end gap-4 mb-6 p-4 bg-gray-50 border rounded-lg">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600">Date From</label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600">Date To</label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        />
                    </div>
                    <button
                        @click="applyFilter"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded hover:bg-orange-600 transition"
                    >
                        Apply Filter
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead>
                            <!-- Row 1: main headers -->
                            <tr class="bg-red-700 text-white text-center text-xs font-semibold uppercase tracking-wide">
                                <th rowspan="2" class="px-3 py-3 border border-red-600">No.</th>
                                <th rowspan="2" class="px-3 py-3 border border-red-600 text-left">Barangay</th>
                                <th rowspan="2" class="px-3 py-3 border border-red-600 text-left">Landmark</th>
                                <th colspan="3" class="px-3 py-3 border border-red-600">
                                    Medical Condition <span class="text-red-200">(??)* </span>
                                </th>
                                <th rowspan="2" class="px-3 py-3 border border-red-600">Total No. of<br>Incidents</th>
                                <th rowspan="2" class="px-3 py-3 border border-red-600 text-left">Incidents<br><span class="text-red-200 text-xs font-normal">(Top 3)</span></th>
                            </tr>
                            <!-- Row 2: sub-headers for medical condition -->
                            <tr class="bg-red-800 text-white text-center text-xs font-semibold uppercase">
                                <th class="px-3 py-2 border border-red-600">Minor</th>
                                <th class="px-3 py-2 border border-red-600">Serious</th>
                                <th class="px-3 py-2 border border-red-600">Dead</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="reportData.length > 0">
                                <tr
                                    v-for="row in reportData"
                                    :key="row.no"
                                    class="border-b border-gray-100 even:bg-red-50 hover:bg-orange-50 transition"
                                >
                                    <td class="px-3 py-2 text-center border border-gray-200">{{ row.no }}</td>
                                    <td class="px-3 py-2 border border-gray-200 font-medium">{{ row.barangay_name }}</td>
                                    <td class="px-3 py-2 border border-gray-200 text-gray-500">{{ row.landmark }}</td>
                                    <td class="px-3 py-2 text-center border border-gray-200">
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ row.minor }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border border-gray-200">
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ row.serious }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border border-gray-200">
                                        <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{{ row.dead }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center border border-gray-200 font-bold text-gray-800">{{ row.total_incidents }}</td>
                                    <td class="px-3 py-2 border border-gray-200 text-gray-600">{{ row.top_incidents }}</td>
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
                    * Medical Condition column has no direct database field. Counts are derived from <code>severity_level</code>: <em>low = Minor, medium = Serious, high = Dead/Critical</em>.
                </p>

            </div>
        </div>

    </AppLayout>
</template>
