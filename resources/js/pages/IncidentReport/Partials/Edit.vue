<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/components/Modal.vue';
import TextInput from '@/components/TextInput.vue';
import Boombox from '@/components/Boombox.vue';
import { PhXCircle, PhMapPin, PhCheckCircle, PhInfo} from '@phosphor-icons/vue';

interface Props {
    show: boolean;
    reportId: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const form = useForm({
    distance: '',
    responder_name: '',
    responder_contact_no: '',
    estimated_arrival: '',
    datetime_arrived: '',
    plate_no: '',
    status: 'pending',
    attachment: null as File | null,
    remarks: '',
});

const incidentInfo = ref({
    user_name: '',
    barangay_name: '',
    incident_name: '',
    emergency_name: '',
    severity_level: '',
    casualty_count: 0,
    map_coordinates: '',
    distance: '',
});

const statuses = [
    { id: 'pending', name: 'Pending' },
    { id: 'assigned', name: 'Assigned' },
    { id: 'arrival', name: 'Arrival' },
    { id: 'completed', name: 'Completed' },
    { id: 'cancelled', name: 'Cancelled' },
];

watch(() => props.reportId, (newId) => {
    if (newId && props.show) {
        loadReportData(newId);
    }
}, { immediate: true });

const loadReportData = (id: number) => {
    axios.get(`/incident-report/${id}/edit`)
        .then(response => {
            const report = response.data.incidentReport;
            incidentInfo.value = {
                user_name: report.user_name,
                barangay_name: report.barangay_name,
                incident_name: report.incident_name,
                emergency_name: report.emergency_name,
                severity_level: report.severity_level,
                casualty_count: report.casualty_count || 0,
                map_coordinates: report.map_coordinates || '',
                distance: report.distance || '',
            };

            form.distance = report.distance || '';
            form.responder_name = report.responder_name || '';
            form.responder_contact_no = report.responder_contact_no || '';
            form.estimated_arrival = report.estimated_arrival || '';
            form.datetime_arrived = report.datetime_arrived || '';
            form.plate_no = report.plate_no || '';
            form.status = report.status;
            form.remarks = report.remarks || '';
        })
        .catch(error => {
            console.error('Error loading report:', error);
        });
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.attachment = target.files[0];
    }
};

const handleStatusChange = (status: any) => {
    form.status = status?.id || 'pending';
};

const submit = () => {
    if (!props.reportId) return;

    router.put(`/incident-report/${props.reportId}`, form.data(), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

const closeModal = () => {
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="3xl" @close="closeModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Edit Incident Report #{{ reportId }}</h2>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <PhXCircle :size="32" color="#f08000" weight="fill" />
                </button>
            </div>
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <PhInfo :size="18" color="#f08000" weight="fill" />
                    Incident Report Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Reporter:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.user_name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Barangay:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.barangay_name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Incident Type:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.incident_name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Emergency Type:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.emergency_name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Severity:</span>
                        <span class="ml-2 capitalize">
                            <span :class="{
                                'text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-xs': incidentInfo.severity_level === 'low',
                                'text-orange-700 bg-orange-100 px-2 py-1 rounded text-xs': incidentInfo.severity_level === 'medium',
                                'text-red-700 bg-red-100 px-2 py-1 rounded text-xs': incidentInfo.severity_level === 'high'
                            }">
                                {{ incidentInfo.severity_level }}
                            </span>
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Casualties:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.casualty_count }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-medium text-gray-600">Location:</span>
                        <span class="ml-2 text-gray-900 text-xs">{{ incidentInfo.map_coordinates || 'N/A' }}</span>
                    </div>
                    <div v-if="incidentInfo.distance">
                        <span class="font-medium text-gray-600">Distance:</span>
                        <span class="ml-2 text-gray-900">{{ incidentInfo.distance }}</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
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

                    <div>
                        <TextInput
                            v-model="form.distance"
                            type="text"
                            label="Distance"
                            placeholder="Enter distance (e.g., 5.2 km)"
                            :error="form.errors.distance"
                        />
                    </div>

                    <div>
                        <TextInput
                            v-model="form.responder_name"
                            type="text"
                            label="Responder Name"
                            placeholder="Enter responder name"
                            :error="form.errors.responder_name"
                        />
                    </div>

                    <div>
                        <TextInput
                            v-model="form.responder_contact_no"
                            type="tel"
                            label="Responder Contact Number"
                            placeholder="Enter contact number"
                            :error="form.errors.responder_contact_no"
                        />
                    </div>

                    <div>
                        <TextInput
                            v-model="form.plate_no"
                            type="text"
                            label="Plate Number"
                            placeholder="Enter plate number"
                            :error="form.errors.plate_no"
                        />
                    </div>

                    <div>
                        <TextInput
                            v-model="form.estimated_arrival"
                            type="datetime-local"
                            label="Estimated Arrival"
                            :error="form.errors.estimated_arrival"
                        />
                    </div>

                    <div>
                        <TextInput
                            v-model="form.datetime_arrived"
                            type="datetime-local"
                            label="Datetime Arrived"
                            :error="form.errors.datetime_arrived"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Attachment
                        </label>
                        <input
                            type="file"
                            @change="handleFileChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            accept="image/*,application/pdf"
                        />
                        <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">
                            {{ form.errors.attachment }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks
                        </label>
                        <textarea
                            v-model="form.remarks"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Enter any additional remarks"
                        />
                        <p v-if="form.errors.remarks" class="mt-1 text-sm text-red-600">
                            {{ form.errors.remarks }}
                        </p>
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
                        {{ form.processing ? 'Updating...' : 'Update Report' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
