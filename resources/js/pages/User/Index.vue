<script setup lang="ts">
import { ref, h } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import { Button } from '@/components/ui/button'
import { PhPlus, PhPencil, PhTrash } from '@phosphor-icons/vue'
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
    filters: object
}>()

const breadcrumbs = [
    { title: 'Homepage', href: '/landing' },
    { title: 'User Management', href: '/users' },
]

const localFilters  = ref(props.filters)
const updateFilters = (newFilters: object) => {
    localFilters.value = newFilters
    router.get(route('users.index'), newFilters, { preserveState: true, replace: true })
}

// Modal state
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

// Role badge
const roleBadgeClass = (role: string) => {
    const map: Record<string, string> = {
        administrator: 'bg-red-100 text-red-700',
        responder:     'bg-blue-100 text-blue-700',
        citizen:       'bg-green-100 text-green-700',
    }
    return `px-2 py-1 text-xs font-semibold rounded-full capitalize ${map[role?.toLowerCase()] ?? 'bg-gray-100 text-gray-700'}`
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'username', header: 'Username' },
    {
        accessorKey: 'full_name',
        header: 'Full Name',
        cell: ({ row }: any) => row.original.full_name || '—',
    },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'mobile_no', header: 'Mobile No.' },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: ({ row }: any) => h(
            'span',
            { class: roleBadgeClass(row.original.role) },
            row.original.role ?? '—'
        ),
    },
    {
        accessorKey: 'barangay.barangay_name',
        header: 'Barangay',
        cell: ({ row }: any) => row.original.barangay?.barangay_name ?? '—',
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

    </AppLayout>
</template>
