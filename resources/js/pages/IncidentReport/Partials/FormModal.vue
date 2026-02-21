<script setup lang="ts">
import { ref, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PhX, PhPlus, PhFloppyDisk, PhMapPin, PhXCircle, PhCheckCircle, PhMagnifyingGlass } from '@phosphor-icons/vue'
import CustomInput from '@/components/CustomInput.vue'
import Boombox from '@/components/BoomBox.vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
})

const props = defineProps<{
    mode: 'create' | 'edit'
    record: any | null
    barangays: Array<{ id: number; barangay_name: string }>
    incidents: Array<{ id: number; incident_name: string; severity_level: string }>
    emergencies: Array<{ id: number; emergency_name: string; severity_level: string }>
    users: Array<{ id: number; full_name: string }>
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
    barangay_id:     props.record?.barangay_id     ?? null,
    map_coordinates: props.record?.map_coordinates ?? '',
    emergency_id:    props.record?.emergency_id    ?? null,
    incident_id:     props.record?.incident_id     ?? null,
    severity_level:  props.record?.severity_level  ?? 'low',
    casualty_count:  props.record?.casualty_count  ?? 0,
    distance:        props.record?.distance        ?? '',
    remarks:         props.record?.remarks         ?? '',
    attachment:      null as File | null,
    responder_name:       props.record?.responder_name       ?? '',
    responder_contact_no: props.record?.responder_contact_no ?? '',
    estimated_arrival:    props.record?.estimated_arrival    ?? '',
    datetime_arrived:     props.record?.datetime_arrived     ?? '',
    plate_no:             props.record?.plate_no             ?? '',
    status:               props.record?.status               ?? 'pending',
})

const severityLevels = [
    { id: 'low',    name: 'Low' },
    { id: 'medium', name: 'Medium' },
    { id: 'high',   name: 'High' },
]

const statusOptions = [
    { value: 'pending',   label: 'Pending' },
    { value: 'assigned',  label: 'Assigned' },
    { value: 'arrival',   label: 'Arrival' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
]

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files?.[0]) form.attachment = target.files[0]
}

// ── Map ─────────────────────────────────────────────────────────────────────
const showMap        = ref(false)
const mapContainer   = ref<HTMLElement | null>(null)
const isLocating     = ref(false)
const isGeocoding    = ref(false)
const markerPos      = ref<[number, number]>([14.5995, 120.9842])
const resolvedAddress = ref('')
const searchQuery    = ref('')
const searchResults  = ref<any[]>([])
const isSearching    = ref(false)

let map: L.Map | null = null
let mapMarker: L.Marker | null = null

// ── Reverse geocode using Nominatim ─────────────────────────────────────────
const reverseGeocode = async (lat: number, lng: number) => {
    isGeocoding.value = true
    resolvedAddress.value = ''
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
            { headers: { 'Accept-Language': 'en' } }
        )
        const data = await res.json()
        resolvedAddress.value = data.display_name ?? 'Address not found'
    } catch {
        resolvedAddress.value = 'Unable to resolve address'
    } finally {
        isGeocoding.value = false
    }
}

