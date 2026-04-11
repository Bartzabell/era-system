<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import FormModal from './Partials/FormModal.vue'
import Boombox from '@/components/BoomBox.vue'
import { Button } from '@/components/ui/button'
import {
    PhPlus, PhMapPin, PhUser, PhFire, PhSiren,
    PhCaretRight, PhPhone, PhImage, PhFloppyDisk, PhTrash, PhPencil,
} from '@phosphor-icons/vue'

interface IrResponder { id: number; responder_id: number; responder_name: string; responder_type: 'leader' | 'member' }

interface IncidentReport {
    id: number; incident_code: string; status: string; severity_level: string
    priority_score: number | null; priority_level: string | null; priority_label: string | null
    casualty_count: number; minor_casualty_count: number; serious_casualty_count: number; deceased_casualty_count: number
    responder_name: string | null; responder_contact_no: string | null; responder_count: number | null; plate_no: string | null
    reported_at: string | null; created_at: string; map_coordinates: string | null; distance: number | null
    remarks: string | null; responder_remarks: string | null; treatment_provided: string | null; cancel_remarks: string | null
    attachment: string | null; responder_attachment: string | null
    estimated_arrival: string | null; datetime_arrived: string | null
    site_location_id: number | null; emergency_id: number | null; incident_id: number | null
    user?: { id: number; first_name: string; last_name: string; mobile_no?: string }
    barangay?: { id: number; barangay_name: string }
    incident?: { id: number; incident_name: string; emergency_id: number; base_severity: number; base_time: number; base_resources: number; base_secondary: number }
    emergency?: { id: number; emergency_name: string }
    siteLocation?: { id: number; site_name: string }
    ir_responders?: IrResponder[]
}

const props = defineProps<{
    incidentReports: IncidentReport[]
    barangays: Array<{ id: number; barangay_name: string }>
    siteLocations: Array<{ id: number; site_name: string; site_type: string; coordinates: string }>
    incidents: Array<{ id: number; incident_name: string; emergency_id: number; base_severity: number; base_time: number; base_resources: number; base_secondary: number }>
    emergencies: Array<{ id: number; emergency_name: string }>
    users: Array<{ id: number; full_name: string }>
    hasFullAccess: boolean
    currentUserId: number
}>()

const breadcrumbs = [{ title: 'Homepage', href: '/landing' }, { title: 'Dispatch', href: '/dispatch' }]

// ── Status ─────────────────────────────────────────────────────────────────
type StatusKey = 'waiting' | 'assigned' | 'arriving' | 'resolved' | 'cancelled'

const statusMeta: Record<StatusKey, { label: string; dot: string; idleBadge: string; activeBadge: string }> = {
    waiting:   { label: 'Waiting',   dot: 'bg-yellow-400', idleBadge: 'bg-white text-yellow-600 border border-yellow-300 hover:bg-yellow-50',  activeBadge: 'bg-yellow-500 text-white border border-yellow-500'  },
    assigned:  { label: 'Assigned',  dot: 'bg-blue-500',   idleBadge: 'bg-white text-blue-600 border border-blue-300 hover:bg-blue-50',        activeBadge: 'bg-blue-500 text-white border border-blue-500'      },
    arriving:  { label: 'Arriving',  dot: 'bg-purple-500', idleBadge: 'bg-white text-purple-600 border border-purple-300 hover:bg-purple-50',  activeBadge: 'bg-purple-500 text-white border border-purple-500'  },
    resolved:  { label: 'Resolved',  dot: 'bg-green-500',  idleBadge: 'bg-white text-green-600 border border-green-300 hover:bg-green-50',     activeBadge: 'bg-green-500 text-white border border-green-500'    },
    cancelled: { label: 'Cancelled', dot: 'bg-gray-400',   idleBadge: 'bg-white text-gray-500 border border-gray-300 hover:bg-gray-50',        activeBadge: 'bg-gray-500 text-white border border-gray-500'      },
}
const statusFlow: StatusKey[] = ['waiting', 'assigned', 'arriving', 'resolved', 'cancelled']
const getStatusMeta = (s: string) => statusMeta[s as StatusKey] ?? statusMeta.waiting

