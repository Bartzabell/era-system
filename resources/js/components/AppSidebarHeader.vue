<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import type { BreadcrumbItem } from '@/types'
import { PhBell, PhX } from '@phosphor-icons/vue'
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

const fetchNotifications = async () => {
    try {
        const res           = await axios.get('/announcement-alert/notifications')
        notifications.value = res.data.notifications
        unreadCount.value   = res.data.unread_count
    } catch {
        // silent — don't reset on error
    }
}

// Only called when user explicitly clicks a notification item
const markAsRead = async (id: number) => {
    const notif = notifications.value.find(n => n.id === id)
    if (!notif || notif.is_read) return // already read, do nothing

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
        // Just fetch — do NOT mark anything as read yet
        await fetchNotifications()
    }
}

const closePanel = () => {
    showNotifs.value = false
    // Do NOT mark as read on close either
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
    const parts = []
    if (n.for_citizens)   parts.push('Citizens')
    if (n.for_responders) parts.push('Responders')
    return parts.join(' & ') || 'All'
}

const audienceBadgeClass = (n: any) => {
    if (n.for_citizens && n.for_responders) return 'bg-purple-100 text-purple-700'
    if (n.for_responders)                   return 'bg-blue-100 text-blue-700'
    if (n.for_citizens)                     return 'bg-green-100 text-green-700'
    return 'bg-gray-100 text-gray-600'
}
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
                <!-- Badge — only renders when unreadCount > 0 -->
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
                        <h3 class="font-bold text-sm text-gray-800">Announcements</h3>
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
                        :class="notif.is_read
                            ? 'bg-white hover:bg-gray-50'
                            : 'bg-orange-50 hover:bg-orange-100'"
                        @click="markAsRead(notif.id)"
                    >
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <!-- Unread dot -->
                                <span
                                    v-if="!notif.is_read"
                                    class="shrink-0 w-2 h-2 rounded-full bg-orange-500"
                                />
                                <p class="text-sm line-clamp-1"
                                    :class="notif.is_read
                                        ? 'text-gray-400 font-normal'
                                        : 'text-gray-800 font-semibold'">
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
                        <p class="text-xs line-clamp-2"
                            :class="notif.is_read
                                ? 'text-gray-400'
                                : 'text-gray-600 ml-3.5'">
                            {{ notif.announcement_message }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1"
                            :class="notif.is_read ? '' : 'ml-3.5'">
                            {{ notif.time_ago }}
                        </p>
                    </div>

                    <div v-if="notifications.length === 0"
                        class="px-4 py-6 text-center text-sm text-gray-400">
                        No announcements
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
