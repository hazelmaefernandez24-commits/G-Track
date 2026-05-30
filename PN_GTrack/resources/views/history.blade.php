@extends('layouts.app')

@section('title', 'Student Location History')
@section('subtitle', 'Timeline and map of student location logs')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    .history-container {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 24px;
        align-items: start;
        margin-top: 10px;
    }
    @media (max-width: 1024px) {
        .history-container {
            grid-template-columns: 1fr;
        }
    }
    .info-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        margin-bottom: 24px;
    }
    .map-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        position: sticky;
        top: 100px;
    }
    #history-map {
        height: 520px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        z-index: 10;
    }
    .history-table-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }
    .table-container {
        overflow-x: auto;
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-top: 12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        text-align: left;
        padding: 12px 16px;
        border-bottom: 2px solid var(--border-color);
        color: var(--text-muted);
        font-size: 12px;
        text-transform: uppercase;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 5;
    }
    td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
        color: var(--text-main);
    }
    tr:hover {
        background: #F8FAFC;
    }
    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-safe { background: #DCFCE7; color: #16A34A; }
    .badge-sos { background: #FEE2E2; color: #DC2626; }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
        transition: color 0.2s;
        border: 1px solid var(--border-color);
        background: #fff;
        padding: 8px 16px;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
    }
    .back-btn:hover {
        color: var(--text-main);
        background: #F8FAFC;
    }
    .btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 11px;
        display: inline-block;
        text-align: center;
    }
    .btn-primary { 
        background: var(--sidebar-bg); 
        color: #fff; 
    }
    .btn-primary:hover {
        background: var(--sidebar-hover);
    }
</style>
@endpush

@section('content')
<a href="/activity" class="back-btn">
    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
    Back to Student Activity
</a>

