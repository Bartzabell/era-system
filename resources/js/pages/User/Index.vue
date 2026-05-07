<script setup lang="ts">
import { ref, h, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import { Button } from '@/components/ui/button'
import { PhPlus, PhPencil, PhTrash, PhShieldCheck } from '@phosphor-icons/vue'
import FormModal from './Partials/FormModal.vue'

interface User {
    id: number
    username: string
    full_name: string
    first_name: string
    middle_name: string
    last_name: string
    email: string
    mobile_no: string
    birth_date: string
    role: string
    admin_verified: string | null
    valid_id: string | null
    barangay?: { id: number; barangay_name: string }
    permissions: Array<{ id: number; name: string; slug: string }>
    responder: any | null
    created_at: string
}

const props = defineProps<{
    users: object
    barangays: Array<{ id: number; barangay_name: string }>
    roles: Array<{ id: number; role_name: string }>
    permissions: Array<{ id: number; name: string; slug: string }>
    filters: { per_page?: number; tab?: string }
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Management', href: '/users' },
]

// ─── Tab state ───────────────────────────────────────────────────────────────
const activeTab = ref<string>(props.filters?.tab ?? 'all')

const tabs = [
    { key: 'all',      label: 'All Users' },
    { key: 'verified', label: 'Verified' },
    { key: 'pending',  label: 'Admin Approval' },
]

const switchTab = (tabKey: string) => {
    activeTab.value = tabKey
    router.get(
        '/users',
        { ...props.filters, tab: tabKey, page: 1 },
        { preserveState: true, replace: true }
    )
}

// ─── Filters ─────────────────────────────────────────────────────────────────
const localFilters = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = { ...newFilters, tab: activeTab.value }
    router.get('/users', localFilters.value, { preserveState: true, replace: true })
}

// ─── CRUD Modals ──────────────────────────────────────────────────────────────
const isFormVisible   = ref(false)
const showDeleteModal = ref(false)
const formMode        = ref<'create' | 'edit'>('create')
const currentRecord   = ref<User | null>(null)

const openCreateModal = () => {
    formMode.value      = 'create'
    currentRecord.value = null
    isFormVisible.value = true
}

const openEditModal = (record: User) => {
    formMode.value      = 'edit'
    currentRecord.value = record
    isFormVisible.value = true
}

const closeFormModal = () => {
    isFormVisible.value = false
    currentRecord.value = null
}

const openDeleteModal = (record: User) => {
    currentRecord.value   = record
    showDeleteModal.value = true
}

