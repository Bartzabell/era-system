<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import CustomInput from '@/components/CustomInput.vue'
import { PhBroadcast, PhTrash, PhSiren, PhCalendar, PhUser } from '@phosphor-icons/vue'
import { Megaphone } from 'lucide-vue-next'
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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Announcements & Alerts', href: '/announcement-alert' },
]

const localFilters = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get(route('announcement-alert.index'), newFilters, { preserveState: true, replace: true })
}

const showDeleteModal = ref(false)
const deletingRecord = ref<Announcement | null>(null)
const openDeleteModal = (record: Announcement) => {
    deletingRecord.value = record
    showDeleteModal.value = true
}
const deleteRecord = () => {
    router.delete(`/announcement-alert/${deletingRecord.value!.id}`, {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

const form = useForm({
    announcement_title: '',
    announcement_message: '',
    for_citizens: false,
    for_responders: false,
})

const submitForm = () => {
    form.post('/announcement-alert', {
        onSuccess: () => form.reset(),
    })
}

const formatDate = (dateStr: string | null): string => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return dateStr
    return date.toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    })
}

const audienceBadgeClass = (record: any) => {
    if (record.for_citizens && record.for_responders) return 'bg-red-100 text-red-700'
    if (record.for_responders) return 'bg-orange-100 text-orange-700'
    if (record.for_citizens) return 'bg-green-100 text-green-700'
    return 'bg-gray-100 text-gray-600'
}

const audienceBadgeLabel = (record: any) => {
    if (record.for_citizens && record.for_responders) return 'General'
    if (record.for_responders) return 'Responders'
    if (record.for_citizens) return 'Citizens'
    return 'None'
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'announcement_title', header: 'Title' },
    {
        accessorKey: 'announcement_message',
        header: 'Message',
        cell: ({ row }: any) => h(
            'p',
            { class: 'line-clamp-2 text-base text-gray-600 max-w-xs' },
            row.original.announcement_message
        ),
    },
    {
        accessorKey: 'audience',
        header: 'Audience',
        cell: ({ row }: any) => h(
            'span',
            { class: `px-2 py-1 text-sm font-semibold rounded-full ${audienceBadgeClass(row.original)}` },
            audienceBadgeLabel(row.original)
        ),
    },
    {
        accessorKey: 'creator.full_name',
        header: 'Created By',
        cell: ({ row }: any) => row.original.creator?.full_name ?? row.original.created_by_name ?? '',
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: ({ row }: any) => formatDate(row.original.created_at),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            h(Button, {
                variant: 'outline', size: 'icon',
                onClick: () => openDeleteModal(row.original),
                class: 'text-white bg-red-500 rounded-full hover:bg-red-600',
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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-2">
                        <div class="flex items-center justify-center text-center gap-2 mb-4 bg-orange-100 rounded-lg p-2">
                            <Megaphone :size="22" weight="fill" class="text-orange-600" />
                            <h2 class="text-2xl font-black text-gray-800">Broadcast Message</h2>
                        </div>

                        <form @submit.prevent="submitForm" class="flex flex-col gap-4 m-4">
                            <div>
                                <CustomInput
                                    name="Announcement Title"
                                    v-model="form.announcement_title"
                                    class="font-black"
                                />
                                <p v-if="form.errors.announcement_title" class="text-sm text-red-500 mt-1">
                                    {{ form.errors.announcement_title }}
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1 text-base text-gray-600">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.announcement_message"
                                    rows="4"
                                    placeholder="Write your announcement..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-base resize-none focus:outline-none focus:ring-2 focus:ring-orange-400"
                                />
                                <p v-if="form.errors.announcement_message" class="text-sm text-red-500 mt-1">
                                    {{ form.errors.announcement_message }}
                                </p>
                            </div>

                            <div>
                                <label class="block mb-2 text-base text-gray-600">
                                    Broadcast To <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="form.for_citizens"
                                            class="w-4 h-4 accent-orange-500"
                                        />
                                        <span class="text-base font-medium text-gray-700">Citizens</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="form.for_responders"
                                            class="w-4 h-4 accent-orange-500"
                                        />
                                        <span class="text-base font-medium text-gray-700">Responders</span>
                                    </label>
                                </div>
                                <p v-if="!form.for_citizens && !form.for_responders && form.isDirty"
                                    class="text-sm text-orange-500 mt-1">
                                    Select at least one audience.
                                </p>
                            </div>

                            <div class="flex justify-center">
                                <ButtonCode
                                    type="submit"
                                    :icon="PhBroadcast"
                                    color="bg-orange-500 hover:bg-orange-600"
                                    text="Send Broadcast"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </div>

                    <div class="flex flex-col gap-4">

                        <div class="bg-white rounded-xl border border-gray-200 flex flex-col gap-2">
                            <div class="flex items-center gap-2 mb-1 bg-gray-200 p-2 rounded-xl">
                                <PhSiren :size="32" class="text-red-500" weight="fill" />
                                <h3 class="text-base font-bold text-gray-700">Today's Broadcasted Alerts</h3>
                                <span class="ml-auto text-sm bg-red-100 text-red-600 font-semibold px-2 py-0.5 rounded-full">
                                    {{ broadcastAlerts.length }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-2 max-h-56 overflow-y-auto p-4">
                                <div
                                    v-for="alert in broadcastAlerts"
                                    :key="alert.id"
                                    class="rounded-lg border border-red-100 bg-red-50 p-3"
                                >
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <p class="text-sm font-bold text-gray-800 line-clamp-1">
                                            {{ alert.announcement_title }}
                                        </p>
                                        <span
                                            class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                            :class="audienceBadgeClass(alert)"
                                        >
                                            {{ audienceBadgeLabel(alert) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 line-clamp-2">{{ alert.announcement_message }}</p>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <PhUser :size="10" /> {{ alert.created_by_name }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ alert.time_ago }}</span>
                                    </div>
                                </div>

                                <p v-if="broadcastAlerts.length === 0"
                                    class="text-sm text-gray-400 text-center py-3">
                                    No broadcasts today
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 flex flex-col gap-2">
                            <div class="flex items-center gap-2 mb-1 bg-gray-200 p-2 rounded-xl">
                                <PhCalendar :size="32" class="text-gray-500" weight="fill" />
                                <h3 class="text-base font-bold text-gray-700">Previous Broadcast (Weekly)</h3>
                                <span class="ml-auto text-sm bg-gray-200 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
                                    {{ broadcastHistory.length }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-2 max-h-56 overflow-y-auto p-4">
                                <div
                                    v-for="item in broadcastHistory"
                                    :key="item.id"
                                    class="rounded-lg border border-gray-200 bg-gray-50 p-3"
                                >
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <p class="text-sm font-bold text-gray-800 line-clamp-1">
                                            {{ item.announcement_title }}
                                        </p>
                                        <span
                                            class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                            :class="audienceBadgeClass(item)"
                                        >
                                            {{ audienceBadgeLabel(item) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 line-clamp-2">{{ item.announcement_message }}</p>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <PhUser :size="10" /> {{ item.created_by_name }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ formatDate(item.created_at) }}</span>
                                    </div>
                                </div>

                                <p v-if="broadcastHistory.length === 0"
                                    class="text-sm text-gray-400 text-center py-3">
                                    No broadcasts this week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">All Announcements</h2>
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

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-xl font-medium text-gray-900">Delete Announcement</h2>
                <p class="text-base text-gray-600">
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
