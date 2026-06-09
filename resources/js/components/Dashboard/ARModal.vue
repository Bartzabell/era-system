<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { PhX, PhFireTruck, PhPhone, PhIdentificationCard, PhCheckCircle } from '@phosphor-icons/vue'

interface Responder {
    id: number
    first_name: string
    last_name: string
    contact_no?: string
    profile_picture?: string | null
    is_active: boolean
}

const props = defineProps<{
    show: boolean
    activeResponders: number
}>()

const emit = defineEmits<{ close: [] }>()

// Fetch responders when modal opens
const responders = ref<Responder[]>([])
const isLoading  = ref(false)

const fetchResponders = async () => {
    isLoading.value = true
    try {
        const res  = await fetch('/api/responders', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        const data = await res.json()
        console.log('RAW API response:', JSON.stringify(data[0], null, 2)) // ← check this
        responders.value = data?.data ?? data ?? []
    } catch (e) {
        console.error(e)
        responders.value = []
    } finally {
        isLoading.value = false
    }
}

const fullName = (r: Responder) => `${r.first_name} ${r.last_name}`

const initials = (r: Responder) => {
    const f = r.first_name?.[0] ?? ''
    const l = r.last_name?.[0] ?? ''
    return (f + l).toUpperCase()
}

const avatarColors = [
    'bg-blue-500', 'bg-indigo-500', 'bg-violet-500',
    'bg-sky-500',  'bg-cyan-500',   'bg-teal-500',
]
const getAvatarColor = (id: number) => avatarColors[id % avatarColors.length]

watch(() => props.show, (val) => { if (val) fetchResponders() })
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
                        <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-blue-700 to-blue-500">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-xl">
                                    <PhFireTruck :size="24" color="white" weight="fill" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-black text-white tracking-tight">Active Responders</h2>
                                    <p class="text-blue-100 text-base">Currently on duty</p>
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
                        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
                            <span class="text-4xl font-black text-blue-700">{{ activeResponders }}</span>
                            <div>
                                <p class="text-base font-semibold text-blue-600">responders on duty</p>
                                <p class="text-sm text-blue-400">Available and ready to deploy</p>
                            </div>
                        </div>

                        <!-- Responder List -->
                        <div class="max-h-[420px] overflow-y-auto px-6 py-4">
                            <!-- Loading -->
                            <div v-if="isLoading" class="space-y-3 py-4">
                                <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 rounded-xl animate-pulse" />
                            </div>

                            <!-- Empty state -->
                            <div v-else-if="responders.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                                <PhFireTruck :size="48" color="#e5e7eb" />
                                <p class="mt-3 text-base font-medium">No active responders found</p>
                                <p class="text-sm text-gray-300 mt-1">Responder data may not be available via API</p>
                            </div>

                            <!-- Responder cards -->
                            <div v-else class="space-y-3">
                                <div
                                    v-for="responder in responders"
                                    :key="responder.id"
                                    class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/40 hover:bg-blue-50 transition-colors"
                                >
                                    <!-- Avatar -->
                                    <div class="w-11 h-11 rounded-full shrink-0 overflow-hidden flex items-center justify-center text-white text-base font-black"
                                        :class="!responder.profile_picture ? getAvatarColor(responder.id) : ''">
                                        <img
                                            v-if="responder.profile_picture"
                                            :src="responder.profile_picture"
                                            :alt="fullName(responder)"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else>{{ initials(responder) }}</span>
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-base text-gray-800 truncate">{{ fullName(responder) }}</p>
                                            <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                                <PhCheckCircle :size="12" weight="fill" />
                                                Active
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span v-if="responder.responder_type" class="flex items-center gap-1 text-sm text-gray-500">
                                                <PhIdentificationCard :size="12" class="text-gray-400" />
                                                {{ responder.responder_type }}
                                            </span>
                                            <span v-if="responder.contact_no" class="flex items-center gap-1 text-sm text-gray-500">
                                                <PhPhone :size="12" class="text-gray-400" />
                                                {{ responder.contact_no }}
                                            </span>
                                            <span v-if="responder.station" class="text-sm text-blue-600 font-medium">
                                                {{ responder.station }}
                                            </span>
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
