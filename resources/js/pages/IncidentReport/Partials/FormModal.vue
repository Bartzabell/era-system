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
L.Icon.Default.mergeOptions({ iconRetinaUrl: markerIcon2x, iconUrl: markerIcon, shadowUrl: markerShadow })

const props = defineProps<{
    mode: 'create' | 'edit'
    record: any | null
    barangays: Array<{ id: number; barangay_name: string }>
    incidents: Array<{ id: number; incident_name: string; emergency_id: number; base_severity: number; base_time: number; base_resources: number; base_secondary: number }>
    emergencies: Array<{ id: number; emergency_name: string }>
    siteLocations: Array<{ id: number; site_name: string; site_type: string; coordinates: string }>
    users: Array<{ id: number; full_name: string }>
    hasFullAccess: boolean
    currentUserId: number
}>()

const emit = defineEmits(['close', 'success'])

const isEdit = computed(() => props.mode === 'edit')

const form = useForm({
    _method:                 isEdit.value ? 'PUT' : undefined,
    user_id:                 props.hasFullAccess ? (props.record?.user_id ?? props.currentUserId) : props.currentUserId,
    barangay_id:             props.record?.barangay_id              ?? null,
    map_coordinates:         props.record?.map_coordinates          ?? '',
    emergency_id:            props.record?.emergency_id             ?? null,
    incident_id:             props.record?.incident_id              ?? null,
    casualty_count:          props.record?.casualty_count           ?? 0,
    minor_casualty_count:    props.record?.minor_casualty_count     ?? 0,
    serious_casualty_count:  props.record?.serious_casualty_count   ?? 0,
    deceased_casualty_count: props.record?.deceased_casualty_count  ?? 0,
    distance:                props.record?.distance                 ?? null,
    distance_km:             props.record?.distance_km              ?? null,
    remarks:                 props.record?.remarks                  ?? '',
    attachment:              null as File | null,
    responder_name:          props.record?.responder_name           ?? '',
    responder_contact_no:    props.record?.responder_contact_no     ?? '',
    responder_count:         props.record?.responder_count          ?? null,
    responder_remarks:       props.record?.responder_remarks        ?? '',
    responder_attachment:    null as File | null,
    treatment_provided:      props.record?.treatment_provided       ?? '',
    cancel_remarks:          props.record?.cancel_remarks           ?? '',
    estimated_arrival:       props.record?.estimated_arrival        ?? '',
    datetime_arrived:        props.record?.datetime_arrived         ?? '',
    plate_no:                props.record?.plate_no                 ?? '',
    status:                  props.record?.status                   ?? 'waiting',
    reported_at:             props.record?.reported_at              ?? '',
    site_location_id:        props.record?.site_location_id         ?? null,
    priority_score:          props.record?.priority_score           ?? null,
    priority_level:          props.record?.priority_level           ?? null,
    priority_label:          props.record?.priority_label           ?? null,
})

const filteredIncidents = computed(() =>
    form.emergency_id ? props.incidents.filter(i => i.emergency_id === form.emergency_id) : props.incidents
)

const WEIGHTS = { severity: 0.35, time: 0.25, distance: 0.20, resources: 0.10, secondary: 0.10 }

const normDist = (km: number | null) =>
    parseFloat(((1 - Math.min(Math.max(km ?? 5, 0), 50) / 50) * 10).toFixed(2))

const mapToLevel = (s: number) =>
    s >= 8.5 ? { level: 'P1', label: 'Critical' } :
    s >= 6.5 ? { level: 'P2', label: 'High' } :
    s >= 4.5 ? { level: 'P3', label: 'Moderate' } :
    s >= 2.5 ? { level: 'P4', label: 'Low' } :
               { level: 'P5', label: 'Informational' }

const recomputePriority = () => {
    const inc = props.incidents.find(i => i.id === form.incident_id)
    if (!inc) { form.priority_score = null; form.priority_level = null; form.priority_label = null; return }
    const distKm = form.distance_km ?? form.distance
    const score = parseFloat((
        inc.base_severity  * WEIGHTS.severity  +
        inc.base_time      * WEIGHTS.time       +
        normDist(distKm)   * WEIGHTS.distance   +
        inc.base_resources * WEIGHTS.resources  +
        inc.base_secondary * WEIGHTS.secondary
    ).toFixed(2))
    const { level, label } = mapToLevel(score)
    form.priority_score = score; form.priority_level = level; form.priority_label = label
}

