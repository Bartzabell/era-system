<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { PhX, PhPlus, PhFloppyDisk } from '@phosphor-icons/vue'
import CustomInput from '@/components/CustomInput.vue'
import Boombox from '@/components/BoomBox.vue'
import ButtonCode from '@/components/ButtonCode.vue'

const props = defineProps<{
    mode: 'create' | 'edit'
    record: any | null
    barangays: Array<{ id: number; barangay_name: string }>
    roles: Array<{ id: number; role_name: string }>
    permissions: Array<{ id: number; name: string; slug: string }>
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
    username:         props.record?.username      ?? '',
    password:         '',
    password_confirmation: '',
    first_name:       props.record?.first_name    ?? '',
    middle_name:      props.record?.middle_name   ?? '',
    last_name:        props.record?.last_name     ?? '',
    email:            props.record?.email         ?? '',
    mobile_no:        props.record?.mobile_no     ?? '',
    birth_date:       props.record?.birth_date    ?? '',
    barangay_id:      props.record?.barangay_id   ?? null,
    role:             props.record?.role          ?? '',
    permissions:      props.record?.permissions?.map((p: any) => p.id) ?? [] as number[],
    is_responder:     props.record?.responder != null,
})

const roleOptions = props.roles.map(r => ({ id: r.role_name, name: r.role_name }))

const togglePermission = (permId: number) => {
    const idx = form.permissions.indexOf(permId)
    if (idx === -1) {
        form.permissions.push(permId)
    } else {
        form.permissions.splice(idx, 1)
    }
}

const hasPermission = (permId: number) => form.permissions.includes(permId)

const submitForm = () => {
    const options = { onSuccess: () => emit('success') }
    if (props.mode === 'create') {
        form.post('/users', options)
    } else {
        form.put(`/users/${props.record.id}`, options)
    }
}

const closeModal = () => {
    form.reset()
    form.clearErrors()
    emit('close')
}
</script>

<template>
    <div class="w-full lg:w-[80vw] xl:w-[60vw]">
        <!-- Header -->
        <div class="flex items-center justify-between w-full px-8 py-1 bg-form-header border-b border-black dark:border-gray-500 dark:bg-gray-800">
            <h1 class="text-base lg:text-2xl font-extrabold dark:text-gray-200">
                {{ mode === 'create' ? 'Add New User' : 'Edit User' }}
            </h1>
            <button @click="closeModal" class="p-3 text-white rounded-full bg-red-500 hover:bg-red-600">
                <PhX :size="16" />
            </button>
        </div>

        <form @submit.prevent="submitForm" class="p-4 bg-form-body dark:bg-gray-800 max-h-[85vh]">

            <!-- Account Info -->
            <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Account Info</h2>
            <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                <div>
                    <CustomInput name="Username" v-model="form.username" />
                    <p v-if="form.errors.username" class="text-xs text-red-500 mt-1">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <Boombox
                        :items="roleOptions"
                        :existing-value="form.role"
                        label-field="name"
                        placeholder="Select role"
                        @change="(v: any) => form.role = v?.id ?? ''"
                    />
                    <p v-if="form.errors.role" class="text-xs text-red-500 mt-1">{{ form.errors.role }}</p>
                </div>
                <div>
                    <CustomInput
                        name="Password"
                        type="password"
                        v-model="form.password"
                        :placeholder="mode === 'edit' ? 'Leave blank to keep current' : ''"
                    />
                    <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                    <CustomInput name="Confirm Password" type="password" v-model="form.password_confirmation" />
                </div>
            </div>

            <!-- Personal Info -->
            <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Personal Info</h2>
            <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                <div>
                    <CustomInput name="First Name" v-model="form.first_name" />
                    <p v-if="form.errors.first_name" class="text-xs text-red-500 mt-1">{{ form.errors.first_name }}</p>
                </div>
                <div>
                    <CustomInput name="Middle Name" v-model="form.middle_name" />
                </div>
                <div>
                    <CustomInput name="Last Name" v-model="form.last_name" />
                    <p v-if="form.errors.last_name" class="text-xs text-red-500 mt-1">{{ form.errors.last_name }}</p>
                </div>
                <div>
                    <CustomInput name="Email" type="email" v-model="form.email" />
                    <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                </div>
                <div>
                    <CustomInput name="Mobile No." v-model="form.mobile_no" />
                </div>
                <div>
                    <CustomInput name="Birth Date" type="date" v-model="form.birth_date" />
                </div>
                <div class="lg:col-span-2">
                    <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Barangay</label>
                    <Boombox
                        :items="barangays"
                        :existing-value="form.barangay_id"
                        label-field="barangay_name"
                        placeholder="Select barangay"
                        @change="(v: any) => form.barangay_id = v?.id ?? null"
                    />
                </div>
            </div>

            <!-- Permissions -->
            <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Permissions</h2>
            <div class="p-3 mb-4 border border-dashed border-gray-400">
                <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                    <label
                        v-for="perm in permissions"
                        :key="perm.id"
                        class="flex items-center gap-2 cursor-pointer select-none p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <input
                            type="checkbox"
                            :checked="hasPermission(perm.id)"
                            @change="togglePermission(perm.id)"
                            class="w-4 h-4 accent-orange-500"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ perm.name }}</span>
                    </label>
                </div>
                <p v-if="form.errors.permissions" class="text-xs text-red-500 mt-1">{{ form.errors.permissions }}</p>
            </div>

            <!-- Responder Toggle -->
            <div class="p-3 mb-4 border border-dashed border-gray-400">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.is_responder"
                        class="w-4 h-4 accent-orange-500"
                    />
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Mark as Responder</span>
                        <p class="text-xs text-gray-400">Creates a responder record for this user</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-center gap-2 mt-4">
                <ButtonCode
                    type="submit"
                    :icon="mode === 'edit' ? PhFloppyDisk : PhPlus"
                    color="bg-orange-500 hover:bg-orange-600"
                    :text="mode === 'edit' ? 'Update User' : 'Create User'"
                    :disabled="form.processing"
                />
                <ButtonCode
                    type="button"
                    color="bg-red-500 hover:bg-red-600"
                    text="Cancel"
                    @click="closeModal"
                />
            </div>
        </form>
    </div>
</template>