<div class="history-container">
    <div>
        <!-- Student Info Card -->
        <div class="info-card">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--text-main); border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                <i data-lucide="user" style="width: 20px; height: 20px; color: var(--sidebar-bg);"></i>
                {{ $student->name }}
            </h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Student ID</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">{{ $student->student_id }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Class</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">
                        <span class="badge" style="background:#EFF6FF; color:var(--sidebar-active);">{{ $student->class }}</span>
                    </div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Gender</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">{{ $student->gender }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Contact</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">
                        @if($student->contact)
                            <a href="tel:{{ $student->contact }}" style="color: var(--sidebar-bg); text-decoration: none; font-weight: 600;">{{ $student->contact }}</a>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Battery Level</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                        @if($student->battery_level !== null)
                            <div style="width: 24px; height: 12px; border: 1px solid #CBD5E1; border-radius: 2px; padding: 1px; display: inline-block;">
                                <div style="width: {{ $student->battery_level }}%; height: 100%; background: {{ $student->battery_level < 20 ? '#EF4444' : '#22C55E' }}; border-radius: 1px;"></div>
                            </div>
                            <span>{{ $student->battery_level }}%</span>
                        @else
                            <span style="color: var(--text-muted);">N/A</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Signal Status</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">{{ $student->signal_status ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- History Table Card -->
        <div class="history-table-card">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
                <i data-lucide="history" style="width: 20px; height: 20px; color: var(--sidebar-bg);"></i>
                Location History Logs
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Scroll through previous GPS pings. Click "Map" to jump and focus on the coordinates.</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Recorded At</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $index => $loc)
                        <tr id="row-{{ $loc->id }}" data-id="{{ $loc->id }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">
                            <td style="font-weight: 500;">{{ $loc->recorded_at ? $loc->recorded_at->format('M d, Y h:i A') : 'N/A' }}</td>
                            <td id="address-{{ $loc->id }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}" style="font-size: 13px; color: var(--text-main); min-width: 220px;">
                                <span style="color: var(--text-muted);">Loading address…</span>
                            </td>
                            <td>
                                <span class="badge {{ $loc->sos_status === 'help' ? 'badge-sos' : 'badge-safe' }}">
                                    {{ $loc->sos_status }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="focusOnLocation({{ $loc->latitude }}, {{ $loc->longitude }}, {{ $loc->id }})">
                                    Map
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                                No location history logs found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Map Card -->
    <div class="map-card">
        <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i data-lucide="map" style="width: 20px; height: 20px; color: var(--sidebar-bg);"></i>
            Location History Map
        </h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">The blue dashed line indicates the path/route taken by the student.</p>
        <div id="history-map"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    let map;
    let markers = {};
    let polyline;

    function initMap() {
        const locations = @json($locations);
        
        // Default coordinates (Cebu) if no locations are present
        let startCoords = [10.3157, 123.8854];
        let zoom = 13;

        if (locations.length > 0) {
            startCoords = [parseFloat(locations[0].latitude), parseFloat(locations[0].longitude)];
            zoom = 16;
        }

        map = L.map('history-map').setView(startCoords, zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        if (locations.length > 0) {
            const latlngs = [];
            
            locations.forEach((loc, index) => {
                const lat = parseFloat(loc.latitude);
                const lng = parseFloat(loc.longitude);
                const isLatest = index === 0;
                
                // Color configuration: Latest is green (or red if SOS), others are blue (or red if SOS)
                let fillColor = isLatest ? '#22C55E' : '#3B82F6';
                if (loc.sos_status === 'help') {
                    fillColor = '#EF4444';
                }

                const marker = L.circleMarker([lat, lng], {
                    radius: isLatest ? 10 : 7,
                    fillColor: fillColor,
                    color: '#FFFFFF',
                    weight: 2,
                    fillOpacity: 0.9,
                }).addTo(map);

                const statusLabel = loc.sos_status === 'help' 
                    ? '<span style="display:inline-block;padding:2px 8px;background:#FEE2E2;color:#DC2626;border-radius:4px;font-weight:700;font-size:12px;margin-top:4px;">🚨 Needs Help</span>' 
                    : '<span style="display:inline-block;padding:2px 8px;background:#DCFCE7;color:#16A34A;border-radius:4px;font-weight:700;font-size:12px;margin-top:4px;">✓ Safe</span>';

                loc.statusLabel = statusLabel;
                loc.isLatest = isLatest;

                marker.bindPopup(formatPopupContent(loc, null));

                markers[loc.id] = marker;
                // Add to path from oldest to newest (locations is descending, so we reverse for path drawing)
                latlngs.unshift([lat, lng]);
            });

            // Draw line connecting path
            polyline = L.polyline(latlngs, {
                color: '#3B82F6',
                weight: 3,
                dashArray: '5, 5',
                opacity: 0.7
            }).addTo(map);

            // Fit bounds to show all markers
            const bounds = L.latLngBounds(latlngs);
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
        }

        setTimeout(() => map.invalidateSize(), 350);

        if (locations.length > 0) {
            loadLocationAddresses(locations);
        }
    }

    function formatPopupContent(loc, address) {
        const lat = parseFloat(loc.latitude);
        const lng = parseFloat(loc.longitude);
        const locationText = address
            ? `<div style="color: #0F172A; font-weight:600; margin-top: 6px;">${address}</div>`
            : `<div style="color: #64748B; font-family: monospace; font-size:11px; margin-top: 6px;">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</div>`;

        const title = loc.sos_status === 'help'
            ? 'SOS Location'
            : (loc.isLatest ? 'Latest Location' : 'Historical Location');

        return `
            <div style="font-size: 13px; line-height: 1.5; min-width: 170px; font-family: 'Inter', sans-serif;">
                <div style="font-weight:700; margin-bottom:4px; color:#0F172A;">${title}</div>
                <div style="color: #64748B;">Time: ${new Date(loc.recorded_at).toLocaleString()}</div>
                ${locationText}
                <div>${loc.statusLabel}</div>
            </div>
        `;
    }

    async function reverseGeocodeCoordinates(lat, lng) {
        if (!lat || !lng) {
            throw new Error('Missing coordinates');
        }

        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1`;
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Geocoding request failed');
        }
        const data = await response.json();

        if (data.address) {
            const components = [];
            const priority = ['road', 'pedestrian', 'footway', 'cycleway', 'house_number', 'neighbourhood', 'suburb', 'city_district', 'city', 'town', 'village', 'county', 'state', 'country'];
            for (const key of priority) {
                if (data.address[key] && !components.includes(data.address[key])) {
                    components.push(data.address[key]);
                }
                if (components.length >= 3) break;
            }
            if (components.length > 0) {
                return components.join(', ');
            }
        }

        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    async function loadLocationAddresses(locations) {
        for (const loc of locations) {
            const addressCell = document.getElementById(`address-${loc.id}`);
            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);

            if (!addressCell || !lat || !lng) {
                if (addressCell) {
                    addressCell.innerHTML = '<span style="color: var(--text-muted);">Coordinates unavailable</span>';
                }
                continue;
            }

            try {
                const address = await reverseGeocodeCoordinates(lat, lng);
                loc.address = address;
                addressCell.innerHTML = `
                    <div style="font-weight:600;">${address}</div>
                    <div style="font-size:11px; color: #64748B; margin-top:4px;">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                `;

                const marker = markers[loc.id];
                if (marker) {
                    const popup = marker.getPopup();
                    if (popup) {
                        popup.setContent(formatPopupContent(loc, address));
                    }
                }
            } catch (error) {
                addressCell.innerHTML = '<span style="color: var(--text-muted);">Unable to resolve address</span>';
            }

            await new Promise(resolve => setTimeout(resolve, 500));
        }
    }

    function focusOnLocation(lat, lng, id) {
        if (map) {
            map.setView([lat, lng], 18);
            const marker = markers[id];
            if (marker) {
                marker.openPopup();
            }
            
            // Highlight row
            document.querySelectorAll('tr').forEach(r => r.style.background = '');
            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.style.background = '#EFF6FF'; // light blue highlight
            }
        }
    }

    window.addEventListener('DOMContentLoaded', initMap);
</script>
@endpush
