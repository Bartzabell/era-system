<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';

interface IncidentMarker {
    id: number;
    lat: number;
    lng: number;
    priority_level: string;
    priority_label: string;
    priority_score: number;
    status: string;
    incident_type: string;
    emergency_type: string;
    barangay: string;
    casualty_count: number;
    reported_at: string;
}

const props = defineProps<{
    incidents: IncidentMarker[];
}>();

const mapContainer = ref<HTMLElement | null>(null);
let map: google.maps.Map | null = null;
const markers: google.maps.marker.AdvancedMarkerElement[] = [];
const infoWindows: google.maps.InfoWindow[] = [];

const getPriorityColor = (level: string) => {
    const colors: Record<string, string> = {
        P1: '#dc2626',
        P2: '#ea580c',
        P3: '#eab308',
        P4: '#22c55e',
        P5: '#9ca3af',
    };
    return colors[level] ?? '#9ca3af';
};

const getEmergencyIcon = (emergencyType: string): string => {
    const icons: Record<string, string> = {
        'Medical Case':          '🏥',
        'Trauma Case':           '🩹',
        'Fire Incident':         '🔥',
        'Vehicle Accident':      '🚗',
        'Rescue Operation':      '🚨',
        'Crime / Security Case': '🚔',
        'Disaster Response':     '🌪️',
    };
    return icons[emergencyType] ?? '⚠️';
};

const createMarkerElement = (level: string, emergencyType: string = ''): HTMLElement => {
    const color = getPriorityColor(level);
    const icon  = getEmergencyIcon(emergencyType);

    const el = document.createElement('div');
    el.style.cssText = `
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
    `;

    el.innerHTML = `
        <div style="
            background-color: ${color};
            width: 36px;
            height: 36px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
        ">
            <span style="transform: rotate(45deg); font-size: 16px; line-height: 1;">${icon}</span>
        </div>
    `;

    return el;
};

const closeAllInfoWindows = () => {
    infoWindows.forEach(iw => iw.close());
};

const clearMarkers = () => {
    markers.forEach(m => (m.map = null));
    markers.length = 0;
    infoWindows.forEach(iw => iw.close());
    infoWindows.length = 0;
};

const updateMarkers = () => {
    if (!map) return;

    clearMarkers();

    if (!props.incidents?.length) return;

    const bounds = new google.maps.LatLngBounds();
    const { AdvancedMarkerElement } = google.maps.marker;

    props.incidents.forEach((incident) => {
        const color    = getPriorityColor(incident.priority_level);
        const position = { lat: incident.lat, lng: incident.lng };

        const marker = new AdvancedMarkerElement({
            position,
            map,
            content: createMarkerElement(incident.priority_level, incident.emergency_type),
            title:   incident.incident_type,
        });

        const popupContent = `
            <div style="font-family: system-ui, sans-serif; min-width: 210px; padding: 4px;">
                <h3 style="font-weight: 700; font-size: 15px; margin: 0 0 8px; color: ${color};">
                    ${incident.incident_type}
                </h3>
                <div style="font-size: 13px; line-height: 1.7; color: #374151;">
                    <p style="margin:0"><strong>Emergency:</strong> ${incident.emergency_type}</p>
                    <p style="margin:0"><strong>Barangay:</strong> ${incident.barangay}</p>
                    <p style="margin:0">
                        <strong>Priority:</strong>
                        <span style="color: ${color}; font-weight: 600;">
                            ${incident.priority_level ?? ''} — ${incident.priority_label ?? ''}
                        </span>
                    </p>
                    <p style="margin:0">
                        <strong>Score:</strong>
                        ${incident.priority_score != null ? incident.priority_score + ' / 10' : '—'}
                    </p>
                    <p style="margin:0"><strong>Status:</strong> <span style="text-transform:capitalize">${incident.status}</span></p>
                    ${incident.casualty_count ? `<p style="margin:0"><strong>Casualties:</strong> ${incident.casualty_count}</p>` : ''}
                    <p style="color: #9ca3af; font-size: 11px; margin-top: 8px;">${incident.reported_at ?? ''}</p>
                </div>
            </div>
        `;

        const infoWindow = new google.maps.InfoWindow({ content: popupContent });

        marker.addListener('click', () => {
            closeAllInfoWindows();
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        infoWindows.push(infoWindow);
        bounds.extend(position);
    });

    if (markers.length > 0) {
        map.fitBounds(bounds);

        google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
            if (map && (map.getZoom() ?? 0) > 15) {
                map.setZoom(15);
            }
        });
    }
};

const loadGoogleMaps = (): Promise<void> => {
    return new Promise((resolve, reject) => {
        if (window.google?.maps?.marker?.AdvancedMarkerElement) {
            resolve();
            return;
        }

        const callbackName = '__gmaps_cb__';
        (window as any)[callbackName] = () => {
            resolve();
            delete (window as any)[callbackName];
        };

        const key = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
        if (!key) {
            console.error('[IncidentMap] VITE_GOOGLE_MAPS_API_KEY is not set in your .env file.');
            reject(new Error('Missing Google Maps API key'));
            return;
        }

        const script   = document.createElement('script');
        script.src     = `https://maps.googleapis.com/maps/api/js?key=${key}&libraries=marker&loading=async&callback=${callbackName}`;
        script.async   = true;
        script.defer   = true;
        script.onerror = () => reject(new Error('Failed to load Google Maps script'));
        document.head.appendChild(script);
    });
};

const initMap = async () => {
    if (!mapContainer.value) return;

    try {
        await loadGoogleMaps();
    } catch (e) {
        console.error('[IncidentMap] Failed to load Google Maps:', e);
        return;
    }

    const mapId = import.meta.env.VITE_GOOGLE_MAPS_MAP_ID;
    if (!mapId) {
        console.error('[IncidentMap] VITE_GOOGLE_MAPS_MAP_ID is not set in your .env file.');
        return;
    }

    map = new google.maps.Map(mapContainer.value, {
        center:            { lat: 14.5995, lng: 120.9842 },
        zoom:              12,
        mapId,
        mapTypeId:         google.maps.MapTypeId.ROADMAP,
        mapTypeControl:    false,
        streetViewControl: false,
        rotateControl:     false,
        fullscreenControl: true,
        zoomControl:       true,
        scaleControl:      true,
        styles: [
            { featureType: 'poi',     elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', elementType: 'labels', stylers: [{ visibility: 'simplified' }] },
        ],
    });

    updateMarkers();
};

onMounted(() => initMap());
onUnmounted(() => clearMarkers());

watch(() => props.incidents, () => updateMarkers(), { deep: true });
</script>

<template>
    <div ref="mapContainer" class="w-full h-full rounded-lg" />
</template>