const haversineKm = (lat1: number, lon1: number, lat2: number, lon2: number): number => {
    const R = 6371, toRad = (d: number) => d * Math.PI / 180
    const dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1)
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2
    return parseFloat((R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))).toFixed(2))
}

const recomputeDistanceFromSite = () => {
    if (!form.site_location_id || !form.map_coordinates) { recomputePriority(); return }
    const site = props.siteLocations.find(s => s.id === form.site_location_id)
    if (!site?.coordinates) { recomputePriority(); return }
    const [iLat, iLng] = form.map_coordinates.split(',').map(Number)
    const [sLat, sLng] = site.coordinates.split(',').map(Number)
    form.distance_km = haversineKm(iLat, iLng, sLat, sLng)
    recomputePriority()
}

watch(() => form.emergency_id, (val) => {
    if (!val) return
    if (!props.incidents.find(i => i.id === form.incident_id && i.emergency_id === val)) form.incident_id = null
    recomputePriority()
})
watch(() => form.incident_id,     recomputePriority)
watch(() => form.distance,        recomputePriority)
watch(() => form.distance_km,     recomputePriority)
watch(() => form.site_location_id, recomputeDistanceFromSite)
watch(() => form.map_coordinates,  recomputeDistanceFromSite)

const priorityDisplay = computed(() => form.priority_score ? { score: form.priority_score, level: form.priority_level, label: form.priority_label } : null)

const priorityBadgeClass = computed(() => ({
    P1: 'bg-red-100 text-red-700 border-red-300',
    P2: 'bg-orange-100 text-orange-700 border-orange-300',
    P3: 'bg-yellow-100 text-yellow-700 border-yellow-300',
    P4: 'bg-blue-100 text-blue-700 border-blue-300',
    P5: 'bg-gray-100 text-gray-600 border-gray-300',
} as Record<string, string>)[form.priority_level ?? ''] ?? 'bg-gray-100 text-gray-600 border-gray-300')

const statusOptions = [
    { value: 'waiting', label: 'Waiting' }, { value: 'assigned', label: 'Assigned' },
    { value: 'arriving', label: 'Arriving' }, { value: 'resolved', label: 'Resolved' },
    { value: 'cancelled', label: 'Cancelled' },
]

const handleFileChange = (e: Event, field: 'attachment' | 'responder_attachment') => {
    const f = (e.target as HTMLInputElement).files?.[0]
    if (f) form[field] = f
}

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

const miniMapContainer = ref<HTMLElement | null>(null)
const miniMapAddress   = ref('')
const isMiniGeocoding  = ref(false)
let miniMap: L.Map | null = null

const parseCoordsFromString = (str: string): [number, number] | null => {
    if (!str) return null
    const parts = str.split(',').map(s => parseFloat(s.trim()))
    return parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1]) ? [parts[0], parts[1]] : null
}

const reverseGeocodeAddress = async (lat: number, lng: number): Promise<string> => {
    try {
        const data = await (await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`, { headers: { 'Accept-Language': 'en' } })).json()
        const p = data.address ?? {}
        const nearby = p.amenity || p.leisure || p.tourism || p.road || p.neighbourhood || p.suburb || p.village || p.town || p.city
        return nearby ? `${nearby}${(p.city || p.town || p.municipality) ? `, ${p.city || p.town || p.municipality}` : ''}` : (data.display_name ?? 'Unknown location')
    } catch { return 'Unable to resolve address' }
}

const initMiniMap = async () => {
    if (!miniMapContainer.value) return
    const coords = parseCoordsFromString(props.record?.map_coordinates ?? '')
    if (!coords) return
    isMiniGeocoding.value = true; miniMapAddress.value = ''
    await nextTick()
    if (miniMap) { miniMap.remove(); miniMap = null }
    miniMap = L.map(miniMapContainer.value, { zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, touchZoom: false, keyboard: false, attributionControl: false }).setView(coords, 16)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(miniMap)
    L.marker(coords).addTo(miniMap)
    miniMapAddress.value  = await reverseGeocodeAddress(coords[0], coords[1])
    isMiniGeocoding.value = false
}

onMounted(() => {
    if (isEdit.value) nextTick(() => initMiniMap())
    else recomputePriority()
})

const reverseGeocode = async (lat: number, lng: number) => {
    isGeocoding.value = true; resolvedAddress.value = ''
    try {
        const data = await (await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`, { headers: { 'Accept-Language': 'en' } })).json()
        resolvedAddress.value = data.display_name ?? 'Address not found'
    } catch { resolvedAddress.value = 'Unable to resolve address' }
    finally { isGeocoding.value = false }
}