const deleteUser = () => {
    router.delete(`/users/${currentRecord.value!.id}`, {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

// ─── Verification Modal ───────────────────────────────────────────────────────
const showVerifyModal   = ref(false)
const verifyRecord      = ref<User | null>(null)
const verifyImageError  = ref(false)

const openVerifyModal = (record: User) => {
    verifyRecord.value     = record
    verifyImageError.value = false
    showVerifyModal.value  = true
}

const closeVerifyModal = () => {
    showVerifyModal.value = false
    verifyRecord.value    = null
}

const validIdUrl = computed(() => {
    if (!verifyRecord.value?.valid_id) return null
    return `/storage/${verifyRecord.value.valid_id}`
})

const verifyAccount = () => {
    router.post(`/users/${verifyRecord.value!.id}/verify`, {}, {
        onSuccess: () => { closeVerifyModal() },
    })
}

const rejectAccount = () => {
    router.delete(`/users/${verifyRecord.value!.id}/reject-verification`, {
        onSuccess: () => { closeVerifyModal() },
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const roleBadgeClass = (role: string) => {
    const map: Record<string, string> = {
        administrator: 'bg-red-100 text-red-700',
        responder:     'bg-blue-100 text-blue-700',
        citizen:       'bg-green-100 text-green-700',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full capitalize ${map[role?.toLowerCase()] ?? 'bg-gray-100 text-gray-700'}`
}

// ─── Columns ──────────────────────────────────────────────────────────────────
const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'username', header: 'Username' },
    {
        accessorKey: 'full_name',
        header: 'Full Name',
        cell: ({ row }: any) => row.original.full_name || '',
    },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'mobile_no', header: 'Mobile No.' },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: ({ row }: any) => h(
            'span',
            { class: roleBadgeClass(row.original.role) },
            row.original.role ?? ''
        ),
    },
    {
        accessorKey: 'barangay.barangay_name',
        header: 'Barangay',
        cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '',
    },
    {
        accessorKey: 'admin_verified',
        header: 'Verified',
        cell: ({ row }: any) => {
            const v = row.original.admin_verified
            return h(
                'span',
                {
                    class: v === 'yes'
                        ? 'px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700'
                        : 'px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700'
                },
                v === 'yes' ? 'Verified' : 'Pending'
            )
        },
    },
    {
        accessorKey: 'permissions',
        header: 'Permissions',
        cell: ({ row }: any) => {
            const perms = row.original.permissions ?? []
            if (!perms.length) return h('span', { class: 'text-gray-400 text-xs' }, 'None')
            return h('div', { class: 'flex flex-wrap gap-1' },
                perms.map((p: any) => h(
                    'span',
                    { class: 'px-1.5 py-0.5 text-xs bg-orange-100 text-orange-700 rounded' },
                    p.name
                ))
            )
        },
    },
    {
        accessorKey: 'responder',
        header: 'Responder',
        cell: ({ row }: any) => h(
            'span',
            {
                class: row.original.responder
                    ? 'px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700'
                    : 'px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500'
            },
            row.original.responder ? 'Yes' : 'No'
        ),
    },
    { accessorKey: 'created_at', header: 'Created At' },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }: any) => h('div', { class: 'flex space-x-2' }, [
            row.original.admin_verified !== 'yes'
                ? h(Button, {
                    variant: 'outline', size: 'icon',
                    onClick: () => openVerifyModal(row.original),
                    class: 'text-white bg-emerald-500 rounded-full hover:bg-emerald-400',
                    title: 'Review & Verify',
                  }, () => h(PhShieldCheck, { size: 18 }))
                : null,
            h(Button, {
                variant: 'outline', size: 'icon',
                onClick: () => openEditModal(row.original),
                class: 'text-white bg-sky-500 rounded-full hover:bg-sky-400',
            }, () => h(PhPencil, { size: 18 })),
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
    <Head title="User Management" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Form Modal -->
        <Modal :show="isFormVisible" @close="closeFormModal" class="fixed inset-0 z-50">
            <FormModal
                v-if="isFormVisible"
                :mode="formMode"
                :record="currentRecord"
                :barangays="barangays"
                :roles="roles"
                :permissions="permissions"
                @close="closeFormModal"
                @success="closeFormModal"
            />
        </Modal>

        <div class="w-full flex justify-center mt-10">
            <div class="w-[92%]">

                <ButtonCode
                    @click="openCreateModal"
                    text="Add User"
                    :icon="PhPlus"
                    color="bg-orange-500 hover:bg-orange-600"
                />

                <!-- Tabs -->
                <div class="flex gap-1 mt-4 mb-4 border-b border-gray-200">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="switchTab(tab.key)"
                        :class="[
                            'px-4 py-2 text-sm font-medium transition-colors rounded-t-md',
                            activeTab === tab.key
                                ? 'border-b-2 border-orange-500 text-orange-600 bg-orange-50'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.key === 'pending'"
                            class="ml-1.5 px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full"
                        >
                            !
                        </span>
                    </button>
                </div>

                <DataTable
                    :columns="columns"
                    :data="users"
                    :show-per-page="true"
                    :filters="localFilters"
                    @update:filters="updateFilters"
                />
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-gray-900">Delete User</h2>
                <p class="text-sm text-gray-600">
                    Are you sure you want to delete
                    <strong>{{ currentRecord?.full_name || currentRecord?.username }}</strong>?
                    This action cannot be undone.
                </p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false">Cancel</Button>
                    <Button @click="deleteUser" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

        <!-- Verification Modal -->
        <Modal :show="showVerifyModal" @close="closeVerifyModal">
            <div class="p-6 max-w-lg w-full">

                <div class="flex items-center gap-2 mb-5">
                    <PhShieldCheck class="text-emerald-500" :size="22" />
                    <h2 class="text-lg font-semibold text-gray-900">Account Verification</h2>
                </div>

                <!-- User Info -->
                <div v-if="verifyRecord" class="space-y-3 mb-5">
                    <div class="bg-gray-50 rounded-lg p-4 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div>
                            <span class="text-gray-500 block text-xs">Full Name</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.full_name || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Username</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.username }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Email</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.email || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Mobile No.</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.mobile_no || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Role</span>
                            <span class="font-medium capitalize text-gray-800">{{ verifyRecord.role }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Barangay</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.barangay?.barangay_name || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Birth Date</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.birth_date || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Registered</span>
                            <span class="font-medium text-gray-800">{{ verifyRecord.created_at }}</span>
                        </div>
                    </div>

                    <!-- Valid ID -->
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Valid ID</p>
                        <div
                            v-if="validIdUrl && !verifyImageError"
                            class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50"
                        >
                            <img
                                :src="validIdUrl"
                                alt="Valid ID"
                                class="w-full object-contain max-h-64"
                                @error="verifyImageError = true"
                            />
                        </div>
                        <div
                            v-else
                            class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-sm text-gray-400"
                        >
                            {{ verifyImageError ? 'Unable to load valid ID image.' : 'No valid ID uploaded.' }}
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <Button variant="outline" @click="closeVerifyModal">Cancel</Button>
                    <Button
                        @click="rejectAccount"
                        class="bg-red-600 hover:bg-red-700 text-white"
                    >
                        Delete Account
                    </Button>
                    <Button
                        @click="verifyAccount"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white"
                    >
                        Verify Account
                    </Button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>
