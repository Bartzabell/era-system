<script setup lang="ts">
import { ref, computed, nextTick, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PhX, PhPlus, PhFloppyDisk, PhMapPin, PhXCircle, PhCheckCircle, PhMagnifyingGlass } from '@phosphor-icons/vue'
import CustomInput from '@/components/CustomInput.vue'
import Boombox from '@/components/BoomBox.vue'
import Modal from '@/components/Modal.vue'
import ButtonCode from '@/components/ButtonCode.vue'
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
    incidents: Array<{ id: number; incident_name: string; emergency_id: number; base_severity: number; base_time: number; base_resources: number; base_secondary: number }>
    emergencies: Array<{ id: number; emergency_name: string }>
    users: Array<{ id: number; full_name: string }>
    hasFullAccess: boolean
    currentUserId: number
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
    _method:              props.mode === 'edit' ? 'PUT' : undefined,
    user_id:              props.hasFullAccess ? (props.record?.user_id ?? props.currentUserId) : props.currentUserId,
    barangay_id:          props.record?.barangay_id     ?? null,
    map_coordinates:      props.record?.map_coordinates ?? '',
    emergency_id:         props.record?.emergency_id    ?? null,
    incident_id:          props.record?.incident_id     ?? null,
    casualty_count:       props.record?.casualty_count  ?? 0,
    distance:             props.record?.distance        ?? null,
    remarks:              props.record?.remarks         ?? '',
    attachment:           null as File | null,
    responder_name:       props.record?.responder_name       ?? '',
    responder_contact_no: props.record?.responder_contact_no ?? '',
    estimated_arrival:    props.record?.estimated_arrival    ?? '',
    datetime_arrived:     props.record?.datetime_arrived     ?? '',
    plate_no:             props.record?.plate_no             ?? '',
    status:               props.record?.status               ?? 'waiting',
    priority_score:       props.record?.priority_score       ?? null,
    priority_level:       props.record?.priority_level       ?? null,
    priority_label:       props.record?.priority_label       ?? null,
})

// ── Emergency → Incident filtering ──────────────────────────────────────────
const filteredIncidents = computed(() =>
    form.emergency_id
        ? props.incidents.filter(i => i.emergency_id === form.emergency_id)
        : props.incidents
)

watch(() => form.emergency_id, (newEmergencyId) => {
    if (!newEmergencyId) return
    const stillValid = props.incidents.find(
        i => i.id === form.incident_id && i.emergency_id === newEmergencyId
    )
    if (!stillValid) form.incident_id = null
    recomputePriority()
})

watch(() => form.incident_id, () => recomputePriority())
watch(() => form.distance,    () => recomputePriority())

// ── Priority Score Calculator ────────────────────────────────────────────────
const WEIGHTS = {
    severity:  0.35,
    time:      0.25,
    distance:  0.20,
    resources: 0.10,
    secondary: 0.10,
}

const normalizeDistance = (km: number | null): number => {
    if (km === null || km === undefined) return 5
    const clamped = Math.min(Math.max(km, 0), 50)
    return parseFloat(((1 - clamped / 50) * 10).toFixed(2))
}

const mapToLevel = (score: number): { level: string; label: string } => {
    if (score >= 8.5) return { level: 'P1', label: 'Critical' }
    if (score >= 6.5) return { level: 'P2', label: 'High' }
    if (score >= 4.5) return { level: 'P3', label: 'Moderate' }
    if (score >= 2.5) return { level: 'P4', label: 'Low' }
    return            { level: 'P5', label: 'Informational' }
}

const recomputePriority = () => {
    const incident = props.incidents.find(i => i.id === form.incident_id)
    if (!incident) {
        form.priority_score = null
        form.priority_level = null
        form.priority_label = null
        return
    }

    const distanceScore = normalizeDistance(form.distance)
    const score = parseFloat((
        (incident.base_severity  * WEIGHTS.severity)  +
        (incident.base_time      * WEIGHTS.time)       +
        (distanceScore           * WEIGHTS.distance)   +
        (incident.base_resources * WEIGHTS.resources)  +
        (incident.base_secondary * WEIGHTS.secondary)
    ).toFixed(2))

    const { level, label } = mapToLevel(score)
    form.priority_score = score
    form.priority_level = level
    form.priority_label = label
}

