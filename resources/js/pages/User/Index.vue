<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import CreateForm from './Partials/Create.vue';
import EditForm from './Partials/Edit.vue';
import { PhPlus } from '@phosphor-icons/vue';
import { Pencil, Trash2 } from 'lucide-vue-next';

interface User {
    id: number;
    username: string;
    full_name: string;
    email: string;
    mobile_no: string;
    role: string;
    barangay?: { id: number; barangay_name: string };
    created_at: string;
}

interface Props {
    users: User[];
    barangays: any[];
    roles: any[];
    permissions: any[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingUserId = ref<number | null>(null);

const columns = [
    { key: 'username', label: 'Username', sortable: true },
    { key: 'full_name', label: 'Full Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'mobile_no', label: 'Mobile No.', sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'barangay_name', label: 'Barangay', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false },
];

const tableData = computed(() =>
    props.users.map(user => ({
        ...user,
        barangay_name: user.barangay?.barangay_name || 'N/A',
    }))
);

const openCreateModal = () => showCreateModal.value = true;
const closeCreateModal = () => showCreateModal.value = false;

const openEditModal = (userId: number) => {
    editingUserId.value = userId;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingUserId.value = null;
};

const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${userId}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">User Management</h2>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                >
                    <PhPlus :size="20" weight="bold" />
                    Add User
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <DataTable
                        :columns="columns"
                        :data="tableData"
                        :per-page="10"
                    >
                        <template #cell-actions="{ row }">
                            <div class="flex items-center gap-2" @click.stop>
                                <button
                                    @click="openEditModal(row.id)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Edit"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <button
                                    @click="deleteUser(row.id)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <CreateForm
            :show="showCreateModal"
            :barangays="barangays"
            :roles="roles"
            :permissions="permissions"
            @close="closeCreateModal"
        />

        <!-- Edit Modal -->
        <EditForm
            :show="showEditModal"
            :user-id="editingUserId"
            :barangays="barangays"
            :roles="roles"
            :permissions="permissions"
            @close="closeEditModal"
        />
    </AppLayout>
</template>
