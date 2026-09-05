<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import { Button } from '@/components/ui/button'
import { PhCaretLeft, PhCaretRight, PhUsers } from '@phosphor-icons/vue'

interface DutyEntry {
    name: string
    time: string
}

const props = defineProps<{
    dutyLogs: Record<string, DutyEntry[]>
    offDutyLogs: Record<string, { name: string }[]>
    month: number
    year: number
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'On-Duty Logs', href: '/duty-logs' },
]

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
]

const currentMonth = ref(props.month)
const currentYear = ref(props.year)

const yearOptions = computed(() => {
    const thisYear = new Date().getFullYear()
    const years = []
    for (let y = thisYear - 5; y <= thisYear + 1; y++) years.push(y)
    return years
})

const goToMonth = (month: number, year: number) => {
    router.get('/duty-logs', { month, year }, { preserveState: true, replace: true })
}

const prevMonth = () => {
    let m = currentMonth.value - 1
    let y = currentYear.value
    if (m < 1) { m = 12; y-- }
    currentMonth.value = m
    currentYear.value = y
    goToMonth(m, y)
}

const nextMonth = () => {
    let m = currentMonth.value + 1
    let y = currentYear.value
    if (m > 12) { m = 1; y++ }
    currentMonth.value = m
    currentYear.value = y
    goToMonth(m, y)
}

const onYearChange = (e: Event) => {
    const y = parseInt((e.target as HTMLSelectElement).value)
    currentYear.value = y
    goToMonth(currentMonth.value, y)
}

// ─── Calendar grid generation ───────────────────────────────────
const calendarDays = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value - 1, 1)
    const startWeekday = firstDay.getDay() // 0 = Sunday
    const daysInMonth = new Date(currentYear.value, currentMonth.value, 0).getDate()

    const days: Array<{ date: number | null; dateKey: string | null }> = []

    for (let i = 0; i < startWeekday; i++) {
        days.push({ date: null, dateKey: null })
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateKey = `${currentYear.value}-${String(currentMonth.value).padStart(2, '0')}-${String(d).padStart(2, '0')}`
        days.push({ date: d, dateKey })
    }

    return days
})

const isToday = (dateKey: string | null) => {
    if (!dateKey) return false
    const today = new Date()
    const todayKey = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
    return dateKey === todayKey
}

const countFor = (dateKey: string | null) => {
    if (!dateKey || !props.dutyLogs[dateKey]) return 0
    return props.dutyLogs[dateKey].length
}

// ─── Modal ────────────────────────────────────────────────────────
const showModal = ref(false)
const selectedDateKey = ref<string | null>(null)

const selectedEntries = computed(() => {
    if (!selectedDateKey.value) return []
    return props.dutyLogs[selectedDateKey.value] ?? []
})

const selectedOffDutyEntries = computed(() => {
    if (!selectedDateKey.value) return []
    return props.offDutyLogs[selectedDateKey.value] ?? []
})

const openDayModal = (dateKey: string | null) => {
    if (!dateKey) return
    selectedDateKey.value = dateKey
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedDateKey.value = null
}

const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
</script>

<template>

    <Head title="Duty Logs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full flex justify-center mt-10">
            <div class="w-[92%]">

                <!-- Calendar Header -->
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-black text-gray-800">On-Duty Logs</h1>

                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="icon" @click="prevMonth">
                            <PhCaretLeft :size="18" />
                        </Button>

                        <span class="text-lg font-bold text-gray-700 min-w-[140px] text-center">
                            {{ monthNames[currentMonth - 1] }}
                        </span>

                        <select :value="currentYear" @change="onYearChange"
                            class="border border-gray-300 rounded-md px-2 py-1.5 text-base font-medium text-gray-700">
                            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                        </select>

                        <Button variant="outline" size="icon" @click="nextMonth">
                            <PhCaretRight :size="18" />
                        </Button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div v-for="day in weekdayLabels" :key="day"
                            class="text-center text-sm font-bold text-gray-500 py-2">
                            {{ day }}
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        <div v-for="(day, idx) in calendarDays" :key="idx"
                            class="min-h-[90px] rounded-lg border p-2 flex flex-col" :class="[
                                day.date ? 'border-gray-200 hover:bg-gray-50 cursor-pointer' : 'border-transparent',
                                isToday(day.dateKey) ? 'ring-2 ring-orange-400' : ''
                            ]" @click="day.date && openDayModal(day.dateKey)">
                            <template v-if="day.date">
                                <span class="text-sm font-semibold text-gray-700">{{ day.date }}</span>

                                <div v-if="countFor(day.dateKey) > 0" class="mt-auto">
                                    <span
                                        class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                                        <PhUsers :size="12" weight="fill" />
                                        {{ countFor(day.dateKey) }}
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day Detail Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6 w-[50vw]">
                <h2 class="text-xl font-bold text-gray-900 mb-1">Responders on Duty</h2>
                <p class="text-base text-gray-500 mb-4">{{ selectedDateKey }}</p>

                <div v-if="selectedEntries.length === 0" class="py-8 text-center text-gray-400">
                    No responders checked in this day.
                </div>

                <div v-else class="space-y-2 max-h-80 overflow-y-auto">
                    <div v-for="(entry, idx) in selectedEntries" :key="idx"
                        class="flex items-center gap-5 justify-between p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <span class="font-semibold text-gray-800">{{ entry.name }}</span>
                        <span class="text-sm text-gray-500">{{ entry.time }}</span>
                    </div>
                </div>

                <h3 class="text-base font-bold text-gray-700 mt-6 mb-2">Not On Duty</h3>
                <div v-if="selectedOffDutyEntries.length === 0" class="py-4 text-center text-gray-400 text-sm">
                    Everyone was on duty this day.
                </div>
                <div v-else class="space-y-2 max-h-60 overflow-y-auto">
                    <div v-for="(entry, idx) in selectedOffDutyEntries" :key="idx"
                        class="flex items-center p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <span class="font-semibold text-gray-800">{{ entry.name }}</span>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <Button variant="outline" @click="closeModal">Close</Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>