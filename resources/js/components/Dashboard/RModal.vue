<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { PhX, PhCheckCircle, PhMapPin, PhClock, PhCalendar } from '@phosphor-icons/vue'

interface ResolvedIncident {
    id: number
    incident_code?: string
    incident_name?: string
    barangay_name?: string
    emergency_name?: string
    priority_level?: string
    priority_label?: string
    updated_at?: string
    datetime_arrived?: string
    reported_at?: string
}

const props = defineProps<{
    show: boolean
    resolvedToday: number
}>()

const emit = defineEmits<{ close: [] }>()

const incidents  = ref<ResolvedIncident[]>([])
const isLoading  = ref(false)

const fetchResolved = async () => {
    isLoading.value = true
    try {
        const today = new Date().toISOString().split('T')[0]
        const res   = await fetch(`/api/incident-reports?status=resolved&date=${today}&per_page=20`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        const data = await res.json()
        incidents.value = data?.data ?? data ?? []
    } catch {
        incidents.value = []
    } finally {
        isLoading.value = false
    }
}

watch(() => props.show, (val) => { if (val) fetchResolved() })

const formatTime = (iso?: string) => {
    if (!iso) return ''
    return new Date(iso).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
}

const priorityConfig: Record<string, { bg: string; text: string; border: string }> = {
    P1: { bg: 'bg-red-50',     text: 'text-red-700',    border: 'border-red-200' },
    P2: { bg: 'bg-orange-50',  text: 'text-orange-700', border: 'border-orange-200' },
    P3: { bg: 'bg-yellow-50',  text: 'text-yellow-700', border: 'border-yellow-200' },
    P4: { bg: 'bg-blue-50',    text: 'text-blue-700',   border: 'border-blue-200' },
    P5: { bg: 'bg-gray-50',    text: 'text-gray-600',   border: 'border-gray-200' },
}
const getPriority = (level?: string) => priorityConfig[level ?? ''] ?? priorityConfig['P5']

const todayLabel = computed(() =>
    new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
)
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="emit('close')">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')" />

                <!-- Modal -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div v-if="show" class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-slate-700 to-slate-500">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-xl">
                                    <PhCheckCircle :size="24" color="white" weight="fill" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-white tracking-tight">Resolved Today</h2>
                                    <p class="text-slate-200 text-sm">Incidents closed on {{ todayLabel }}</p>
                                </div>
                            </div>
                            <button
                                @click="emit('close')"
                                class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-colors"
                            >
                                <PhX :size="20" />
                            </button>
                        </div>

                        <!-- Count bar -->
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="flex items-end gap-2">
                                    <span class="text-5xl font-black text-slate-700">{{ resolvedToday }}</span>
                                    <span class="text-sm font-semibold text-slate-400 mb-1.5">incidents resolved today</span>
                                </div>
                                <div class="ml-auto flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-xl">
                                    <PhCheckCircle :size="14" weight="fill" />
                                    All Clear
                                </div>
                            </div>
                        </div>

                        <!-- List -->
                        <div class="max-h-[420px] overflow-y-auto px-6 py-4 space-y-3">
                            <!-- Loading -->
                            <div v-if="isLoading" class="space-y-3 py-4">
                                <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 rounded-xl animate-pulse" />
                            </div>

                            <!-- Empty state (0 from server) -->
                            <div v-else-if="resolvedToday === 0" class="flex flex-col items-center justify-center py-14 text-gray-400">
                                <PhCalendar :size="48" color="#e5e7eb" />
                                <p class="mt-3 text-sm font-semibold text-gray-500">No incidents resolved today</p>
                                <p class="text-xs text-gray-300 mt-1">Check back later as incidents are resolved</p>
                            </div>

                            <!-- No API data but count > 0 -->
                            <template v-else-if="incidents.length === 0 && resolvedToday > 0">
                                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                                    <PhCheckCircle :size="48" color="#d1fae5" weight="fill" />
                                    <p class="mt-3 text-sm font-semibold text-green-600">{{ resolvedToday }} incident{{ resolvedToday > 1 ? 's' : '' }} resolved today</p>
                                    <p class="text-xs text-gray-400 mt-1">Detailed list unavailable — API endpoint may not exist yet</p>
                                </div>
                            </template>

                            <!-- Resolved incidents list -->
                            <div
                                v-for="(incident, idx) in incidents"
                                :key="incident.id"
                                class="flex items-start gap-3 p-4 rounded-xl border transition-all hover:shadow-sm bg-white"
                                :class="[getPriority(incident.priority_level).border]"
                            >
                                <!-- Index -->
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 text-xs font-black flex items-center justify-center shrink-0 mt-0.5">
                                    {{ idx + 1 }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-bold text-sm text-gray-800 leading-tight">
                                                {{ incident.incident_name ?? 'Unknown Incident' }}
                                            </p>
                                            <p v-if="incident.incident_code" class="text-xs text-gray-400 font-mono mt-0.5">
                                                {{ incident.incident_code }}
                                            </p>
                                        </div>
                                        <span
                                            v-if="incident.priority_level"
                                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border"
                                            :class="[getPriority(incident.priority_level).bg, getPriority(incident.priority_level).text, getPriority(incident.priority_level).border]"
                                        >
                                            {{ incident.priority_level }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 mt-2">
                                        <div v-if="incident.barangay_name" class="flex items-center gap-1 text-xs text-gray-500">
                                            <PhMapPin :size="12" weight="fill" class="text-gray-400" />
                                            {{ incident.barangay_name }}
                                        </div>
                                        <div v-if="incident.updated_at" class="flex items-center gap-1 text-xs text-gray-500">
                                            <PhClock :size="12" class="text-gray-400" />
                                            Resolved at {{ formatTime(incident.updated_at) }}
                                        </div>
                                        <!-- Resolved badge -->
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">
                                            <PhCheckCircle :size="11" weight="fill" />
                                            Resolved
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <p class="text-xs text-gray-400 flex items-center gap-1">
                                <PhCalendar :size="12" />
                                Data as of {{ todayLabel }}
                            </p>
                            <button
                                @click="emit('close')"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