const activeFilter   = ref<StatusKey | null>(null)
const toggleFilter   = (s: StatusKey) => { activeFilter.value = activeFilter.value === s ? null : s }
const statusCounts   = computed(() => props.incidentReports.reduce((c, r) => ({ ...c, [r.status]: (c[r.status] ?? 0) + 1 }), {} as Record<string, number>))
const filteredReports = computed(() => activeFilter.value ? props.incidentReports.filter(r => r.status === activeFilter.value) : props.incidentReports)

// ── Selected report ────────────────────────────────────────────────────────
const selectedId     = ref<number | null>(null)
const selectedReport = computed(() => props.incidentReports.find(r => r.id === selectedId.value) ?? null)
const selectReport   = (id: number) => { selectedId.value = selectedId.value === id ? null : id }

// ── Inline form ────────────────────────────────────────────────────────────
const inlineEmergencyId = ref<number | null>(null)
const inlineIncidentId  = ref<number | null>(null)
const isSubmitting      = ref(false)

watch(selectedId, () => {
    const r = props.incidentReports.find(x => x.id === selectedId.value)
    inlineEmergencyId.value = r?.emergency_id ?? r?.emergency?.id ?? null
    inlineIncidentId.value  = r?.incident_id  ?? r?.incident?.id  ?? null
})

const filteredInlineIncidents = computed(() =>
    inlineEmergencyId.value ? props.incidents.filter(i => i.emergency_id === inlineEmergencyId.value) : props.incidents
)

// ── Priority ───────────────────────────────────────────────────────────────
const WEIGHTS   = { severity: 0.35, time: 0.25, distance: 0.20, resources: 0.10, secondary: 0.10 }
const normDist  = (km: number | null) => parseFloat(((1 - Math.min(Math.max(km ?? 5, 0), 50) / 50) * 10).toFixed(2))
const mapToLevel = (s: number) =>
    s >= 8.5 ? { level: 'P1', label: 'Critical'     } :
    s >= 6.5 ? { level: 'P2', label: 'High'          } :
    s >= 4.5 ? { level: 'P3', label: 'Moderate'      } :
    s >= 2.5 ? { level: 'P4', label: 'Low'           } :
               { level: 'P5', label: 'Informational' }

const computedPriority = computed(() => {
    const inc = props.incidents.find(i => i.id === inlineIncidentId.value)
    if (!inc) return null
    const score = parseFloat((
        inc.base_severity  * WEIGHTS.severity  +
        inc.base_time      * WEIGHTS.time       +
        normDist(selectedReport.value?.distance ?? null) * WEIGHTS.distance +
        inc.base_resources * WEIGHTS.resources  +
        inc.base_secondary * WEIGHTS.secondary
    ).toFixed(2))
    return { score, ...mapToLevel(score) }
})

const priorityBadgeClass = (level: string | null) =>
    ({ P1: 'bg-red-100 text-red-700 border-red-300', P2: 'bg-orange-100 text-orange-700 border-orange-300', P3: 'bg-yellow-100 text-yellow-700 border-yellow-300', P4: 'bg-blue-100 text-blue-700 border-blue-300', P5: 'bg-gray-100 text-gray-600 border-gray-300' } as Record<string, string>)[level ?? ''] ?? 'bg-gray-100 text-gray-600 border-gray-300'

const getPriorityClass = (level: string | null) =>
    ({ P1: 'bg-red-100 text-red-700', P2: 'bg-orange-100 text-orange-700', P3: 'bg-yellow-100 text-yellow-700', P4: 'bg-blue-100 text-blue-700', P5: 'bg-gray-100 text-gray-600' } as Record<string, string>)[level ?? ''] ?? 'bg-gray-100 text-gray-600'

