<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/components/Modal.vue';
import TextInput from '@/components/Textinput.vue';
import Boombox from '@/components/BoomBox.vue';
import Toggle from '@/components/Toggle.vue';
import { PhXCircle } from '@phosphor-icons/vue';

interface Props {
    show: boolean;
    userId: number | null;
    barangays: any[];
    roles: any[];
    permissions: any[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'close': [] }>();

const form = useForm({
    username: '',
    password: '',
    password_confirmation: '',
    full_name: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    mobile_no: '',
    birth_date: '',
    barangay_id: null as number | null,
    role: '',
    permissions: [] as number[],
    is_responder: false,
});

watch(() => props.userId, (newId) => {
    if (newId && props.show) loadUserData(newId);
}, { immediate: true });

const loadUserData = (id: number) => {
    axios.get(`/users/${id}/edit`)
        .then(response => {
            const user = response.data.user;
            form.username = user.username || '';
            form.full_name = user.full_name || '';
            form.first_name = user.first_name || '';
            form.middle_name = user.middle_name || '';
            form.last_name = user.last_name || '';
            form.email = user.email || '';
            form.mobile_no = user.mobile_no || '';
            form.birth_date = user.birth_date || '';
            form.barangay_id = user.barangay_id || null;
            form.role = user.role || '';
            form.permissions = user.permissions?.map((p: any) => p.id) || [];
            form.is_responder = !!user.responder;
        })
        .catch(error => console.error('Error loading user:', error));
};

const handleBarangayChange = (item: any) => form.barangay_id = item?.id || null;
const handleRoleChange = (item: any) => form.role = item?.role_name || '';

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    index > -1 ? form.permissions.splice(index, 1) : form.permissions.push(permissionId);
};

const submit = () => {
    if (!props.userId) return;
    router.put(`/users/${props.userId}`, form.data(), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};

const closeModal = () => {
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="closeModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Edit User #{{ userId }}</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <PhXCircle :size="32" color="#f08000" weight="fill" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <TextInput v-model="form.username" label="Username" placeholder="Enter username" :error="form.errors.username" required />
                    <TextInput v-model="form.password" type="password" label="Password" placeholder="Leave blank to keep current" :error="form.errors.password" />
                    <TextInput v-model="form.password_confirmation" type="password" label="Confirm Password" placeholder="Confirm new password" :error="form.errors.password_confirmation" />
                    <TextInput v-model="form.email" type="email" label="Email" placeholder="Enter email" :error="form.errors.email" />
                    <TextInput v-model="form.first_name" label="First Name" placeholder="Enter first name" :error="form.errors.first_name" />
                    <TextInput v-model="form.middle_name" label="Middle Name" placeholder="Enter middle name" :error="form.errors.middle_name" />
                    <TextInput v-model="form.last_name" label="Last Name" placeholder="Enter last name" :error="form.errors.last_name" />
                    <TextInput v-model="form.full_name" label="Full Name" placeholder="Enter full name" :error="form.errors.full_name" />
                    <TextInput v-model="form.mobile_no" type="tel" label="Mobile Number" placeholder="Enter mobile number" :error="form.errors.mobile_no" />
                    <TextInput v-model="form.birth_date" type="date" label="Birth Date" :error="form.errors.birth_date" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                        <Boombox
                            :items="barangays"
                            :existing-value="form.barangay_id"
                            label-field="barangay_name"
                            placeholder="Select Barangay"
                            @change="handleBarangayChange"
                        />
                        <p v-if="form.errors.barangay_id" class="mt-1 text-sm text-red-600">{{ form.errors.barangay_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <Boombox
                            :items="roles"
                            :existing-value="form.role"
                            label-field="role_name"
                            placeholder="Select Role"
                            @change="handleRoleChange"
                        />
                        <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3 max-h-64 overflow-y-auto">
                            <Toggle
                                v-for="permission in permissions"
                                :key="permission.id"
                                :model-value="form.permissions.includes(permission.id)"
                                @update:model-value="togglePermission(permission.id)"
                                :label="permission.name"
                                :description="permission.description"
                            />
                        </div>
                        <p v-if="form.errors.permissions" class="mt-1 text-sm text-red-600">{{ form.errors.permissions }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <Toggle
                            v-model="form.is_responder"
                            label="Is Responder"
                            description="Enable if this user should be marked as a responder"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Updating...' : 'Update User' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
