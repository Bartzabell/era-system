<script setup lang="ts">
import { computed } from 'vue'
import { PhX, PhMapPin, PhClock, PhWarning } from '@phosphor-icons/vue'
import { Flame } from 'lucide-vue-next'

interface IncidentMarker {
    id: number
    lat: number
    lng: number
    priority_level: string
    priority_label: string
    priority_score: number
    status: string
    incident_type: string
    emergency_type: string
    barangay: string
    casualty_count: number
    created_at: string
}

const props = defineProps<{
    show: boolean
    activeIncidents: number
    incidentMarkers: IncidentMarker[]
}>()

const emit = defineEmits<{ close: [] }>()

const priorityConfig: Record<string, { bg: string; text: string; border: string; dot: string; label: string }> = {
    P1: { bg: 'bg-red-50',     text: 'text-red-700',    border: 'border-red-200',    dot: 'bg-red-600',    label: 'Critical' },
    P2: { bg: 'bg-orange-50',  text: 'text-orange-700', border: 'border-orange-200', dot: 'bg-orange-500', label: 'High' },
    P3: { bg: 'bg-yellow-50',  text: 'text-yellow-700', border: 'border-yellow-200', dot: 'bg-yellow-400', label: 'Moderate' },
    P4: { bg: 'bg-blue-50',    text: 'text-blue-700',   border: 'border-blue-200',   dot: 'bg-blue-500',   label: 'Low' },
    P5: { bg: 'bg-gray-50',    text: 'text-gray-600',   border: 'border-gray-200',   dot: 'bg-gray-400',   label: 'Info' },
}

const getPriority = (level: string) => priorityConfig[level] ?? priorityConfig['P5']

const sortedIncidents = computed(() =>
    [...props.incidentMarkers].sort((a, b) => (b.priority_score ?? 0) - (a.priority_score ?? 0))
)

const prioritySummary = computed(() => {
    const counts: Record<string, number> = { P1: 0, P2: 0, P3: 0, P4: 0, P5: 0 }
    props.incidentMarkers.forEach(i => { if (i.priority_level in counts) counts[i.priority_level]++ })
    return counts
})
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
                        <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-red-600 to-red-500">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-xl">
                                    <Flame :size="24" color="white" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-black text-white tracking-tight">Active Incidents</h2>
                                    <p class="text-red-100 text-base">Live incident overview</p>
                                </div>
                            </div>
                            <button
                                @click="emit('close')"
                                class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-colors"
                            >
                                <PhX :size="20" />
                            </button>
                        </div>

                        <!-- Stats Bar -->
                        <div class="px-6 py-4 bg-red-50 border-b border-red-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-4xl font-black text-red-700">{{ activeIncidents }}</span>
                                    <span class="text-base text-red-500 font-medium">total active</span>
                                </div>
                                <!-- Priority breakdown pills -->
                                <div class="flex gap-2">
                                    <template v-for="(count, level) in prioritySummary" :key="level">
                                        <div
                                            v-if="count > 0"
                                            class="flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-bold border"
                                            :class="[getPriority(level).bg, getPriority(level).text, getPriority(level).border]"
                                        >
                                            <span class="w-2 h-2 rounded-full" :class="getPriority(level).dot" />
                                            {{ level }}: {{ count }}
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Incident List -->
                        <div class="max-h-[420px] overflow-y-auto px-6 py-4 space-y-3">
                            <div v-if="sortedIncidents.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                                <Flame :size="48" color="#e5e7eb" />
                                <p class="mt-3 text-base font-medium">No active incidents</p>
                                <p class="text-sm text-gray-300 mt-1">All clear — no incidents to display</p>
                            </div>

                            <div
                                v-for="incident in sortedIncidents"
                                :key="incident.id"
                                class="flex items-start gap-3 p-4 rounded-xl border transition-all hover:shadow-sm"
                                :class="[getPriority(incident.priority_level).bg, getPriority(incident.priority_level).border]"
                            >
                                <!-- Priority indicator -->
                                <div class="flex flex-col items-center gap-1 shrink-0 mt-0.5">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-sm font-black border"
                                        :class="[getPriority(incident.priority_level).text, getPriority(incident.priority_level).border, getPriority(incident.priority_level).bg]"
                                    >
                                        {{ incident.priority_level }}
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-bold text-base text-gray-800 leading-tight">{{ incident.incident_type }}</p>
                                            <p class="text-sm text-gray-500 mt-0.5">{{ incident.emergency_type }}</p>
                                        </div>
                                        <div class="flex flex-col items-end gap-1 shrink-0">
                                            <span
                                                v-if="incident.priority_score"
                                                class="text-sm font-semibold"
                                                :class="getPriority(incident.priority_level).text"
                                            >
                                                {{ incident.priority_score }}/10
                                            </span>
                                            <span class="text-sm px-2 py-0.5 rounded-full bg-white/70 text-gray-600 border border-gray-200 capitalize">
                                                {{ incident.status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 mt-2">
                                        <div class="flex items-center gap-1 text-sm text-gray-500">
                                            <PhMapPin :size="12" weight="fill" class="text-gray-400" />
                                            <span>{{ incident.barangay }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-sm text-gray-500">
                                            <PhClock :size="12" class="text-gray-400" />
                                            <span>{{ incident.created_at }}</span>
                                        </div>
                                        <div v-if="incident.casualty_count > 0" class="flex items-center gap-1 text-sm text-red-600 font-medium">
                                            <PhWarning :size="12" weight="fill" />
                                            <span>{{ incident.casualty_count }} {{ incident.casualty_count === 1 ? 'casualty' : 'casualties' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button
                                @click="emit('close')"
                                class="px-4 py-2 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
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
