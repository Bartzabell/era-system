<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import CustomInput from '@/components/CustomInput.vue'
import { PhBroadcast, PhTrash, PhMegaphone, PhClock, PhCalendar, PhUser } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { h } from 'vue'

interface Announcement {
    id: number
    announcement_title: string
    announcement_message: string
    for_citizens: boolean
    for_responders: boolean
    audience: string
    created_by_name: string
    time_ago: string
    created_at: string
}

const props = defineProps<{
    announcements: object
    broadcastAlerts: Announcement[]
    broadcastHistory: Announcement[]
    filters: object
}>()

const breadcrumbs = [
    { title: 'Homepage', href: '/landing' },
    { title: 'Announcements & Alerts', href: '/announcement-alert' },
]

const localFilters  = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get(route('announcement-alert.index'), newFilters, { preserveState: true, replace: true })
}

// Delete modal
const showDeleteModal  = ref(false)
const deletingRecord   = ref<Announcement | null>(null)
const openDeleteModal  = (record: Announcement) => {
    deletingRecord.value  = record
    showDeleteModal.value = true
}
const deleteRecord = () => {
    router.delete(`/announcement-alert/${deletingRecord.value!.id}`, {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

// Create form
const form = useForm({
    announcement_title:   '',
    announcement_message: '',
    for_citizens:         false,
    for_responders:       false,
})

const submitForm = () => {
    form.post('/announcement-alert', {
        onSuccess: () => form.reset(),
    })
}

// Audience badge
const audienceBadgeClass = (record: Announcement) => {
    if (record.for_citizens && record.for_responders) return 'bg-purple-100 text-purple-700'
    if (record.for_responders) return 'bg-blue-100 text-blue-700'
    if (record.for_citizens)   return 'bg-green-100 text-green-700'
    return 'bg-gray-100 text-gray-600'
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'announcement_title', header: 'Title' },
    {
        accessorKey: 'announcement_message',
        header: 'Message',
        cell: ({ row }: any) => h('p', { class: 'line-clamp-2 text-sm text-gray-600 max-w-xs' }, row.original.announcement_message),
    },
    {
        accessorKey: 'audience',
        header: 'Audience',
        cell: ({ row }: any) => h(
            'span',
            { class: `px-2 py-1 text-xs font-semibold rounded-full ${audienceBadgeClass(row.original)}` },
            row.original.audience
        ),
    },
    { accessorKey: 'created_by_name', header: 'Created By' },
    { accessorKey: 'created_at',      header: 'Created At' },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            h(Button, {
                variant: 'outline', size: 'icon',
                onClick: () => openDeleteModal(row.original),
                class: 'text-white bg-red-500 rounded-full hover:bg-red-400',
            }, () => h(PhTrash, { size: 16 })),
        ]),
    },
]
</script>

