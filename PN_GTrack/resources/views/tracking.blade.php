@extends('layouts.app')

@section('title', 'Real-Time Tracking')
@section('subtitle', 'Live student location monitoring')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    .map-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }

    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .map-title h2 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .map-title p {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-icon {
        color: var(--text-muted);
    }

    .filter-select {
        padding: 8px 36px 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-main);
        background-color: #FFFFFF;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        outline: none;
        cursor: pointer;
    }

    .filter-select:focus {
        border-color: var(--sidebar-active);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }

    #map {
        height: 550px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        z-index: 10; /* Keep it below header */
    }

    /* Custom Leaflet popup */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .leaflet-popup-content {
        margin: 16px;
        font-family: 'Inter', sans-serif;
    }
</style>
@endpush

@section('content')

<div class="map-card">
    <div class="map-header">
        <div class="map-title">
            <h2>Student Location Map</h2>
            <p>Live student location updates with safety status</p>
        </div>
        
        <div class="filter-group">
            <i data-lucide="filter" class="filter-icon" style="width: 18px; height: 18px;"></i>
            <select id="class-filter" class="filter-select">
                <option>All Classes</option>
                <option>2026</option>
                <option>2027</option>
                <option>2028</option>
            </select>
        </div>
    </div>

    <div id="map"></div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    let map;
    let markers = [];
    let selectedClass = 'All Classes';

    function getColorByGender(gender) {
        if (!gender) return '#64748B'; // muted
        return gender.toString().toLowerCase() === 'female' ? '#EF4444' : '#3B82F6';
    }

    function showPopup(location) {
        const student = location.student || {};
        const recorded = location.recorded_at ? new Date(location.recorded_at).toLocaleString() : 'Unknown';
        const sos = location.sos_status || student.sos_status || 'safe';

        const sosLabel = sos === 'help' 
            ? '<span style="display:inline-block;padding:2px 8px;background:#FEE2E2;color:#DC2626;border-radius:4px;font-weight:700;font-size:12px;margin-top:4px;">🚨 Needs Help</span>' 
            : '<span style="display:inline-block;padding:2px 8px;background:#DCFCE7;color:#16A34A;border-radius:4px;font-weight:700;font-size:12px;margin-top:4px;">✓ Safe</span>';

        return `
            <div style="font-size:13px; line-height:1.5; min-width:200px;">
                <div style="font-size:15px; font-weight:700; color:#0F172A; margin-bottom:4px;">${student.name || 'Unknown Student'}</div>
                <div style="color:#64748B; margin-bottom:8px;">ID: ${student.student_id || 'N/A'} • Class: ${student.class || 'N/A'}</div>
                <div style="display:flex; justify-content:space-between; border-top:1px solid #E2E8F0; padding-top:8px; margin-top:8px;">
                    <span style="color:#64748B;">Latest Update:</span>
                    <span style="font-weight:500;">${recorded}</span>
                </div>
                <div>${sosLabel}</div>
            </div>
        `;
    }

    function clearMarkers() {
        markers.forEach(marker => marker.remove());
        markers = [];
    }

    function loadLocations() {
        const urlParams = new URLSearchParams(window.location.search);
        const focusStudentId = urlParams.get('student_id');

        const url = selectedClass === 'All Classes' ? '/api/location/all' : `/api/location/all?class=${selectedClass}`;
        
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                clearMarkers();
                if (!Array.isArray(data) || data.length === 0) return;

                const bounds = [];
                let focusMarker = null;

                data.forEach(loc => {
                    const lat = parseFloat(loc.latitude);
                    const lng = parseFloat(loc.longitude);
                    if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                    // Make SOS markers pulse or stand out
                    let radius = 8;
                    let color = '#FFFFFF';
                    let weight = 2;
                    let isSOS = (loc.sos_status === 'help' || (loc.student && loc.student.sos_status === 'help'));
                    
                    if (isSOS) {
                        radius = 10;
                        color = '#EF4444'; // Red border
                        weight = 3;
                    }

                    const marker = L.circleMarker([lat, lng], {
                        radius: radius,
                        fillColor: getColorByGender(loc.student?.gender),
                        color: color,
                        weight: weight,
                        fillOpacity: 0.9,
                    }).addTo(map);

                    marker.bindPopup(showPopup(loc));
                    markers.push(marker);
                    bounds.push([lat, lng]);

                    if (focusStudentId && (loc.student_id == focusStudentId || (loc.student && loc.student.student_id == focusStudentId))) {
                        focusMarker = marker;
                    }
                });

                if (focusMarker) {
                    map.setView(focusMarker.getLatLng(), 18);
                    focusMarker.openPopup();
                } else if (bounds.length && markers.length > 0) {
                    // Only fit bounds if we aren't focused on a specific student, and don't do it constantly if not needed
                    // map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                }
            })
            .catch(err => console.error('Error loading location data:', err));
    }

    function initMap() {
        // Cebu coordinates
        map = L.map('map').setView([10.3157, 123.8854], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        document.getElementById('class-filter').addEventListener('change', function () {
            selectedClass = this.value;
            loadLocations();
            
            // Recenter/fit bounds when filter changes
            setTimeout(() => {
                if(markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 16 });
                }
            }, 500);
        });

        loadLocations();
        setInterval(loadLocations, 15000); // Refresh map every 15s

        setTimeout(() => map.invalidateSize(), 350);
    }

    window.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
