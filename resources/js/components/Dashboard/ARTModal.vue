<script setup lang="ts">
import { computed } from 'vue'
import { PhX, PhClockCountdown, PhArrowUp, PhArrowDown, PhMinus, PhCheckCircle } from '@phosphor-icons/vue'

interface IncidentFeedItem {
    id: number
    incident_name: string
    landmark: string
    coordinates: { lat: number; lng: number }
    time_ago: string
    priority_score: number
    priority_level: string
    priority_label: string
    status: string
    created_at: string
}

const props = defineProps<{
    show: boolean
    avgResponseTime: string
    incidentFeed: IncidentFeedItem[]
}>()

const emit = defineEmits<{ close: [] }>()

// Parse the avgResponseTime string to a number for display logic
const avgMinutes = computed(() => {
    const match = props.avgResponseTime.match(/(\d+)/)
    return match ? parseInt(match[1]) : 0
})

const performanceLabel = computed(() => {
    if (avgMinutes.value === 0) return { label: 'No Data', color: 'text-gray-500', bg: 'bg-gray-100', icon: PhMinus }
    if (avgMinutes.value <= 5)  return { label: 'Excellent',  color: 'text-green-600',  bg: 'bg-green-50',  icon: PhArrowDown }
    if (avgMinutes.value <= 10) return { label: 'Good',       color: 'text-blue-600',   bg: 'bg-blue-50',   icon: PhArrowDown }
    if (avgMinutes.value <= 20) return { label: 'Acceptable', color: 'text-yellow-600', bg: 'bg-yellow-50', icon: PhMinus }
    return { label: 'Needs Improvement', color: 'text-red-600', bg: 'bg-red-50', icon: PhArrowUp }
})

const benchmarks = [
    { label: 'Target',    value: '≤ 5 min',  met: avgMinutes.value <= 5,  color: 'text-green-600' },
    { label: 'Good',      value: '≤ 10 min', met: avgMinutes.value <= 10, color: 'text-blue-600' },
    { label: 'Acceptable',value: '≤ 20 min', met: avgMinutes.value <= 20, color: 'text-yellow-600' },
]

// Response time breakdown from incidentFeed (resolved incidents with response data)
const resolvedWithTimes = computed(() =>
    props.incidentFeed
        .filter(i => i.status === 'resolved')
        .slice(0, 8)
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
                    <div v-if="show" class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-green-700 to-green-500">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-xl">
                                    <PhClockCountdown :size="24" color="white" weight="fill" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-black text-white tracking-tight">Avg. Response Time</h2>
                                    <p class="text-green-100 text-base">Time from report to arrival</p>
                                </div>
                            </div>
                            <button
                                @click="emit('close')"
                                class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-colors"
                            >
                                <PhX :size="20" />
                            </button>
                        </div>

                        <!-- Main metric -->
                        <div class="px-6 py-6 bg-green-50 border-b border-green-100">
                            <div class="flex items-end gap-4">
                                <div>
                                    <p class="text-6xl font-black text-green-700 leading-none">{{ avgResponseTime }}</p>
                                    <p class="text-base text-green-500 font-medium mt-1">average response time</p>
                                </div>
                                <div class="mb-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-base font-bold"
                                        :class="[performanceLabel.bg, performanceLabel.color]"
                                    >
                                        <component :is="performanceLabel.icon" :size="14" />
                                        {{ performanceLabel.label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Benchmarks -->
                        <div class="px-6 py-4 border-b border-gray-100">
                            <p class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-3">Performance Benchmarks</p>
                            <div class="space-y-2">
                                <div v-for="b in benchmarks" :key="b.label" class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <PhCheckCircle
                                            :size="16"
                                            :class="b.met ? 'text-green-500' : 'text-gray-300'"
                                            weight="fill"
                                        />
                                        <span class="text-base text-gray-600">{{ b.label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-semibold text-gray-500">{{ b.value }}</span>
                                        <span
                                            class="text-sm font-bold px-2 py-0.5 rounded-full"
                                            :class="b.met ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                        >
                                            {{ b.met ? 'Met' : 'Not Met' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress arc / bar -->
                        <div class="px-6 py-4 border-b border-gray-100">
                            <p class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-3">Performance Scale</p>
                            <div class="relative w-full h-3 bg-gradient-to-r from-green-400 via-yellow-400 to-red-500 rounded-full overflow-hidden">
                                <!-- Marker -->
                                <div
                                    class="absolute top-0 bottom-0 w-1 bg-white shadow-md rounded-full transition-all duration-500"
                                    :style="{ left: `${Math.min((avgMinutes / 30) * 100, 100)}%` }"
                                />
                            </div>
                            <div class="flex justify-between text-sm text-gray-400 mt-1">
                                <span>0 min</span>
                                <span>10 min</span>
                                <span>20 min</span>
                                <span>30+ min</span>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="px-6 py-4 bg-gray-50">
                            <p class="text-sm text-gray-400 italic">
                                Calculated from incident report creation time to responder arrival time across all resolved incidents.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
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
