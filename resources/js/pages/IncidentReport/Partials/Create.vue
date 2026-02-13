<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import TextInput from '@/components/TextInput.vue';
import Boombox from '@/components/Boombox.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { PhXCircle, PhMapPin, PhCheckCircle} from '@phosphor-icons/vue';

// Fix marker icon paths
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

interface Props {
    show: boolean;
    barangays: Array<{ id: number; barangay_name: string }>;
    incidents: Array<{ id: number; incident_name: string; severity_level: string }>;
    emergencies: Array<{ id: number; emergency_name: string; severity_level: string }>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'close': [];
}>();

const form = useForm({
    barangay_id: null as number | null,
    map_coordinates: '',
    emergency_id: null as number | null,
    incident_id: null as number | null,
    severity_level: 'low',
    casualty_count: 0,
    distance: '',
    attachment: null as File | null,
    remarks: '',
});

const severityLevels = [
    { id: 'low', name: 'Low' },
    { id: 'medium', name: 'Medium' },
    { id: 'high', name: 'High' },
];

// Map state
const showMap = ref(false);
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
const markerPosition = ref<[number, number]>([14.5995, 120.9842]); // Default Manila
const isLocating = ref(false);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.attachment = target.files[0];
    }
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

// Initialize map
const initMap = () => {
    nextTick(() => {
        if (!mapContainer.value) return;

        // Parse existing coordinates if available
        let initialCenter: [number, number] = [14.5995, 120.9842];
        if (form.map_coordinates) {
            const coords = form.map_coordinates.split(',').map(s => parseFloat(s.trim()));
            if (coords.length === 2 && !isNaN(coords[0]) && !isNaN(coords[1])) {
                initialCenter = [coords[0], coords[1]];
                markerPosition.value = initialCenter;
            }
        }

        // Create map
        map = L.map(mapContainer.value).setView(initialCenter, 13);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add marker
        marker = L.marker(markerPosition.value, {
            draggable: true
        }).addTo(map);

        // Handle map click
        map.on('click', (e: L.LeafletMouseEvent) => {
            markerPosition.value = [e.latlng.lat, e.latlng.lng];
            if (marker) {
                marker.setLatLng(e.latlng);
            }
        });

        // Handle marker drag
        marker.on('dragend', () => {
            if (marker) {
                const position = marker.getLatLng();
                markerPosition.value = [position.lat, position.lng];
            }
        });
    });
};

// Open map picker
const openMapPicker = () => {
    showMap.value = true;
    nextTick(() => {
        initMap();
    });
};

// Get current location
const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            markerPosition.value = [lat, lng];

            if (map && marker) {
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
            }

            isLocating.value = false;
        },
        (error) => {
            console.error('Error getting location:', error);
            isLocating.value = false;
            alert('Unable to get your location. Please click on the map to select a location.');
        }
    );
};

// Confirm location selection
const confirmLocation = () => {
    const [lat, lng] = markerPosition.value;
    form.map_coordinates = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    closeMapModal();
};

// Close map modal
const closeMapModal = () => {
    showMap.value = false;
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
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
                    <PhXCircle :size="32" color="#f08000" weight="fill" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

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

                    <!-- <div>
                        <TextInput
                            v-model="form.distance"
                            type="text"
                            label="Distance"
                            placeholder=""
                            :error="form.errors.distance"
                            readonly
                        />
                    </div> -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Map Coordinates <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <TextInput
                                v-model="form.map_coordinates"
                                type="text"
                                placeholder="Click 'Pick on Map' to select location"
                                :error="form.errors.map_coordinates"
                                class="flex-1"
                                readonly
                            />
                            <button
                                type="button"
                                @click="openMapPicker"
                                class="px-4 py-2 text-sm font-medium text-white bg-gray-700 border border-transparent rounded-md hover:bg-gray-900 whitespace-nowrap flex items-center gap-2"
                            >
                                <PhMapPin :size="25" color="#f08000" weight="fill" />
                                Pick on Map
                            </button>
                        </div>
                        <p v-if="form.errors.map_coordinates" class="mt-1 text-sm text-red-600">
                            {{ form.errors.map_coordinates }}
                        </p>
                        <p v-else-if="form.map_coordinates" class="mt-1 text-sm text-orange-600 flex items-center gap-1">
                            <PhCheckCircle :size="15" color="#f08000" weight="fill" />
                            Location selected
                        </p>
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
                        {{ form.processing ? 'Creating...' : 'Create Report' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>

    <Modal :show="showMap" max-width="4xl" @close="closeMapModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Location on Map</h3>
                <button
                    @click="closeMapModal"
                    class="text-gray-400 hover:text-gray-500"
                >
                    <PhXCircle :size="32" color="#f08000" weight="fill" />
                </button>
            </div>

            <div class="mb-3 flex items-center justify-between gap-x-2">
                <p class="text-sm text-gray-600">
                    Click anywhere on the map or drag the marker to select the incident location
                </p>
                <button
                    type="button"
                    @click="getCurrentLocation"
                    :disabled="isLocating"
                    class="px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <PhMapPin :size="15" color="#f08000" weight="fill" />
                    {{ isLocating ? 'Locating...' : 'Use My Location' }}
                </button>
            </div>

            <div
                ref="mapContainer"
                class="border border-gray-300 rounded-lg"
                style="height: 500px; width: 100%;"
            ></div>

            <div class="mt-4 p-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-700">
                    <strong>Selected Coordinates:</strong>
                    {{ markerPosition[0].toFixed(6) }}, {{ markerPosition[1].toFixed(6) }}
                </p>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button
                    type="button"
                    @click="closeMapModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="confirmLocation"
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700"
                >
                    Confirm Location
                </button>
            </div>
        </div>
    </Modal>
</template>
