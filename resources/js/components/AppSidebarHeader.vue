<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import Modal from '@/components/Modal.vue'
import type { BreadcrumbItem } from '@/types'
import { PhBell, PhX, PhWarning, PhShieldWarning, PhMegaphone } from '@phosphor-icons/vue'
import axios from 'axios'

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[]
    }>(),
    {
        breadcrumbs: () => [],
    },
)

const page = usePage()
const user = page.props.auth?.user as any

const showNotifs    = ref(false)
const notifications = ref<any[]>([])
const unreadCount   = ref(0)
let pollInterval: ReturnType<typeof setInterval> | null = null

// ─── Detail Modal ─────────────────────────────────────────────────────────────
const showDetailModal  = ref(false)
const selectedNotif    = ref<any | null>(null)

const openDetailModal = async (notif: any) => {
    selectedNotif.value = notif
    showDetailModal.value = true
    showNotifs.value = false
    await markAsRead(notif.id)
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedNotif.value = null
}
// ─────────────────────────────────────────────────────────────────────────────

const fetchNotifications = async () => {
    try {
        const res           = await axios.get('/announcement-alert/notifications')
        notifications.value = res.data.notifications
        unreadCount.value   = res.data.unread_count
    } catch {
        // silent — don't reset on error
    }
}

const markAsRead = async (id: number) => {
    const notif = notifications.value.find(n => n.id === id)
    if (!notif || notif.is_read) return

    try {
        await axios.post('/announcement-alert/mark-read', {
            announcement_id: id,
        })
        notif.is_read     = true
        unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch {
        // silent
    }
}

const toggleNotifs = async () => {
    showNotifs.value = !showNotifs.value
    if (showNotifs.value) {
        await fetchNotifications()
    }
}

const closePanel = () => {
    showNotifs.value = false
}

const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement
    if (!target.closest('.notif-wrapper')) {
        showNotifs.value = false
    }
}

onMounted(() => {
    fetchNotifications()
    pollInterval = setInterval(fetchNotifications, 60000)
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval)
    document.removeEventListener('click', handleClickOutside)
})

const audienceLabel = (n: any) => {
    if (n.is_incident_alert) return 'Incident'
    const parts = []
    if (n.for_citizens)       parts.push('Citizens')
    if (n.for_responders)     parts.push('Responders')
    if (n.for_administrators) parts.push('Admins')
    return parts.join(' & ') || 'All'
}

const audienceBadgeClass = (n: any) => {
    if (n.is_incident_alert)                          return 'bg-red-100 text-red-700'
    if (n.for_citizens && n.for_responders)           return 'bg-purple-100 text-purple-700'
    if (n.for_responders && n.for_administrators)     return 'bg-indigo-100 text-indigo-700'
    if (n.for_responders)                             return 'bg-blue-100 text-blue-700'
    if (n.for_citizens)                               return 'bg-green-100 text-green-700'
    if (n.for_administrators)                         return 'bg-yellow-100 text-yellow-700'
    return 'bg-gray-100 text-gray-600'
}

const rowClass = (n: any) => {
    if (n.is_read) return 'bg-white hover:bg-gray-50'
    if (n.is_incident_alert) return 'bg-red-50 hover:bg-red-100'
    return 'bg-orange-50 hover:bg-orange-100'
}

const dotClass = (n: any) =>
    n.is_incident_alert ? 'bg-red-500' : 'bg-orange-500'
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center bg-white gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2 flex-1">
            <SidebarTrigger class="ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Notification Bell -->
        <div class="relative notif-wrapper">
            <button
                @click.stop="toggleNotifs"
                class="relative p-2 rounded-full hover:bg-gray-100 transition-colors"
            >
                <PhBell :size="22" color="#404040" weight="fill" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-1"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </button>

            <!-- Dropdown Panel -->
            <div
                v-if="showNotifs"
                class="absolute right-0 top-12 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden notif-wrapper"
                @click.stop
            >
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-gray-800">Notifications</h3>
                        <span
                            v-if="unreadCount > 0"
                            class="text-xs bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded-full"
                        >
                            {{ unreadCount }} unread
                        </span>
                    </div>
                    <button @click.stop="closePanel" class="text-gray-400 hover:text-gray-600">
                        <PhX :size="16" />
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="px-4 py-3 border-b border-gray-50 transition-colors cursor-pointer"
                        :class="rowClass(notif)"
                        @click="openDetailModal(notif)"
                    >
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <PhWarning
                                    v-if="!notif.is_read && notif.is_incident_alert"
                                    :size="14"
                                    class="shrink-0 text-red-500"
                                    weight="fill"
                                />
                                <span
                                    v-else-if="!notif.is_read"
                                    class="shrink-0 w-2 h-2 rounded-full"
                                    :class="dotClass(notif)"
                                />
                                <p
                                    class="text-sm line-clamp-1"
                                    :class="notif.is_read
                                        ? 'text-gray-400 font-normal'
                                        : 'text-gray-800 font-semibold'"
                                >
                                    {{ notif.announcement_title }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 text-xs px-1.5 py-0.5 rounded font-medium"
                                :class="audienceBadgeClass(notif)"
                            >
                                {{ audienceLabel(notif) }}
                            </span>
                        </div>
                        <p
                            class="text-xs line-clamp-2 whitespace-pre-line"
                            :class="notif.is_read ? 'text-gray-400' : 'text-gray-600 ml-3.5'"
                        >
                            {{ notif.announcement_message }}
                        </p>
                        <p
                            class="text-xs text-gray-400 mt-1"
                            :class="notif.is_read ? '' : 'ml-3.5'"
                        >
                            {{ notif.time_ago }}
                        </p>
                    </div>

                    <div
                        v-if="notifications.length === 0"
                        class="px-4 py-6 text-center text-sm text-gray-400"
                    >
                        No notifications
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ─── Notification Detail Modal ──────────────────────────────────────── -->
    <Modal :show="showDetailModal" @close="closeDetailModal">
        <div v-if="selectedNotif" class="p-6 w-full max-w-md">

            <!-- Modal Header -->
            <div class="flex items-center gap-2 mb-5">
                <PhWarning
                    v-if="selectedNotif.is_incident_alert"
                    class="text-red-500 shrink-0"
                    :size="22"
                    weight="fill"
                />
                <PhMegaphone
                    v-else
                    class="text-orange-500 shrink-0"
                    :size="22"
                    weight="fill"
                />
                <h2 class="text-lg font-semibold text-gray-900 leading-tight">
                    {{ selectedNotif.announcement_title }}
                </h2>
            </div>

            <!-- Audience Badge + Time -->
            <div class="flex items-center gap-2 mb-4">
                <span
                    class="text-xs px-2 py-0.5 rounded-full font-semibold"
                    :class="audienceBadgeClass(selectedNotif)"
                >
                    {{ audienceLabel(selectedNotif) }}
                </span>
                <span class="text-xs text-gray-400">{{ selectedNotif.time_ago }}</span>
            </div>

            <!-- Message Body -->
            <div
                class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 whitespace-pre-line leading-relaxed mb-5"
            >
                {{ selectedNotif.announcement_message }}
            </div>

            <!-- Linked Incident (if any) -->
            <div
                v-if="selectedNotif.is_incident_alert"
                class="flex items-center gap-2 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 mb-5"
            >
                <PhShieldWarning :size="14" weight="fill" />
                This notification is linked to an incident report.
            </div>

            <!-- Footer -->
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button
                    @click="closeDetailModal"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
