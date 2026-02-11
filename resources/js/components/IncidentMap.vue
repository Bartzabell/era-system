<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface IncidentMarker {
    id: number;
    lat: number;
    lng: number;
    severity: string;
    status: string;
    incident_type: string;
    emergency_type: string;
    barangay: string;
    casualty_count: number;
    created_at: string;
}

const props = defineProps<{
    incidents: IncidentMarker[];
}>();

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
const markers: L.Marker[] = [];

const getSeverityColor = (severity: string) => {
    switch (severity?.toLowerCase()) {
        case 'critical':
            return '#dc2626'; // red-600
        case 'high':
            return '#ea580c'; // orange-600
        case 'medium':
            return '#f59e0b'; // amber-500
        case 'low':
            return '#eab308'; // yellow-500
        default:
            return '#6b7280'; // gray-500
    }
};

const createCustomIcon = (severity: string) => {
    const color = getSeverityColor(severity);

    return L.divIcon({
        className: 'custom-incident-marker',
        html: `
            <div style="
                background-color: ${color};
                width: 32px;
                height: 32px;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                border: 3px solid white;
                box-shadow: 0 3px 10px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div style="
                    transform: rotate(45deg);
                    color: white;
                    font-size: 16px;
                    font-weight: bold;
                ">🔥</div>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
};

const initMap = () => {
    if (!mapContainer.value) return;

    // Initialize map centered on Philippines
    map = L.map(mapContainer.value).setView([14.5995, 120.9842], 12);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    // Add markers
    updateMarkers();
};

const updateMarkers = () => {
    if (!map) return;

    // Clear existing markers
    markers.forEach(marker => marker.remove());
    markers.length = 0;

    // Add new markers
    if (props.incidents && props.incidents.length > 0) {
        const bounds: L.LatLngBoundsExpression = [];

        props.incidents.forEach((incident) => {
            const marker = L.marker([incident.lat, incident.lng], {
                icon: createCustomIcon(incident.severity)
            });

            const popupContent = `
                <div style="font-family: system-ui; min-width: 200px;">
                    <h3 style="font-weight: bold; font-size: 16px; margin-bottom: 8px; color: ${getSeverityColor(incident.severity)}">
                        ${incident.incident_type}
                    </h3>
                    <div style="font-size: 13px; line-height: 1.6;">
                        <p><strong>Emergency:</strong> ${incident.emergency_type}</p>
                        <p><strong>Barangay:</strong> ${incident.barangay}</p>
                        <p><strong>Severity:</strong> <span style="color: ${getSeverityColor(incident.severity)}; font-weight: 600;">${incident.severity}</span></p>
                        <p><strong>Status:</strong> ${incident.status}</p>
                        ${incident.casualty_count ? `<p><strong>Casualties:</strong> ${incident.casualty_count}</p>` : ''}
                        <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">${incident.created_at}</p>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            marker.addTo(map!);
            markers.push(marker);
            bounds.push([incident.lat, incident.lng]);
        });

        // Fit map to show all markers
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
};

onMounted(() => {
    initMap();
});

watch(() => props.incidents, () => {
    updateMarkers();
}, { deep: true });
</script>

<template>
    <div ref="mapContainer" class="w-full h-full rounded-lg" />
</template>

<style>
/* Fix Leaflet marker icons */
.leaflet-default-icon-path {
    background-image: url('https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png');
}

.custom-incident-marker {
    background: transparent;
    border: none;
}
</style>