const priorityDisplay = computed(() => {
    if (!form.priority_score) return null
    return {
        score: form.priority_score,
        level: form.priority_level,
        label: form.priority_label,
    }
})

const priorityBadgeClass = computed(() => {
    const map: Record<string, string> = {
        P1: 'bg-red-100 text-red-700 border-red-300',
        P2: 'bg-orange-100 text-orange-700 border-orange-300',
        P3: 'bg-yellow-100 text-yellow-700 border-yellow-300',
        P4: 'bg-blue-100 text-blue-700 border-blue-300',
        P5: 'bg-gray-100 text-gray-600 border-gray-300',
    }
    return map[form.priority_level ?? ''] ?? 'bg-gray-100 text-gray-600 border-gray-300'
})

// ── Severity label derived from incident_reports.severity_level (read-only display) ──
const severityBadgeClass = (level: string) => {
    const map: Record<string, string> = {
        low:    'bg-green-100 text-green-700',
        medium: 'bg-yellow-100 text-yellow-700',
        high:   'bg-red-100 text-red-700',
    }
    return `inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize ${map[level] ?? 'bg-gray-100 text-gray-600'}`
}

const statusOptions = [
    { value: 'waiting',   label: 'Waiting' },
    { value: 'assigned',  label: 'Assigned' },
    { value: 'arriving',  label: 'Arriving' },
    { value: 'resolved',  label: 'Resolved' },
    { value: 'cancelled', label: 'Cancelled' },
]

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files?.[0]) form.attachment = target.files[0]
}

// ── Main Map Picker ──────────────────────────────────────────────────────────
const showMap         = ref(false)
const mapContainer    = ref<HTMLElement | null>(null)
const isLocating      = ref(false)
const isGeocoding     = ref(false)
const markerPos       = ref<[number, number]>([14.5995, 120.9842])
const resolvedAddress = ref('')
const searchQuery     = ref('')
const searchResults   = ref<any[]>([])
const isSearching     = ref(false)

let map: L.Map | null = null
let mapMarker: L.Marker | null = null

// ── Mini-map (edit mode read-only) ───────────────────────────────────────────
const miniMapContainer = ref<HTMLElement | null>(null)
const miniMapAddress   = ref('')
const isMiniGeocoding  = ref(false)
let miniMap: L.Map | null = null

const parseCoordsFromString = (str: string): [number, number] | null => {
    if (!str) return null
    const parts = str.split(',').map(s => parseFloat(s.trim()))
    if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
        return [parts[0], parts[1]]
    }
    return null
}

const reverseGeocodeAddress = async (lat: number, lng: number): Promise<string> => {
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
            { headers: { 'Accept-Language': 'en' } }
        )
        const data = await res.json()
        const p = data.address ?? {}
        const nearby = p.amenity || p.leisure || p.tourism || p.road || p.neighbourhood || p.suburb || p.village || p.town || p.city
        if (nearby) {
            const city = p.city || p.town || p.municipality || ''
            return nearby + (city ? `, ${city}` : '')
        }
        return data.display_name ?? 'Unknown location'
    } catch {
        return 'Unable to resolve address'
    }
}

const initMiniMap = async () => {
    if (!miniMapContainer.value) return
    const coords = parseCoordsFromString(props.record?.map_coordinates ?? '')
    if (!coords) return

    isMiniGeocoding.value = true
    miniMapAddress.value  = ''
    await nextTick()

    if (miniMap) { miniMap.remove(); miniMap = null }

    miniMap = L.map(miniMapContainer.value, {
        zoomControl: false, dragging: false, scrollWheelZoom: false,
        doubleClickZoom: false, touchZoom: false, keyboard: false, attributionControl: false,
    }).setView(coords, 16)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(miniMap)
    L.marker(coords).addTo(miniMap)

    miniMapAddress.value  = await reverseGeocodeAddress(coords[0], coords[1])
    isMiniGeocoding.value = false
}