<template>
    <Head title="Announcements & Alerts" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="w-full flex justify-center mt-6">
            <div class="w-[92%] flex flex-col gap-6">

                <!-- Top section: Form + Broadcast Panels -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Create Announcement Form -->
                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <PhMegaphone :size="22" class="text-orange-500" weight="fill" />
                            <h2 class="text-lg font-bold text-gray-800">New Announcement</h2>
                        </div>

                        <form @submit.prevent="submitForm" class="flex flex-col gap-4">
                            <div>
                                <CustomInput
                                    name="Announcement Title"
                                    v-model="form.announcement_title"
                                />
                                <p v-if="form.errors.announcement_title" class="text-xs text-red-500 mt-1">
                                    {{ form.errors.announcement_title }}
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1 text-sm text-gray-600">Message <span class="text-red-500">*</span></label>
                                <textarea
                                    v-model="form.announcement_message"
                                    rows="4"
                                    placeholder="Write your announcement..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm resize-none focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-gray-700 dark:text-white"
                                />
                                <p v-if="form.errors.announcement_message" class="text-xs text-red-500 mt-1">
                                    {{ form.errors.announcement_message }}
                                </p>
                            </div>

                            <!-- Audience Toggles -->
                            <div>
                                <label class="block mb-2 text-sm text-gray-600">Broadcast To <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="form.for_citizens"
                                            class="w-4 h-4 accent-orange-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Citizens</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="form.for_responders"
                                            class="w-4 h-4 accent-orange-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Responders</span>
                                    </label>
                                </div>
                                <p v-if="!form.for_citizens && !form.for_responders && form.isDirty"
                                    class="text-xs text-orange-500 mt-1">
                                    Select at least one audience.
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <ButtonCode
                                    type="submit"
                                    :icon="PhBroadcast"
                                    color="bg-orange-500 hover:bg-orange-600"
                                    text="Broadcast"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </div>

                    <!-- Right: Broadcast Alerts + History -->
                    <div class="flex flex-col gap-4">

                        <!-- Today's Broadcasts -->
                        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
                            <div class="flex items-center gap-2 mb-1">
                                <PhClock :size="18" class="text-orange-500" weight="fill" />
                                <h3 class="text-sm font-bold text-gray-700">Today's Broadcasts</h3>
                                <span class="ml-auto text-xs bg-orange-100 text-orange-600 font-semibold px-2 py-0.5 rounded-full">
                                    {{ broadcastAlerts.length }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-2 max-h-56 overflow-y-auto">
                                <div
                                    v-for="alert in broadcastAlerts"
                                    :key="alert.id"
                                    class="rounded-lg border border-orange-100 bg-orange-50 p-3"
                                >
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <p class="text-xs font-bold text-gray-800 line-clamp-1">{{ alert.announcement_title }}</p>
                                        <span
                                            class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                            :class="{
                                                'bg-purple-100 text-purple-700': alert.for_citizens && alert.for_responders,
                                                'bg-blue-100 text-blue-700':    !alert.for_citizens && alert.for_responders,
                                                'bg-green-100 text-green-700':  alert.for_citizens && !alert.for_responders,
                                            }"
                                        >
                                            {{ alert.audience }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ alert.announcement_message }}</p>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <PhUser :size="10" /> {{ alert.created_by_name }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ alert.time_ago }}</span>
                                    </div>
                                </div>

                                <p v-if="broadcastAlerts.length === 0" class="text-xs text-gray-400 text-center py-3">
                                    No broadcasts today
                                </p>
                            </div>
                        </div>

                        <!-- This Week's History -->
                        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
                            <div class="flex items-center gap-2 mb-1">
                                <PhCalendar :size="18" class="text-gray-500" weight="fill" />
                                <h3 class="text-sm font-bold text-gray-700">This Week</h3>
                                <span class="ml-auto text-xs bg-gray-100 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
                                    {{ broadcastHistory.length }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-2 max-h-56 overflow-y-auto">
                                <div
                                    v-for="item in broadcastHistory"
                                    :key="item.id"
                                    class="rounded-lg border border-gray-100 bg-gray-50 p-3"
                                >
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <p class="text-xs font-bold text-gray-800 line-clamp-1">{{ item.announcement_title }}</p>
                                        <span
                                            class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                            :class="{
                                                'bg-purple-100 text-purple-700': item.for_citizens && item.for_responders,
                                                'bg-blue-100 text-blue-700':    !item.for_citizens && item.for_responders,
                                                'bg-green-100 text-green-700':  item.for_citizens && !item.for_responders,
                                            }"
                                        >
                                            {{ item.audience }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ item.announcement_message }}</p>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <PhUser :size="10" /> {{ item.created_by_name }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ item.created_at }}</span>
                                    </div>
                                </div>

                                <p v-if="broadcastHistory.length === 0" class="text-xs text-gray-400 text-center py-3">
                                    No broadcasts this week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Announcements Datatable -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">All Announcements</h2>
                    <DataTable
                        :columns="columns"
                        :data="announcements"
                        :show-per-page="true"
                        :filters="localFilters"
                        @update:filters="updateFilters"
                    />
                </div>

            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">Delete Announcement</h2>
                <p class="text-sm text-gray-600">
                    Are you sure you want to delete
                    <strong>{{ deletingRecord?.announcement_title }}</strong>?
                    This action cannot be undone.
                </p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false">Cancel</Button>
                    <Button @click="deleteRecord" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