// ── Shared update payload ──────────────────────────────────────────────────
const reportPayload = (r: IncidentReport, overrides = {}) => ({
    status:                  r.status,
    responder_name:          r.responder_name          ?? '',
    responder_contact_no:    r.responder_contact_no    ?? '',
    responder_count:         r.responder_count,
    plate_no:                r.plate_no                ?? '',
    estimated_arrival:       r.estimated_arrival       ?? '',
    datetime_arrived:        r.datetime_arrived        ?? '',
    remarks:                 r.remarks                 ?? '',
    responder_remarks:       r.responder_remarks       ?? '',
    treatment_provided:      r.treatment_provided      ?? '',
    cancel_remarks:          r.cancel_remarks          ?? '',
    minor_casualty_count:    r.minor_casualty_count    ?? 0,
    serious_casualty_count:  r.serious_casualty_count  ?? 0,
    deceased_casualty_count: r.deceased_casualty_count ?? 0,
    distance:                r.distance,
    reported_at:             r.reported_at             ?? '',
    site_location_id:        r.site_location_id,
    priority_score:          r.priority_score,
    priority_level:          r.priority_level,
    priority_label:          r.priority_label,
    ...overrides,
})

// ── Submit / status update ─────────────────────────────────────────────────
const submitReport = () => {
    if (!selectedReport.value) return
    isSubmitting.value = true
    const pri = computedPriority.value
    router.put(`/dispatch/${selectedReport.value.id}`, reportPayload(selectedReport.value, {
        emergency_id:   inlineEmergencyId.value,
        incident_id:    inlineIncidentId.value,
        priority_score: pri?.score ?? selectedReport.value.priority_score,
        priority_level: pri?.level ?? selectedReport.value.priority_level,
        priority_label: pri?.label ?? selectedReport.value.priority_label,
    }), { preserveScroll: true, onFinish: () => { isSubmitting.value = false } })
}

const updatingStatus = ref<number | null>(null)
const updateStatus = (report: IncidentReport, status: string) => {
    updatingStatus.value = report.id
    router.put(`/dispatch/${report.id}`, reportPayload(report, { status }),
        { preserveScroll: true, onFinish: () => { updatingStatus.value = null } })
}

// ── Team assign ────────────────────────────────────────────────────────────
const teamLeaderId  = ref<number | null>(null)
const teamMemberIds = ref<number[]>([])
const isAssigning   = ref(false)

const addMember    = (v: any) => { if (v?.id && !teamMemberIds.value.includes(v.id)) teamMemberIds.value.push(v.id) }
const removeMember = (id: number) => { teamMemberIds.value = teamMemberIds.value.filter(x => x !== id) }

const assignTeam = (report: IncidentReport) => {
    if (!teamLeaderId.value) return
    isAssigning.value = true
    router.post(`/dispatch/${report.id}/assign-team`, {
        leader_id: teamLeaderId.value, member_ids: teamMemberIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { teamLeaderId.value = null; teamMemberIds.value = [] },
        onFinish:  () => { isAssigning.value = false },
    })
}

// ── Modals ─────────────────────────────────────────────────────────────────
const isFormVisible   = ref(false)
const formMode        = ref<'create' | 'edit'>('create')
const currentRecord   = ref<IncidentReport | null>(null)
const showDeleteModal = ref(false)
const recordToDelete  = ref<IncidentReport | null>(null)

const openCreateModal = () => { formMode.value = 'create'; currentRecord.value = null; isFormVisible.value = true }
const openEditModal   = (r: IncidentReport) => { formMode.value = 'edit'; currentRecord.value = r; isFormVisible.value = true }
const closeFormModal  = () => { isFormVisible.value = false; currentRecord.value = null }
const openDeleteModal = (r: IncidentReport) => { recordToDelete.value = r; showDeleteModal.value = true }
const deleteRecord    = () => {
    router.delete(`/dispatch/${recordToDelete.value!.id}`, {
        onSuccess: () => { showDeleteModal.value = false; if (selectedId.value === recordToDelete.value?.id) selectedId.value = null },
    })
}