// ── Forward geocode / search ─────────────────────────────────────────────────
const searchAddress = async () => {
    if (!searchQuery.value.trim()) return
    isSearching.value = true
    searchResults.value = []
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchQuery.value)}&format=json&limit=5`,
            { headers: { 'Accept-Language': 'en' } }
        )
        searchResults.value = await res.json()
    } catch {
        searchResults.value = []
    } finally {
        isSearching.value = false
    }
}

const selectSearchResult = (result: any) => {
    const lat = parseFloat(result.lat)
    const lng = parseFloat(result.lon)
    markerPos.value = [lat, lng]
    map?.setView([lat, lng], 16)
    mapMarker?.setLatLng([lat, lng])
    resolvedAddress.value = result.display_name
    searchResults.value = []
    searchQuery.value = ''
}

// ── Map init ─────────────────────────────────────────────────────────────────
const updateMarker = (lat: number, lng: number) => {
    markerPos.value = [lat, lng]
    reverseGeocode(lat, lng)
}

const initMap = () => {
    nextTick(() => {
        if (!mapContainer.value) return

        let center: [number, number] = [14.5995, 120.9842]
        if (form.map_coordinates) {
            const parts = form.map_coordinates.split(',').map(s => parseFloat(s.trim()))
            if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                center = [parts[0], parts[1]]
                markerPos.value = center
            }
        }

        map = L.map(mapContainer.value).setView(center, 13)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors',
        }).addTo(map)

        mapMarker = L.marker(markerPos.value, { draggable: true }).addTo(map)

        reverseGeocode(markerPos.value[0], markerPos.value[1])

        map.on('click', (e: L.LeafletMouseEvent) => {
            mapMarker?.setLatLng(e.latlng)
            updateMarker(e.latlng.lat, e.latlng.lng)
        })

        mapMarker.on('dragend', () => {
            if (mapMarker) {
                const p = mapMarker.getLatLng()
                updateMarker(p.lat, p.lng)
            }
        })
    })
}

const openMapPicker = () => {
    showMap.value = true
    nextTick(() => initMap())
}

const closeMapModal = () => {
    showMap.value = false
    searchQuery.value = ''
    searchResults.value = []
    resolvedAddress.value = ''
    map?.remove()
    map = null
    mapMarker = null
}

const getCurrentLocation = () => {
    if (!navigator.geolocation) { alert('Geolocation not supported'); return }
    isLocating.value = true
    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            const lat = coords.latitude
            const lng = coords.longitude
            markerPos.value = [lat, lng]
            map?.setView([lat, lng], 16)
            mapMarker?.setLatLng([lat, lng])
            reverseGeocode(lat, lng)
            isLocating.value = false
        },
        () => {
            isLocating.value = false
            alert('Unable to get location. Click the map or search an address instead.')
        }
    )
}

const confirmLocation = () => {
    const [lat, lng] = markerPos.value
    form.map_coordinates = `${lat.toFixed(6)}, ${lng.toFixed(6)}`
    closeMapModal()
}

// ── Submit ───────────────────────────────────────────────────────────────────
const submitForm = () => {
    const options = { onSuccess: () => emit('success') }
    if (props.mode === 'create') {
        form.post('/incident-report', options)
    } else {
        form.put(`/incident-report/${props.record.id}`, options)
    }
}

const closeModal = () => {
    form.reset()
    form.clearErrors()
    emit('close')
}
</script>

<template>
    <div class="w-full lg:w-[80vw] xl:w-[90vw]">
        <!-- Header -->
        <div class="flex items-center justify-between w-full px-8 py-1 bg-form-header border-b border-black dark:border-gray-500 dark:bg-gray-800">
            <h1 class="text-base lg:text-2xl font-extrabold dark:text-gray-200">
                {{ mode === 'create' ? 'Create Incident Report' : 'Edit Incident Report' }}
            </h1>
            <button @click="closeModal" class="p-3 text-white rounded-full bg-red-500 hover:bg-red-600">
                <PhX :size="16" />
            </button>
        </div>

        <form @submit.prevent="submitForm" class="p-4 bg-form-body dark:bg-gray-800 max-h-[85vh] overflow-y-auto">

            <!-- ── CREATE: Incident Details ─────────────────────────────── -->
            <template v-if="mode === 'create'">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Incident Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Barangay <span class="text-red-500">*</span></label>
                        <Boombox :items="barangays" :existing-value="form.barangay_id" label-field="barangay_name"
                            placeholder="Select barangay" @change="(v: any) => form.barangay_id = v?.id ?? null" />
                        <p v-if="form.errors.barangay_id" class="text-xs text-red-500 mt-1">{{ form.errors.barangay_id }}</p>
                    </div>
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Incident Type <span class="text-red-500">*</span></label>
                        <Boombox :items="incidents" :existing-value="form.incident_id" label-field="incident_name"
                            placeholder="Select incident type" @change="(v: any) => form.incident_id = v?.id ?? null" />
                        <p v-if="form.errors.incident_id" class="text-xs text-red-500 mt-1">{{ form.errors.incident_id }}</p>
                    </div>
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Emergency Type <span class="text-red-500">*</span></label>
                        <Boombox :items="emergencies" :existing-value="form.emergency_id" label-field="emergency_name"
                            placeholder="Select emergency type" @change="(v: any) => form.emergency_id = v?.id ?? null" />
                        <p v-if="form.errors.emergency_id" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_id }}</p>
                    </div>
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Severity Level <span class="text-red-500">*</span></label>
                        <Boombox :items="severityLevels" :existing-value="form.severity_level" label-field="name"
                            placeholder="Select severity" @change="(v: any) => form.severity_level = v?.id ?? 'low'" />
                        <p v-if="form.errors.severity_level" class="text-xs text-red-500 mt-1">{{ form.errors.severity_level }}</p>
                    </div>
                    <div>
                        <CustomInput name="Casualty Count" type="number" v-model="form.casualty_count" />
                        <p v-if="form.errors.casualty_count" class="text-xs text-red-500 mt-1">{{ form.errors.casualty_count }}</p>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Map Coordinates <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <CustomInput name="" v-model="form.map_coordinates" class="flex-1" readonly />
                            <button type="button" @click="openMapPicker"
                                class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-md hover:bg-gray-900 flex items-center gap-2 whitespace-nowrap">
                                <PhMapPin :size="18" />
                                Pick on Map
                            </button>
                        </div>
                        <p v-if="form.errors.map_coordinates" class="text-xs text-red-500 mt-1">{{ form.errors.map_coordinates }}</p>
                        <p v-else-if="form.map_coordinates" class="mt-1 text-xs text-orange-600 flex items-center gap-1">
                            <PhCheckCircle :size="14" /> Location selected
                        </p>
                    </div>
                </div>
            </template>

            <!-- ── EDIT: Read-only Report Details ─────────────────────────── -->
            <template v-if="mode === 'edit'">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Report Details</h2>
                <div class="grid grid-cols-1 gap-4 p-4 mb-4 rounded-lg border border-blue-200 bg-blue-50 dark:bg-gray-700/50 dark:border-blue-700 lg:grid-cols-3">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Barangay</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.barangay?.barangay_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Incident Type</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.incident?.incident_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Emergency Type</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.emergency?.emergency_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Severity Level</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="{
                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': record?.severity_level === 'low',
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300': record?.severity_level === 'medium',
                                'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': record?.severity_level === 'high',
                            }">
                            {{ record?.severity_level ?? '—' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Casualty Count</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.casualty_count ?? '0' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Reported By</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.user?.full_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Date Reported</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ record?.created_at ? new Date(record.created_at).toLocaleString() : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Map Coordinates</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 break-all">{{ record?.map_coordinates ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Current Status</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="{
                                'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-200': record?.status === 'pending',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300': record?.status === 'assigned',
                                'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300': record?.status === 'arrival',
                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': record?.status === 'completed',
                                'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': record?.status === 'cancelled',
                            }">
                            {{ record?.status ?? '—' }}
                        </span>
                    </div>

                    <div v-if="record?.remarks" class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Remarks</p>
                        <p class="text-sm text-gray-800 dark:text-gray-100">{{ record.remarks }}</p>
                    </div>

                    <div v-if="record?.attachment">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400 mb-0.5">Attachment</p>
                        <a :href="`/storage/${record.attachment}`" target="_blank"
                            class="text-sm text-blue-600 hover:underline dark:text-blue-400 flex items-center gap-1">
                            View Attachment
                        </a>
                    </div>
                </div>

                <!-- ── Responder Details (editable) ────────────────────── -->
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Responder Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                    <div><CustomInput name="Responder Name"       v-model="form.responder_name" /></div>
                    <div><CustomInput name="Responder Contact No" v-model="form.responder_contact_no" /></div>
                    <div><CustomInput name="Plate No"             v-model="form.plate_no" /></div>
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Status</label>
                        <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:text-white">
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-red-500 mt-1">{{ form.errors.status }}</p>
                    </div>
                    <div><CustomInput name="Estimated Arrival" type="datetime-local" v-model="form.estimated_arrival" /></div>
                    <div><CustomInput name="Datetime Arrived"  type="datetime-local" v-model="form.datetime_arrived" /></div>
                </div>
            </template>

            <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Notes & Attachment</h2>
            <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                <div><CustomInput name="Remarks" v-model="form.remarks" /></div>
                <div>
                    <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Attachment</label>
                    <input type="file" @change="handleFileChange" accept="image/*,application/pdf"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" />
                    <p v-if="form.errors.attachment" class="text-xs text-red-500 mt-1">{{ form.errors.attachment }}</p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-2 mt-4">
                <ButtonCode type="submit" :icon="mode === 'edit' ? PhFloppyDisk : PhPlus"
                    color="bg-orange-500 hover:bg-orange-600" :text="mode === 'edit' ? 'Update' : 'Save'" />
                <ButtonCode v-if="mode === 'edit'" type="button" color="bg-red-500 hover:bg-red-600"
                    text="Cancel" @click="closeModal" />
            </div>
        </form>
    </div>

    <!-- Map Picker Modal -->
    <Modal :show="showMap" max-width="4xl" @close="closeMapModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Location on Map</h3>
                <button @click="closeMapModal" class="text-gray-400 hover:text-gray-500">
                    <PhXCircle :size="32" color="#f08000" weight="fill" />
                </button>
            </div>

            <!-- Address Search Bar -->
            <div class="mb-3 relative">
                <div class="flex gap-2">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search address or place name..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        @keyup.enter="searchAddress"
                    />
                    <button
                        type="button"
                        @click="searchAddress"
                        :disabled="isSearching"
                        class="px-3 py-2 text-sm font-medium text-white bg-gray-700 rounded-md hover:bg-gray-900 disabled:opacity-50 flex items-center gap-2"
                    >
                        <PhMagnifyingGlass :size="15" />
                        {{ isSearching ? 'Searching...' : 'Search' }}
                    </button>
                </div>
                <!-- Search Results Dropdown -->
                <ul v-if="searchResults.length" class="absolute z-[9999] w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto">
                    <li
                        v-for="result in searchResults"
                        :key="result.place_id"
                        @click="selectSearchResult(result)"
                        class="px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 cursor-pointer border-b last:border-0"
                    >
                        {{ result.display_name }}
                    </li>
                </ul>
            </div>

            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-sm text-gray-600">Click the map, drag the marker, or search an address above.</p>
                <button
                    type="button"
                    @click="getCurrentLocation"
                    :disabled="isLocating"
                    class="px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 disabled:opacity-50 flex items-center gap-2 whitespace-nowrap"
                >
                    <PhMapPin :size="15" />
                    {{ isLocating ? 'Locating...' : 'Use My Location' }}
                </button>
            </div>

            <div ref="mapContainer" class="border border-gray-300 rounded-lg" style="height: 450px; width: 100%;" />

            <!-- Resolved Address -->
            <div class="mt-3 p-3 bg-gray-50 rounded-md space-y-1">
                <p class="text-sm text-gray-700">
                    <strong>Coordinates:</strong> {{ markerPos[0].toFixed(6) }}, {{ markerPos[1].toFixed(6) }}
                </p>
                <p class="text-sm text-gray-700">
                    <strong>Address:</strong>
                    <span v-if="isGeocoding" class="text-gray-400 italic"> Resolving address...</span>
                    <span v-else class="text-gray-600"> {{ resolvedAddress || '—' }}</span>
                </p>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" @click="closeMapModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" @click="confirmLocation"
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">
                    Confirm Location
                </button>
            </div>
        </div>
    </Modal>
</template>