onMounted(() => {
    if (props.mode === 'edit') nextTick(() => initMiniMap())
    if (props.mode === 'create') recomputePriority()
})

// ── Main Map Picker logic ────────────────────────────────────────────────────
const reverseGeocode = async (lat: number, lng: number) => {
    isGeocoding.value     = true
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

const searchAddress = async () => {
    if (!searchQuery.value.trim()) return
    isSearching.value   = true
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
    markerPos.value       = [lat, lng]
    resolvedAddress.value = result.display_name
    searchResults.value   = []
    searchQuery.value     = ''
    map?.setView([lat, lng], 16)
    mapMarker?.setLatLng([lat, lng])
}

const updateMarker = (lat: number, lng: number) => {
    markerPos.value = [lat, lng]
    reverseGeocode(lat, lng)
}

const initMap = () => {
    nextTick(() => {
        if (!mapContainer.value) return

        let center: [number, number] = [14.5995, 120.9842]
        if (form.map_coordinates) {
            const parsed = parseCoordsFromString(form.map_coordinates)
            if (parsed) { center = parsed; markerPos.value = center }
        }

        map = L.map(mapContainer.value).setView(center, 13)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '© OpenStreetMap contributors',
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
    showMap.value         = false
    searchQuery.value     = ''
    searchResults.value   = []
    resolvedAddress.value = ''
    map?.remove()
    map = null
    mapMarker = null
}

const getCurrentLocation = async () => {
    isLocating.value = true
    if (navigator.geolocation) {
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
            async () => { await locateByIp() },
            { timeout: 8000 }
        )
    } else {
        await locateByIp()
    }
}

const locateByIp = async () => {
    try {
        const res  = await fetch('https://ipapi.co/json/')
        const data = await res.json()
        if (data.latitude && data.longitude) {
            const lat = parseFloat(data.latitude)
            const lng = parseFloat(data.longitude)
            markerPos.value = [lat, lng]
            map?.setView([lat, lng], 13)
            mapMarker?.setLatLng([lat, lng])
            reverseGeocode(lat, lng)
        } else {
            alert('Unable to determine location. Please click the map or search an address.')
        }
    } catch {
        alert('Unable to determine location. Please click the map or search an address.')
    } finally {
        isLocating.value = false
    }
}

const confirmLocation = () => {
    const [lat, lng] = markerPos.value
    form.map_coordinates = `${lat.toFixed(6)}, ${lng.toFixed(6)}`
    closeMapModal()
}

const submitForm = () => {
    const options = { onSuccess: () => emit('success') }
    if (props.mode === 'create') {
        form.post('/incident-report', options)
    } else {
        form.post(`/incident-report/${props.record.id}`, options)
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

        <form @submit.prevent="submitForm" class="p-4 bg-form-body dark:bg-gray-800 max-h-[85vh] ">

            <!-- ── CREATE MODE ──────────────────────────────────────────────── -->
            <template v-if="mode === 'create'">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Incident Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">

                    <div v-if="hasFullAccess" class="lg:col-span-2">
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Reporter <span class="text-red-500">*</span></label>
                        <Boombox
                            :items="users"
                            :existing-value="form.user_id"
                            label-field="full_name"
                            placeholder="Select reporter"
                            @change="(v: any) => form.user_id = v?.id ?? null"
                        />
                        <p v-if="form.errors.user_id" class="text-xs text-red-500 mt-1">{{ form.errors.user_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Barangay <span class="text-red-500">*</span></label>
                        <Boombox :items="barangays" :existing-value="form.barangay_id" label-field="barangay_name"
                            placeholder="Select barangay" @change="(v: any) => form.barangay_id = v?.id ?? null" />
                        <p v-if="form.errors.barangay_id" class="text-xs text-red-500 mt-1">{{ form.errors.barangay_id }}</p>
                    </div>

                    <!-- Emergency first, then filtered Incident -->
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Emergency Type <span class="text-red-500">*</span></label>
                        <Boombox
                            :items="emergencies"
                            :existing-value="form.emergency_id"
                            label-field="emergency_name"
                            placeholder="Select emergency type"
                            @change="(v: any) => { form.emergency_id = v?.id ?? null }"
                        />
                        <p v-if="form.errors.emergency_id" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">
                            Incident Type <span class="text-red-500">*</span>
                            <span v-if="!form.emergency_id" class="text-gray-400 font-normal text-xs"> — select emergency first</span>
                        </label>
                        <Boombox
                            :items="filteredIncidents"
                            :existing-value="form.incident_id"
                            label-field="incident_name"
                            placeholder="Select incident type"
                            @change="(v: any) => { form.incident_id = v?.id ?? null }"
                        />
                        <p v-if="form.errors.incident_id" class="text-xs text-red-500 mt-1">{{ form.errors.incident_id }}</p>
                    </div>

                    <div>
                        <CustomInput name="Casualty Count" type="number" v-model="form.casualty_count" />
                        <p v-if="form.errors.casualty_count" class="text-xs text-red-500 mt-1">{{ form.errors.casualty_count }}</p>
                    </div>

                    <div>
                        <CustomInput name="Distance (km)" type="number" v-model="form.distance" />
                        <p class="text-xs text-gray-400 mt-0.5">Optional — improves priority accuracy</p>
                        <p v-if="form.errors.distance" class="text-xs text-red-500 mt-1">{{ form.errors.distance }}</p>
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

                    <!-- Priority Score live preview -->
                    <div v-if="priorityDisplay" class="lg:col-span-2">
                        <p class="block m-1 text-sm text-gray-600 dark:text-gray-200">Computed Priority</p>
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-gray-300 bg-gray-50 dark:bg-gray-700/40">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border"
                                :class="priorityBadgeClass">
                                {{ priorityDisplay.level }} — {{ priorityDisplay.label }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Score: <strong class="text-gray-700 dark:text-gray-200">{{ priorityDisplay.score }}</strong> / 10
                            </span>
                        </div>
                    </div>
                    <div v-else-if="form.incident_id === null && form.emergency_id !== null" class="lg:col-span-2">
                        <p class="text-xs text-gray-400 italic">Select an incident type to compute priority score.</p>
                    </div>
                </div>
            </template>

            <!-- ── EDIT MODE: Read-only Report Details ──────────────────────── -->
            <template v-if="mode === 'edit'">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Report Details</h2>
                <div class="grid grid-cols-1 gap-4 p-4 mb-4 rounded-lg border border-orange-200 bg-orange-50 dark:bg-gray-700/50 dark:border-orange-700 lg:grid-cols-3">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Reporter</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.user?.full_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Barangay</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.barangay?.barangay_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Emergency Type</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.emergency?.emergency_name ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Incident Type</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.incident?.incident_name ?? '—' }}</p>
                    </div>

                    <!-- severity_level read from incident_reports (auto-set on create by server) -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Severity Level</p>
                        <span :class="severityBadgeClass(record?.severity_level ?? '')">
                            {{ record?.severity_level ?? '—' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Casualty Count</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.casualty_count ?? '0' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Distance (km)</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ record?.distance != null ? `${record.distance} km` : '—' }}
                        </p>
                    </div>

                    <!-- Priority -->
                    <div class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Priority</p>
                        <div v-if="record?.priority_level" class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border"
                                :class="{
                                    'bg-red-100 text-red-700 border-red-300':          record.priority_level === 'P1',
                                    'bg-orange-100 text-orange-700 border-orange-300': record.priority_level === 'P2',
                                    'bg-yellow-100 text-yellow-700 border-yellow-300': record.priority_level === 'P3',
                                    'bg-blue-100 text-blue-700 border-blue-300':       record.priority_level === 'P4',
                                    'bg-gray-100 text-gray-600 border-gray-300':       record.priority_level === 'P5',
                                }">
                                {{ record.priority_level }} — {{ record.priority_label }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Score: <strong class="text-gray-700 dark:text-gray-200">{{ record.priority_score }}</strong> / 10
                            </span>
                        </div>
                        <p v-else class="text-sm text-gray-400">—</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Date Reported</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ record?.created_at ? new Date(record.created_at).toLocaleString() : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Current Status</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="{
                                'bg-yellow-100 text-yellow-700': record?.status === 'waiting',
                                'bg-orange-100 text-orange-700': record?.status === 'assigned',
                                'bg-purple-100 text-purple-700': record?.status === 'arriving',
                                'bg-green-100 text-green-700':   record?.status === 'resolved',
                                'bg-red-100 text-red-700':       record?.status === 'cancelled',
                            }">
                            {{ record?.status ?? '—' }}
                        </span>
                    </div>

                    <!-- Location spanning full width -->
                    <div class="lg:col-span-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-1">Location</p>
                        <div class="flex items-start gap-2 mb-2">
                            <PhMapPin :size="16" class="text-orange-500 mt-0.5 shrink-0" />
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                <span v-if="isMiniGeocoding" class="text-gray-400 italic">Resolving location...</span>
                                <span v-else-if="miniMapAddress">{{ miniMapAddress }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>
                        <div
                            v-if="record?.map_coordinates"
                            ref="miniMapContainer"
                            class="w-full rounded-lg border border-orange-200 overflow-hidden"
                            style="height: 200px;"
                        />
                    </div>

                    <div v-if="record?.remarks" class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Remarks</p>
                        <p class="text-sm text-gray-800 dark:text-gray-100">{{ record.remarks }}</p>
                    </div>

                    <div v-if="record?.attachment">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Attachment</p>
                        <a :href="`/storage/${record.attachment}`" target="_blank"
                            class="text-sm text-orange-600 hover:underline flex items-center gap-1">
                            View Attachment
                        </a>
                    </div>
                </div>

                <!-- ── Responder Details (editable) ─────────────────────── -->
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Responder Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                    <div><CustomInput name="Responder Name"       v-model="form.responder_name" /></div>
                    <div><CustomInput name="Responder Contact No" v-model="form.responder_contact_no" /></div>
                    <div><CustomInput name="Plate No"             v-model="form.plate_no" /></div>
                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Status</label>
                        <select v-model="form.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:text-white">
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-red-500 mt-1">{{ form.errors.status }}</p>
                    </div>
                    <div><CustomInput name="Estimated Arrival" type="datetime-local" v-model="form.estimated_arrival" /></div>
                    <div><CustomInput name="Datetime Arrived"  type="datetime-local" v-model="form.datetime_arrived" /></div>
                    <div>
                        <CustomInput name="Distance (km)" type="number" v-model="form.distance" />
                        <p v-if="form.errors.distance" class="text-xs text-red-500 mt-1">{{ form.errors.distance }}</p>
                    </div>
                </div>
            </template>

            <!-- ── Notes & Attachment (both modes) ─────────────────────────── -->
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

            <div class="mb-3 relative">
                <div class="flex gap-2">
                    <input v-model="searchQuery" type="text" placeholder="Search address or place name..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        @keyup.enter="searchAddress" />
                    <button type="button" @click="searchAddress" :disabled="isSearching"
                        class="px-3 py-2 text-sm font-medium text-white bg-gray-700 rounded-md hover:bg-gray-900 disabled:opacity-50 flex items-center gap-2">
                        <PhMagnifyingGlass :size="15" />
                        {{ isSearching ? 'Searching...' : 'Search' }}
                    </button>
                </div>
                <ul v-if="searchResults.length"
                    class="absolute z-[9999] w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto">
                    <li v-for="result in searchResults" :key="result.place_id"
                        @click="selectSearchResult(result)"
                        class="px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 cursor-pointer border-b last:border-0">
                        {{ result.display_name }}
                    </li>
                </ul>
            </div>

            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-sm text-gray-600">Click the map, drag the marker, or search an address above.</p>
                <button type="button" @click="getCurrentLocation" :disabled="isLocating"
                    class="px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 disabled:opacity-50 flex items-center gap-2 whitespace-nowrap">
                    <PhMapPin :size="15" />
                    {{ isLocating ? 'Locating...' : 'Use My Location' }}
                </button>
            </div>

            <div ref="mapContainer" class="border border-gray-300 rounded-lg" style="height: 450px; width: 100%;" />

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