// ── Time helpers ───────────────────────────────────────────────────────────
const timeAgo = (val: string | null) => {
    if (!val) return ''
    const mins = Math.floor((Date.now() - new Date(val).getTime()) / 60000)
    if (mins < 1) return 'just now'
    if (mins < 60) return `${mins}m ago`
    const hrs = Math.floor(mins / 60)
    return hrs < 24 ? `${hrs}h ago` : `${Math.floor(hrs / 24)}d ago`
}
const minutesAgo = (val: string | null) => {
    if (!val) return 'unknown time'
    const mins = Math.floor((Date.now() - new Date(val).getTime()) / 60000)
    if (mins < 1) return 'just now'
    if (mins < 60) return `${mins} minute${mins !== 1 ? 's' : ''} ago`
    const hrs = Math.floor(mins / 60)
    return hrs < 24 ? `${hrs} hour${hrs !== 1 ? 's' : ''} ago` : `${Math.floor(hrs / 24)} day(s) ago`
}
</script>

<template>
    <Head title="Dispatch" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <Modal :show="isFormVisible" @close="closeFormModal" class="fixed inset-0 z-50">
            <FormModal v-if="isFormVisible" :mode="formMode" :record="currentRecord" :barangays="barangays"
                :incidents="incidents" :site-locations="siteLocations" :emergencies="emergencies" :users="users"
                :has-full-access="hasFullAccess" :current-user-id="currentUserId"
                @close="closeFormModal" @success="closeFormModal" />
        </Modal>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Delete Incident Report</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Are you sure you want to delete <strong>{{ recordToDelete?.incident_code }}</strong>? This cannot be undone.
                </p>
                <div class="flex justify-end mt-6 gap-2">
                    <Button @click="showDeleteModal = false" class="bg-gray-500 hover:bg-gray-600 text-white">Cancel</Button>
                    <Button @click="deleteRecord" class="bg-red-600 hover:bg-red-700 text-white">Delete</Button>
                </div>
            </div>
        </Modal>

        <div class="flex h-[calc(100vh-64px)] overflow-hidden bg-gray-100 dark:bg-gray-950">

            <!-- ── LEFT: Feed ── -->
            <div class="w-72 flex-shrink-0 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Incident Feed</span>
                    <button @click="openCreateModal" class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition-colors">
                        <PhPlus :size="12" weight="bold" /> New
                    </button>
                </div>

                <div class="px-3 py-2.5 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Filter by status</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button v-for="s in statusFlow" :key="s" @click="toggleFilter(s)"
                            :class="['flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all duration-150', activeFilter === s ? statusMeta[s].activeBadge : statusMeta[s].idleBadge]">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', activeFilter === s ? 'bg-white' : statusMeta[s].dot]" />
                            {{ statusCounts[s] ?? 0 }} {{ statusMeta[s].label }}
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div v-if="filteredReports.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 gap-2 p-8">
                        <PhSiren :size="32" class="opacity-30" />
                        <p class="text-xs text-center">No incidents{{ activeFilter ? ` with status "${activeFilter}"` : '' }}.</p>
                    </div>

                    <button v-for="report in filteredReports" :key="report.id" @click="selectReport(report.id)"
                        :class="['w-full text-left px-4 py-3 border-b border-gray-100 dark:border-gray-700 transition-all duration-150 relative group',
                            selectedId === report.id ? 'bg-orange-50 dark:bg-orange-950/30 border-l-[3px] border-l-orange-500 pl-3.5' : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-l-[3px] border-l-transparent']">
                        <div class="flex items-center justify-between mb-1">
                            <span v-if="report.priority_level" :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-full', getPriorityClass(report.priority_level)]">{{ report.priority_level }}</span>
                            <span v-else class="text-[10px] text-gray-300">—</span>
                            <span :class="['text-[10px] font-semibold px-1.5 py-0.5 rounded-full', getStatusMeta(report.status).idleBadge]">{{ getStatusMeta(report.status).label }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ report.emergency?.emergency_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ report.incident?.incident_name ?? '' }}</p>
                        <div class="flex items-center justify-between mt-1 gap-1">
                            <span class="flex items-center gap-1 text-[11px] text-gray-500 truncate">
                                <PhMapPin :size="10" class="text-orange-400 shrink-0" />{{ report.barangay?.barangay_name ?? '—' }}
                            </span>
                            <span class="text-[11px] text-gray-400 shrink-0">{{ timeAgo(report.reported_at ?? report.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5">
                            <PhUser :size="10" class="text-gray-300 shrink-0" />
                            <span class="text-[11px] text-gray-400 truncate">{{ report.user ? `${report.user.first_name} ${report.user.last_name}` : '—' }}</span>
                        </div>
                        <PhCaretRight :size="12" :class="['absolute right-2 top-1/2 -translate-y-1/2 transition-opacity', selectedId === report.id ? 'text-orange-500 opacity-100' : 'opacity-0 group-hover:opacity-25']" />
                    </button>
                </div>
            </div>

            <!-- ── RIGHT: Detail ── -->
            <div class="flex overflow-y-auto w-full">
                <div v-if="!selectedReport" class="flex flex-col items-center justify-center gap-3 text-gray-400 dark:text-gray-600 w-full">
                    <PhFire :size="48" class="opacity-15" />
                    <p class="text-sm font-medium">Select an incident to view details</p>
                    <p class="text-xs opacity-60">Click any report from the feed on the left</p>
                </div>

                <div v-else class="w-full px-5">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-3.5 bg-gray-800 dark:bg-gray-900">
                            <h2 class="text-sm font-bold text-white tracking-wide">Emergency Details</h2>
                            <div v-if="hasFullAccess" class="flex items-center gap-1.5">
                                <button @click="openEditModal(selectedReport)" class="p-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white transition-colors" title="Edit">
                                    <PhPencil :size="13" />
                                </button>
                                <button @click="openDeleteModal(selectedReport)" class="p-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors" title="Delete">
                                    <PhTrash :size="13" />
                                </button>
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="relative w-full bg-gray-900" style="height:186px;">
                            <img v-if="selectedReport.attachment" :src="`/storage/${selectedReport.attachment}`" alt="Incident photo" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-gray-900 flex flex-col items-center justify-center gap-2">
                                <PhImage :size="40" class="text-gray-700" />
                                <p class="text-xs text-gray-600">Photo submitted by citizen</p>
                            </div>
                            <div v-if="computedPriority || selectedReport.priority_level" class="absolute bottom-2 right-2">
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-bold border shadow', priorityBadgeClass(computedPriority?.level ?? selectedReport.priority_level)]">
                                    {{ computedPriority?.level ?? selectedReport.priority_level }} · {{ computedPriority?.label ?? selectedReport.priority_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Info strip -->
                        <div class="flex divide-x divide-gray-100 border-b border-gray-100 text-xs">
                            <div class="flex items-center gap-1 px-3 py-2 min-w-0">
                                <PhMapPin :size="11" class="text-orange-400 shrink-0" />
                                <span class="text-gray-700 dark:text-gray-200 font-medium truncate">{{ selectedReport.siteLocation?.site_name ?? selectedReport.barangay?.barangay_name ?? selectedReport.map_coordinates ?? '-' }}</span>
                            </div>
                            <div class="px-2 py-2 text-gray-400 whitespace-nowrap shrink-0 flex items-center">{{ minutesAgo(selectedReport.reported_at ?? selectedReport.created_at) }}</div>
                            <div class="px-2 py-2 text-orange-600 dark:text-orange-400 font-semibold shrink-0 flex items-center truncate">{{ selectedReport.incident?.incident_name ?? '—' }}</div>
                        </div>

                        <!-- Reporter -->
                        <div class="flex items-center justify-between px-5 py-3 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-100 dark:border-orange-800/30">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-orange-200 dark:bg-orange-700 flex items-center justify-center shrink-0">
                                    <PhUser :size="15" class="text-orange-600 dark:text-orange-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white leading-tight">{{ selectedReport.user ? `${selectedReport.user.first_name} ${selectedReport.user.last_name}` : '—' }}</p>
                                    <p class="text-[11px] text-orange-500 font-medium">Reported By</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 shrink-0">
                                <PhPhone :size="12" class="text-orange-400" />{{ selectedReport.user?.mobile_no ?? '—' }}
                            </div>
                        </div>

                        <!-- Form body -->
                        <div class="px-5 py-4 space-y-4">

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Emergency Type</label>
                                <Boombox :items="emergencies" :existing-value="inlineEmergencyId" label-field="emergency_name" placeholder="Select emergency type"
                                    @change="(v: any) => { inlineEmergencyId = v?.id ?? null; if (!props.incidents.find(i => i.id === inlineIncidentId && i.emergency_id === inlineEmergencyId)) inlineIncidentId = null }" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                                    Incident Type
                                    <span v-if="!inlineEmergencyId" class="normal-case font-normal text-gray-400 text-[10px]">— select emergency first</span>
                                </label>
                                <Boombox :items="filteredInlineIncidents" :existing-value="inlineIncidentId" label-field="incident_name" placeholder="Select incident type"
                                    @change="(v: any) => inlineIncidentId = v?.id ?? null" />
                            </div>

                            <div v-if="computedPriority" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-dashed border-gray-200 dark:border-gray-600">
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-bold border', priorityBadgeClass(computedPriority.level)]">{{ computedPriority.level }} · {{ computedPriority.label }}</span>
                                <span class="text-xs text-gray-500">Score: <strong class="text-gray-700 dark:text-gray-200">{{ computedPriority.score }}</strong>/10</span>
                            </div>

                            <button @click="submitReport" :disabled="isSubmitting || !inlineIncidentId || !inlineEmergencyId"
                                class="w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold tracking-wide transition-colors flex items-center justify-center gap-2 shadow-sm">
                                <PhFloppyDisk :size="16" />{{ isSubmitting ? 'Saving…' : 'Submit Report' }}
                            </button>

                            <!-- Responder Team -->
                            <template v-if="hasFullAccess">
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Responder Team</p>

                                    <div v-if="selectedReport.ir_responders?.length" class="space-y-1.5">
                                        <div v-for="r in selectedReport.ir_responders" :key="r.id"
                                            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                                            <span :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-full', r.responder_type === 'leader' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700']">
                                                {{ r.responder_type === 'leader' ? 'Leader' : 'Member' }}
                                            </span>
                                            <span class="text-xs text-gray-700 dark:text-gray-200">{{ r.responder_name }}</span>
                                        </div>
                                    </div>
                                    <p v-else class="text-xs text-gray-400 italic">No responders assigned yet.</p>

                                    <template v-if="selectedReport.status === 'waiting'">
                                        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2">
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Assign Team</p>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Team Leader</label>
                                                <Boombox :items="users" :existing-value="teamLeaderId" label-field="full_name" placeholder="Select leader"
                                                    @change="(v: any) => teamLeaderId = v?.id ?? null" />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Team Members</label>
                                                <Boombox :items="users.filter(u => u.id !== teamLeaderId)" :existing-value="null" label-field="full_name" placeholder="Add member" @change="addMember" />
                                                <div v-if="teamMemberIds.length" class="mt-1.5 flex flex-wrap gap-1">
                                                    <span v-for="id in teamMemberIds" :key="id" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">
                                                        {{ users.find(u => u.id === id)?.full_name }}
                                                        <button @click="removeMember(id)" class="ml-0.5 text-blue-400 hover:text-blue-700">×</button>
                                                    </span>
                                                </div>
                                            </div>

                                            <button @click="assignTeam(selectedReport)" :disabled="!teamLeaderId || isAssigning"
                                                class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-bold tracking-wide transition-colors flex items-center justify-center gap-2 shadow-sm">
                                                <PhUser :size="16" />{{ isAssigning ? 'Dispatching…' : 'Dispatch Team' }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Status buttons -->
                        <div v-if="hasFullAccess && ['arriving','assigned','waiting'].includes(selectedReport.status)"
                            class="px-5 pb-4 pt-1 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2.5 mt-3">Update Status</p>
                            <div class="flex gap-1.5">
                                <button v-for="s in (['resolved','cancelled'] as StatusKey[])" :key="s"
                                    :disabled="updatingStatus === selectedReport.id" @click="updateStatus(selectedReport, s)"
                                    :class="['flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all border cursor-pointer disabled:opacity-50',
                                        selectedReport.status === s ? statusMeta[s].activeBadge : statusMeta[s].idleBadge]">
                                    <span :class="['w-1.5 h-1.5 rounded-full shrink-0', selectedReport.status === s ? 'bg-white' : statusMeta[s].dot]" />
                                    {{ statusMeta[s].label }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