const searchAddress = async () => {
    if (!searchQuery.value.trim()) return
    isSearching.value = true; searchResults.value = []
    try { searchResults.value = await (await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchQuery.value)}&format=json&limit=5`, { headers: { 'Accept-Language': 'en' } })).json() }
    catch { searchResults.value = [] }
    finally { isSearching.value = false }
}

const selectSearchResult = (result: any) => {
    const lat = parseFloat(result.lat), lng = parseFloat(result.lon)
    markerPos.value = [lat, lng]; resolvedAddress.value = result.display_name
    searchResults.value = []; searchQuery.value = ''
    map?.setView([lat, lng], 16); mapMarker?.setLatLng([lat, lng])
}

const updateMarker = (lat: number, lng: number) => { markerPos.value = [lat, lng]; reverseGeocode(lat, lng) }

const initMap = () => {
    nextTick(() => {
        if (!mapContainer.value) return
        let center: [number, number] = [14.5995, 120.9842]
        if (form.map_coordinates) { const p = parseCoordsFromString(form.map_coordinates); if (p) { center = p; markerPos.value = center } }
        map = L.map(mapContainer.value).setView(center, 13)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors' }).addTo(map)
        mapMarker = L.marker(markerPos.value, { draggable: true }).addTo(map)
        reverseGeocode(markerPos.value[0], markerPos.value[1])
        map.on('click', (e: L.LeafletMouseEvent) => { mapMarker?.setLatLng(e.latlng); updateMarker(e.latlng.lat, e.latlng.lng) })
        mapMarker.on('dragend', () => { if (mapMarker) { const p = mapMarker.getLatLng(); updateMarker(p.lat, p.lng) } })
    })
}

const openMapPicker = () => { showMap.value = true; nextTick(() => initMap()) }
const closeMapModal = () => { showMap.value = false; searchQuery.value = ''; searchResults.value = []; resolvedAddress.value = ''; map?.remove(); map = null; mapMarker = null }

const getCurrentLocation = () => {
    isLocating.value = true
    navigator.geolocation
        ? navigator.geolocation.getCurrentPosition(
            ({ coords }) => { markerPos.value = [coords.latitude, coords.longitude]; map?.setView(markerPos.value, 16); mapMarker?.setLatLng(markerPos.value); reverseGeocode(coords.latitude, coords.longitude); isLocating.value = false },
            async () => locateByIp(), { timeout: 8000 }
          )
        : locateByIp()
}

const locateByIp = async () => {
    try {
        const data = await (await fetch('https://ipapi.co/json/')).json()
        if (data.latitude && data.longitude) {
            markerPos.value = [parseFloat(data.latitude), parseFloat(data.longitude)]
            map?.setView(markerPos.value, 13); mapMarker?.setLatLng(markerPos.value); reverseGeocode(markerPos.value[0], markerPos.value[1])
        } else { alert('Unable to determine location.') }
    } catch { alert('Unable to determine location.') }
    finally { isLocating.value = false }
}

const confirmLocation = () => {
    const [lat, lng] = markerPos.value
    form.map_coordinates = `${lat.toFixed(6)}, ${lng.toFixed(6)}`
    closeMapModal()
}

const submitForm = () => {
    const options = { onSuccess: () => emit('success') }
    isEdit.value ? form.post(`/incident-report/${props.record.id}`, options) : form.post('/incident-report', options)
}

const closeModal = () => { form.reset(); form.clearErrors(); emit('close') }
</script>

<template>
    <div class="w-full lg:w-[80vw] xl:w-[90vw]">
        <div class="flex items-center justify-between w-full px-8 py-1 bg-form-header border-b border-black dark:border-gray-500 dark:bg-gray-800">
            <h1 class="text-base lg:text-2xl font-extrabold dark:text-gray-200">
                {{ isEdit ? `Edit Incident Report : ${record?.incident_code ?? '—'}` : 'Create Incident Report' }}
            </h1>
            <button @click="closeModal" class="p-3 text-white rounded-full bg-red-500 hover:bg-red-600">
                <PhX :size="16" />
            </button>
        </div>

        <form @submit.prevent="submitForm" class="p-4 bg-form-body dark:bg-gray-800 max-h-[85vh]">

            <!-- INCIDENT DETAILS (create) -->
            <template v-if="!isEdit">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Incident Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">

                    <div v-if="hasFullAccess" class="lg:col-span-2">
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Reporter <span class="text-red-500">*</span></label>
                        <Boombox :items="users" :existing-value="form.user_id" label-field="full_name"
                            placeholder="Select reporter" @change="(v: any) => form.user_id = v?.id ?? null" />
                        <p v-if="form.errors.user_id" class="text-xs text-red-500 mt-1">{{ form.errors.user_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Barangay <span class="text-red-500">*</span></label>
                        <Boombox :items="barangays" :existing-value="form.barangay_id" label-field="barangay_name"
                            placeholder="Select barangay" @change="(v: any) => form.barangay_id = v?.id ?? null" />
                        <p v-if="form.errors.barangay_id" class="text-xs text-red-500 mt-1">{{ form.errors.barangay_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Emergency Type <span class="text-red-500">*</span></label>
                        <Boombox :items="emergencies" :existing-value="form.emergency_id" label-field="emergency_name"
                            placeholder="Select emergency type" @change="(v: any) => form.emergency_id = v?.id ?? null" />
                        <p v-if="form.errors.emergency_id" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">
                            Incident Type <span class="text-red-500">*</span>
                            <span v-if="!form.emergency_id" class="text-gray-400 font-normal text-xs"> — select emergency first</span>
                        </label>
                        <Boombox :items="filteredIncidents" :existing-value="form.incident_id" label-field="incident_name"
                            placeholder="Select incident type" @change="(v: any) => form.incident_id = v?.id ?? null" />
                        <p v-if="form.errors.incident_id" class="text-xs text-red-500 mt-1">{{ form.errors.incident_id }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Map Coordinates <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <CustomInput name="" v-model="form.map_coordinates" class="flex-1" readonly />
                            <button type="button" @click="openMapPicker"
                                class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-md hover:bg-gray-900 flex items-center gap-2 whitespace-nowrap">
                                <PhMapPin :size="18" /> Pick on Map
                            </button>
                        </div>
                        <p v-if="form.errors.map_coordinates" class="text-xs text-red-500 mt-1">{{ form.errors.map_coordinates }}</p>
                        <p v-else-if="form.map_coordinates" class="mt-1 text-xs text-orange-600 flex items-center gap-1">
                            <PhCheckCircle :size="14" /> Location selected
                        </p>
                    </div>

                    <div v-if="priorityDisplay" class="lg:col-span-2">
                        <p class="block m-1 text-sm text-gray-600 dark:text-gray-200">Computed Priority</p>
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-gray-300 bg-gray-50 dark:bg-gray-700/40">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border" :class="priorityBadgeClass">
                                {{ priorityDisplay.level }} — {{ priorityDisplay.label }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Score: <strong class="text-gray-700 dark:text-gray-200">{{ priorityDisplay.score }}</strong> / 10</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- READ-ONLY CARD (edit) -->
            <template v-else>
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Report Details</h2>
                <div class="grid grid-cols-1 gap-4 p-4 mb-4 rounded-lg border border-orange-200 bg-orange-50 dark:bg-gray-700/50 dark:border-orange-700 lg:grid-cols-3">
                    <div v-for="(val, key) in {
                        Reporter: record?.user?.full_name,
                        Barangay: record?.barangay?.barangay_name,
                        'Emergency Type': record?.emergency?.emergency_name,
                        'Incident Type': record?.incident?.incident_name,
                        'Distance (km)': record?.distance != null ? `${record.distance} km` : null,
                    }" :key="key">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">{{ key }}</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ val ?? '—' }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Priority</p>
                        <div v-if="record?.priority_level" class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border" :class="{
                                'bg-red-100 text-red-700 border-red-300':          record.priority_level === 'P1',
                                'bg-orange-100 text-orange-700 border-orange-300': record.priority_level === 'P2',
                                'bg-yellow-100 text-yellow-700 border-yellow-300': record.priority_level === 'P3',
                                'bg-blue-100 text-blue-700 border-blue-300':       record.priority_level === 'P4',
                                'bg-gray-100 text-gray-600 border-gray-300':       record.priority_level === 'P5',
                            }">{{ record.priority_level }} — {{ record.priority_label }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Score: <strong class="text-gray-700 dark:text-gray-200">{{ record.priority_score }}</strong> / 10</span>
                        </div>
                        <p v-else class="text-sm text-gray-400">—</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Date Reported</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ record?.created_at ? new Date(record.created_at).toLocaleString() : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Current Status</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize" :class="{
                            'bg-yellow-100 text-yellow-700': record?.status === 'waiting',
                            'bg-orange-100 text-orange-700': record?.status === 'assigned',
                            'bg-purple-100 text-purple-700': record?.status === 'arriving',
                            'bg-green-100 text-green-700':   record?.status === 'resolved',
                            'bg-red-100 text-red-700':       record?.status === 'cancelled',
                        }">{{ record?.status ?? '—' }}</span>
                    </div>

                    <div class="lg:col-span-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-1">Location</p>
                        <div class="flex items-start gap-2 mb-2">
                            <PhMapPin :size="16" class="text-orange-500 mt-0.5 shrink-0" />
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                <span v-if="isMiniGeocoding" class="text-gray-400 italic">Resolving location...</span>
                                <span v-else>{{ miniMapAddress || '—' }}</span>
                            </p>
                        </div>
                        <div v-if="record?.map_coordinates" ref="miniMapContainer" class="w-full rounded-lg border border-orange-200 overflow-hidden" style="height: 200px;" />
                    </div>

                    <div v-if="record?.remarks" class="lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Remarks</p>
                        <p class="text-sm text-gray-800 dark:text-gray-100">{{ record.remarks }}</p>
                    </div>
                    <div v-if="record?.attachment">
                        <p class="text-xs font-semibold uppercase tracking-wider text-orange-500 dark:text-orange-400 mb-0.5">Attachment</p>
                        <a :href="`/storage/${record.attachment}`" target="_blank" class="text-sm text-orange-600 hover:underline">View Attachment</a>
                    </div>
                </div>
            </template>

            <!-- RESPONDER DETAILS (edit always; create if admin) -->
            <template v-if="isEdit || hasFullAccess">
                <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Responder Details</h2>
                <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">

                    <div><CustomInput name="Responder Name"       v-model="form.responder_name" /></div>
                    <div><CustomInput name="Responder Contact No" v-model="form.responder_contact_no" /></div>
                    <div><CustomInput name="Responder Count" type="number" v-model="form.responder_count" /></div>
                    <div><CustomInput name="Plate No"             v-model="form.plate_no" /></div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Site Location</label>
                        <Boombox :items="siteLocations" :existing-value="form.site_location_id" label-field="site_name"
                            placeholder="Select site location" @change="(v: any) => form.site_location_id = v?.id ?? null" />
                        <p v-if="form.distance_km != null" class="text-xs text-orange-600 mt-1">
                            Computed distance: <strong>{{ form.distance_km }} km</strong>
                        </p>
                        <p v-if="form.errors.site_location_id" class="text-xs text-red-500 mt-1">{{ form.errors.site_location_id }}</p>
                    </div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Status</label>
                        <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:bg-gray-700 dark:text-white">
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-red-500 mt-1">{{ form.errors.status }}</p>
                    </div>

                    <div><CustomInput name="Estimated Arrival" type="datetime-local" v-model="form.estimated_arrival" /></div>
                    <div><CustomInput name="Datetime Arrived"  type="datetime-local" v-model="form.datetime_arrived" /></div>
                    <div><CustomInput name="Reported At"       type="datetime-local" v-model="form.reported_at" /></div>

                    <div class="lg:col-span-2">
                        <label class="block m-1 text-sm font-semibold text-gray-600 dark:text-gray-200">Casualty Count</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <CustomInput name="Minor"    type="number" v-model="form.minor_casualty_count" />
                                <p v-if="form.errors.minor_casualty_count" class="text-xs text-red-500 mt-1">{{ form.errors.minor_casualty_count }}</p>
                            </div>
                            <div>
                                <CustomInput name="Serious"  type="number" v-model="form.serious_casualty_count" />
                                <p v-if="form.errors.serious_casualty_count" class="text-xs text-red-500 mt-1">{{ form.errors.serious_casualty_count }}</p>
                            </div>
                            <div>
                                <CustomInput name="Deceased" type="number" v-model="form.deceased_casualty_count" />
                                <p v-if="form.errors.deceased_casualty_count" class="text-xs text-red-500 mt-1">{{ form.errors.deceased_casualty_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2"><CustomInput name="Responder Remarks"  v-model="form.responder_remarks" /></div>
                    <div class="lg:col-span-2"><CustomInput name="Treatment Provided" v-model="form.treatment_provided" /></div>
                    <div class="lg:col-span-2"><CustomInput name="Cancel Remarks"     v-model="form.cancel_remarks" /></div>

                    <div>
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Responder Attachment</label>
                        <input type="file" @change="(e) => handleFileChange(e, 'responder_attachment')" accept="image/*,application/pdf"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" />
                        <p v-if="form.errors.responder_attachment" class="text-xs text-red-500 mt-1">{{ form.errors.responder_attachment }}</p>
                    </div>
                    <div v-if="isEdit && record?.responder_attachment">
                        <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Current Responder Attachment</label>
                        <a :href="`/storage/${record.responder_attachment}`" target="_blank" class="text-sm text-orange-600 hover:underline">View File</a>
                    </div>
                </div>
            </template>

            <!-- NOTES & ATTACHMENT -->
            <h2 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Notes & Attachment</h2>
            <div class="grid grid-cols-1 gap-3 p-3 mb-4 border border-dashed border-gray-400 lg:grid-cols-2">
                <div><CustomInput name="Remarks" v-model="form.remarks" /></div>
                <div>
                    <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Attachment</label>
                    <input type="file" @change="(e) => handleFileChange(e, 'attachment')" accept="image/*,application/pdf"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" />
                    <p v-if="form.errors.attachment" class="text-xs text-red-500 mt-1">{{ form.errors.attachment }}</p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-2 mt-4">
                <ButtonCode type="submit" :icon="isEdit ? PhFloppyDisk : PhPlus"
                    color="bg-orange-500 hover:bg-orange-600" :text="isEdit ? 'Update' : 'Save'" />
                <ButtonCode v-if="isEdit" type="button" color="bg-red-500 hover:bg-red-600" text="Cancel" @click="closeModal" />
            </div>
        </form>
    </div>

    <!-- Map Picker Modal -->
    <Modal :show="showMap" max-width="4xl" @close="closeMapModal">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Location on Map</h3>
                <button @click="closeMapModal"><PhXCircle :size="32" color="#f08000" weight="fill" /></button>
            </div>

            <div class="mb-3 relative">
                <div class="flex gap-2">
                    <input v-model="searchQuery" type="text" placeholder="Search address or place name..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        @keyup.enter="searchAddress" />
                    <button type="button" @click="searchAddress" :disabled="isSearching"
                        class="px-3 py-2 text-sm font-medium text-white bg-gray-700 rounded-md hover:bg-gray-900 disabled:opacity-50 flex items-center gap-2">
                        <PhMagnifyingGlass :size="15" /> {{ isSearching ? 'Searching...' : 'Search' }}
                    </button>
                </div>
                <ul v-if="searchResults.length" class="absolute z-[9999] w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto">
                    <li v-for="result in searchResults" :key="result.place_id" @click="selectSearchResult(result)"
                        class="px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 cursor-pointer border-b last:border-0">
                        {{ result.display_name }}
                    </li>
                </ul>
            </div>

            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-sm text-gray-600">Click the map, drag the marker, or search an address above.</p>
                <button type="button" @click="getCurrentLocation" :disabled="isLocating"
                    class="px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 disabled:opacity-50 flex items-center gap-2 whitespace-nowrap">
                    <PhMapPin :size="15" /> {{ isLocating ? 'Locating...' : 'Use My Location' }}
                </button>
            </div>

            <div ref="mapContainer" class="border border-gray-300 rounded-lg" style="height: 450px; width: 100%;" />

            <div class="mt-3 p-3 bg-gray-50 rounded-md space-y-1">
                <p class="text-sm text-gray-700"><strong>Coordinates:</strong> {{ markerPos[0].toFixed(6) }}, {{ markerPos[1].toFixed(6) }}</p>
                <p class="text-sm text-gray-700">
                    <strong>Address:</strong>
                    <span v-if="isGeocoding" class="text-gray-400 italic"> Resolving address...</span>
                    <span v-else class="text-gray-600"> {{ resolvedAddress || '—' }}</span>
                </p>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" @click="closeMapModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="button" @click="confirmLocation" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">Confirm Location</button>
            </div>
        </div>
    </Modal>
</template>
