<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import TextInput from '@/components/TextInput.vue';
import Boombox from '@/components/Boombox.vue';

interface Props {
    show: boolean;
    barangays: Array<{ id: number; barangay_name: string }>;
    incidents: Array<{ id: number; incident_name: string; severity_level: string }>;
    emergencies: Array<{ id: number; emergency_name: string; severity_level: string }>;
    users: Array<{ id: number; full_name: string }>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const form = useForm({
    user_id: null as number | null,
    barangay_id: null as number | null,
    map_coordinates: '',
    emergency_id: null as number | null,
    incident_id: null as number | null,
    severity_level: 'low',
    casualty_count: 0,
    distance: '',
    attachment: null as File | null,
    responder_name: '',
    responder_contact_no: '',
    estimated_arrival: '',
    datetime_arrived: '',
    plate_no: '',
    status: 'pending',
    remarks: '',
});

const severityLevels = [
    { id: 'low', name: 'Low' },
    { id: 'medium', name: 'Medium' },
    { id: 'high', name: 'High' },
];

const statuses = [
    { id: 'pending', name: 'Pending' },
    { id: 'assigned', name: 'Assigned' },
    { id: 'arrival', name: 'Arrival' },
    { id: 'completed', name: 'Completed' },
    { id: 'cancelled', name: 'Cancelled' },
];

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.attachment = target.files[0];
    }
};

const handleUserChange = (user: any) => {
    form.user_id = user?.id || null;
};

const handleBarangayChange = (barangay: any) => {
    form.barangay_id = barangay?.id || null;
};

const handleIncidentChange = (incident: any) => {
    form.incident_id = incident?.id || null;
};

const handleEmergencyChange = (emergency: any) => {
    form.emergency_id = emergency?.id || null;
};

const handleSeverityChange = (severity: any) => {
    form.severity_level = severity?.id || 'low';
};

const handleStatusChange = (status: any) => {
    form.status = status?.id || 'pending';
};

const submit = () => {
    router.post('/incident-report', form.data(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="closeModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Create Incident Report</h2>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- User Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reporter <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="users"
                            :existing-value="form.user_id"
                            label-field="full_name"
                            placeholder="Select reporter"
                            @change="handleUserChange"
                        />
                        <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.user_id }}
                        </p>
                    </div>

                    <!-- Barangay Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Barangay <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="barangays"
                            :existing-value="form.barangay_id"
                            label-field="barangay_name"
                            placeholder="Select barangay"
                            @change="handleBarangayChange"
                        />
                        <p v-if="form.errors.barangay_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.barangay_id }}
                        </p>
                    </div>

                    <!-- Incident Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Incident Type <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="incidents"
                            :existing-value="form.incident_id"
                            label-field="incident_name"
                            description-field="severity_level"
                            placeholder="Select incident type"
                            @change="handleIncidentChange"
                        />
                        <p v-if="form.errors.incident_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.incident_id }}
                        </p>
                    </div>

                    <!-- Emergency Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Emergency Type <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="emergencies"
                            :existing-value="form.emergency_id"
                            label-field="emergency_name"
                            description-field="severity_level"
                            placeholder="Select emergency type"
                            @change="handleEmergencyChange"
                        />
                        <p v-if="form.errors.emergency_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.emergency_id }}
                        </p>
                    </div>

                    <!-- Severity Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Severity Level <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="severityLevels"
                            :existing-value="form.severity_level"
                            label-field="name"
                            placeholder="Select severity"
                            @change="handleSeverityChange"
                        />
                        <p v-if="form.errors.severity_level" class="mt-1 text-sm text-red-600">
                            {{ form.errors.severity_level }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <Boombox
                            :items="statuses"
                            :existing-value="form.status"
                            label-field="name"
                            placeholder="Select status"
                            @change="handleStatusChange"
                        />
                        <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Casualty Count -->
                    <div>
                        <TextInput
                            v-model="form.casualty_count"
                            type="number"
                            label="Casualty Count"
                            placeholder="Enter casualty count"
                            :min="0"
                            :error="form.errors.casualty_count"
                        />
                    </div>

                    <!-- Distance -->
                    <div>
                        <TextInput
                            v-model="form.distance"
                            type="text"
                            label="Distance"
                            placeholder="Enter distance (e.g., 5.2 km)"
                            :error="form.errors.distance"
                        />
                    </div>

                    <!-- Map Coordinates -->
                    <div class="md:col-span-2">
                        <TextInput
                            v-model="form.map_coordinates"
                            type="text"
                            label="Map Coordinates"
                            placeholder="Enter coordinates (e.g., 14.5995, 120.9842)"
                            :error="form.errors.map_coordinates"
                        />
                    </div>

                    <!-- Responder Name -->
                    <div>
                        <TextInput
                            v-model="form.responder_name"
                            type="text"
                            label="Responder Name"
                            placeholder="Enter responder name"
                            :error="form.errors.responder_name"
                        />
                    </div>

                    <!-- Responder Contact -->
                    <div>
                        <TextInput
                            v-model="form.responder_contact_no"
                            type="tel"
                            label="Responder Contact Number"
                            placeholder="Enter contact number"
                            :error="form.errors.responder_contact_no"
                        />
                    </div>

                    <!-- Plate Number -->
                    <div>
                        <TextInput
                            v-model="form.plate_no"
                            type="text"
                            label="Plate Number"
                            placeholder="Enter plate number"
                            :error="form.errors.plate_no"
                        />
                    </div>

                    <!-- Estimated Arrival -->
                    <div>
                        <TextInput
                            v-model="form.estimated_arrival"
                            type="datetime-local"
                            label="Estimated Arrival"
                            :error="form.errors.estimated_arrival"
                        />
                    </div>

                    <!-- Datetime Arrived -->
                    <div>
                        <TextInput
                            v-model="form.datetime_arrived"
                            type="datetime-local"
                            label="Datetime Arrived"
                            :error="form.errors.datetime_arrived"
                        />
                    </div>

                    <!-- Attachment -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Attachment
                        </label>
                        <input
                            type="file"
                            @change="handleFileChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            accept="image/*,application/pdf"
                        />
                        <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">
                            {{ form.errors.attachment }}
                        </p>
                    </div>

                    <!-- Remarks -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks
                        </label>
                        <textarea
                            v-model="form.remarks"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter any additional remarks"
                        />
                        <p v-if="form.errors.remarks" class="mt-1 text-sm text-red-600">
                            {{ form.errors.remarks }}
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
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
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Report' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
