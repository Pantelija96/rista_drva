import L from 'leaflet';

// The clustering plugin extends the same Leaflet instance used by every map.
window.L = L;
const clusteringReady = import('leaflet.markercluster');
const instances = new Map();
const symbols = { school: 'school', tree: 'tree', adopted: 'heart', group: 'layer-group', position: 'location-dot' };
const en = document.documentElement.lang === 'en';
const t = (sr, english) => en ? english : sr;

function markerIcon(type) {
    const category = Object.hasOwn(symbols, type) ? type : 'position';
    return L.divIcon({
        className: `ecosync-marker-icon ecosync-marker-${category}`,
        html: `<span aria-hidden="true"><i class="fas fa-${symbols[category]}" aria-hidden="true"></i></span>`,
        iconSize: [40, 40], iconAnchor: [20, 38], popupAnchor: [0, -34],
    });
}

function validPosition(lat, lng) {
    return lat !== null && lng !== null && lat !== '' && lng !== '' &&
        Number.isFinite(Number(lat)) && Number.isFinite(Number(lng)) &&
        Number(lat) >= -90 && Number(lat) <= 90 && Number(lng) >= -180 && Number(lng) <= 180;
}

function detailContent(record) {
    const content = document.createElement('div');
    const title = document.createElement('strong'); title.textContent = record.title;
    const description = document.createElement('p'); description.textContent = record.subtitle;
    content.append(title, description);
    if (record.planted_at) {
        const date = document.createElement('p'); date.textContent = `${t('Datum sadnje:', 'Planting date:')} ${record.planted_at}`; content.append(date);
    }
    if (record.graduation_year) {
        const generation = document.createElement('p'); generation.textContent = `${t('Generacija', 'Graduating class')} ${record.graduation_year}`; content.append(generation);
    }
    const link = document.createElement('a');
    const url = new URL(record.url, location.href);
    if (url.origin === location.origin && ['http:', 'https:'].includes(url.protocol)) {
        link.href = url.href; link.textContent = t('Pogledaj profil', 'View profile'); content.append(link);
    }
    return content;
}

function addAttribution(map, settings) {
    // Required OSM attribution stays visible even when a provider label is customized.
    const osm = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors';
    map.attributionControl.addAttribution(osm);
    const provider = String(settings.attribution || '');
    if (provider && !['&copy; OpenStreetMap contributors', '© OpenStreetMap contributors'].includes(provider)) {
        // Provider labels are configuration text, not executable HTML.
        const label = document.createElement('span'); label.textContent = provider;
        map.attributionControl.addAttribution(label.innerHTML);
    }
}

async function initialize(container, state) {
    if (state.started || !container.isConnected || !state.canvas.clientWidth || !state.canvas.clientHeight) return;
    state.started = true;
    const settings = JSON.parse(container.querySelector('[data-map-settings]').textContent);
    const status = container.querySelector('[data-map-status]');
    try {
        const map = L.map(state.canvas, { maxZoom: settings.maxZoom, scrollWheelZoom: false });
        state.map = map;
        map.setView([settings.center.lat, settings.center.lng], settings.zoom);
        addAttribution(map, settings);
        const tiles = L.tileLayer(settings.tileUrl, { maxZoom: settings.maxZoom, updateWhenIdle: true, keepBuffer: 1 });
        tiles.on('tileerror', () => { status.textContent = t('Podloga mape trenutno nije dostupna. Markere i profile i dalje možete pregledati.', 'Map tiles are temporarily unavailable. You can still browse markers and profiles.'); });
        tiles.addTo(map);
        if (settings.editable) {
            setupPicker(container, state);
            status.textContent = t('Kliknite na mapu ili prevucite marker. Koordinate se čuvaju potvrdom obrasca.', 'Click the map or drag the marker. Submit the form to save coordinates.');
            return;
        }
        state.abort = new AbortController();
        const data = settings.markers ?? (await fetch(settings.url, {
            signal: state.abort.signal, headers: { Accept: 'application/json' },
        }).then(response => {
            if (!response.ok) throw new Error('Map data unavailable');
            return response.json();
        })).markers;
        if (!container.isConnected) return;
        if (settings.clustering) await clusteringReady;
        if (!container.isConnected) return;
        const layer = settings.clustering ? L.markerClusterGroup({
            animate: false, removeOutsideVisibleBounds: true, maxClusterRadius: 55,
            iconCreateFunction: cluster => L.divIcon({
                className: 'ecosync-visual-cluster', iconSize: [44, 44],
                html: `<span>${cluster.getChildCount()}</span><span class="ecosync-sr-only">${t('lokacija', 'locations')}</span>`,
            }),
        }) : L.featureGroup();
        layer.addTo(map);
        const markers = data.filter(record => validPosition(record.lat, record.lng) && settings.categories.includes(record.type)).map(record => {
            const marker = L.marker([record.lat, record.lng], { icon: markerIcon(record.type), title: record.title, alt: record.title, keyboard: true });
            marker.bindPopup(() => detailContent(record));
            marker.on('click', () => {
                const preview = container.querySelector('[data-map-preview]');
                preview.replaceChildren(detailContent(record)); preview.hidden = false;
            });
            return { record, marker };
        });
        setupFilters(container, map, layer, markers);
        if (settings.fitToMarkers && markers.length) {
            map.fitBounds(L.latLngBounds(markers.map(entry => entry.marker.getLatLng())), { padding: [25, 25], maxZoom: Math.min(settings.maxZoom, 16) });
        }
    } catch (error) {
        if (error.name !== 'AbortError' && container.isConnected) {
            status.textContent = t('Mapa trenutno nije dostupna. Pokušajte ponovo kasnije ili otvorite javne profile.', 'The map is temporarily unavailable. Please try later or open the public profiles.');
        }
    }
}

function setupPicker(container, state) {
    const root = container.closest('[data-location-picker]');
    const map = state.map;
    let marker;
    function update(lat, lng, center = false) {
        if (!validPosition(lat, lng)) {
            marker?.remove(); marker = undefined;
            return;
        }
        const position = L.latLng(Number(lat), Number(lng));
        if (!marker) {
            marker = L.marker(position, { icon: markerIcon('position'), draggable: true, autoPan: true, title: t('Izabrana lokacija', 'Selected location'), alt: t('Izabrana lokacija', 'Selected location') }).addTo(map);
            marker.on('dragend', () => emit(marker.getLatLng()));
        } else marker.setLatLng(position);
        if (center) map.panTo(position, { animate: false });
    }
    function emit(position) {
        position = position.wrap();
        update(position.lat, position.lng);
        root.dispatchEvent(new CustomEvent('ecosync-location', {
            detail: { lat: Number(position.lat.toFixed(7)), lng: Number(position.lng.toFixed(7)) },
        }));
    }
    map.on('click', event => emit(event.latlng));
    const change = event => update(event.detail.lat, event.detail.lng, true);
    root.addEventListener('ecosync-coordinate-update', change);
    state.cleanup = () => root.removeEventListener('ecosync-coordinate-update', change);
    update(root.dataset.latitude || null, root.dataset.longitude || null, true);
}

function setupFilters(container, map, layer, markers) {
    const controls = [...container.querySelectorAll('[data-map-filter]')];
    const search = container.querySelector('[data-map-search]');
    const links = container.querySelector('[data-map-links]');
    const status = container.querySelector('[data-map-status]');
    const preview = container.querySelector('[data-map-preview]');
    const options = {
        city: record => [record.city, record.municipality].filter(Boolean).map(value => [value, value]),
        school_id: record => record.school_id ? [[record.school_id, `${record.school_name} — ${record.city || ''}`]] : [],
        graduation_year: record => (record.graduation_years || [record.graduation_year]).filter(Boolean).map(year => [year, year]),
        species_id: record => record.species_id ? [[record.species_id, record.species]] : [],
    };
    controls.forEach(control => {
        const values = options[control.dataset.mapFilter];
        if (!values) return;
        const distinct = new Map(markers.flatMap(({ record }) => values(record)));
        [...distinct].sort((a, b) => String(a[1]).localeCompare(String(b[1]), en ? 'en' : 'sr')).forEach(([value, label]) => control.add(new Option(label, value)));
    });
    function refresh(fit = false) {
        const filters = Object.fromEntries(controls.map(control => [control.dataset.mapFilter, control.value]));
        const term = (search?.value || '').toLocaleLowerCase(en ? 'en' : 'sr');
        const filtered = markers.filter(({ record }) => {
            if (!`${record.title} ${record.subtitle}`.toLocaleLowerCase(en ? 'en' : 'sr').includes(term)) return false;
            if (filters.category && (filters.category === 'tree' ? !['tree', 'adopted'].includes(record.type) : record.type !== filters.category)) return false;
            if (filters.city && record.city !== filters.city && record.municipality !== filters.city) return false;
            if (filters.graduation_year && !(record.graduation_years || [record.graduation_year]).map(String).includes(filters.graduation_year)) return false;
            for (const field of ['school_id', 'species_id', 'adoption_status']) {
                if (filters[field] && String(record[field] ?? '') !== filters[field]) return false;
            }
            return true;
        });
        const groupIds = new Set(filtered.filter(({ record }) => record.type === 'group').map(({ record }) => record.id));
        const individualGroupIds = new Set(filtered.filter(({ record }) => record.group_id).map(({ record }) => record.group_id));
        const detailed = map.getZoom() >= 14;
        // A logical group replaces its individual locations at overview zoom. Cluster counts refer only to displayed locations.
        const visible = filtered.filter(({ record }) => record.type === 'group'
            ? !detailed || !individualGroupIds.has(record.id)
            : !record.group_id || !groupIds.has(record.group_id) || detailed);
        layer.clearLayers(); layer.addLayers ? layer.addLayers(visible.map(entry => entry.marker)) : visible.forEach(entry => layer.addLayer(entry.marker));
        links.replaceChildren(); preview.hidden = true;
        filtered.forEach(({ record }) => {
            const link = document.createElement('a'); link.href = record.url; const icon = document.createElement('i'); icon.className = `fas fa-${symbols[record.type]}`; icon.setAttribute('aria-hidden', 'true'); link.append(icon, document.createTextNode(` ${record.title}`)); links.append(link);
        });
        status.textContent = markers.length ? `${filtered.length} ${t('zapisa sa potvrđenom lokacijom. Broj zapisa nije ukupan broj posađenih stabala.', filtered.length === 1 ? 'record with a confirmed location. This is not the total number of planted trees.' : 'records with confirmed locations. This is not the total number of planted trees.')}` : t('Još nema objavljenih škola ili stabala sa potvrđenom lokacijom.', 'No published schools or trees have confirmed locations yet.');
        if (markers.length && !filtered.length) status.textContent = t('Nema potvrđenih lokacija za izabrane filtere.', 'No confirmed locations match your filters.');
        if (fit && visible.length) map.fitBounds(L.latLngBounds(visible.map(entry => entry.marker.getLatLng())), { padding: [25, 25], maxZoom: Math.min(map.getMaxZoom(), 16) });
    }
    search?.addEventListener('input', () => refresh());
    search?.addEventListener('change', () => refresh(true));
    controls.forEach(control => control.addEventListener('change', () => refresh(true)));
    container.querySelector('[data-map-reset]')?.addEventListener('click', () => { controls.forEach(c => c.value = ''); if(search) search.value = ''; map.closePopup(); refresh(true); });
    container.querySelector('[data-map-clear]')?.addEventListener('click', () => { map.closePopup(); preview.hidden = true; });
    map.on('zoomend', () => refresh());
    refresh();
}

function scan() {
    for (const [container, state] of instances) {
        if (!container.isConnected) {
            state.abort?.abort(); state.cleanup?.(); state.resize.disconnect(); state.map?.remove(); instances.delete(container);
        }
    }
    document.querySelectorAll('[data-leaflet-map]').forEach(container => {
        if (instances.has(container)) return;
        const state = { canvas: container.querySelector('.ecosync-leaflet-map'), started: false };
        state.resize = new ResizeObserver(() => {
            if (!state.started) initialize(container, state);
            else if (state.map && state.canvas.clientWidth && state.canvas.clientHeight) state.map.invalidateSize({ pan: false });
        });
        instances.set(container, state); state.resize.observe(state.canvas);
        initialize(container, state);
    });
}
scan();
document.addEventListener('livewire:navigated', scan);
for (const event of ['shown.bs.modal', 'shown.bs.tab', 'shown.bs.collapse']) {
    document.addEventListener(event, () => instances.forEach(state => state.map?.invalidateSize({ pan: false })));
}
new MutationObserver(records => {
    if (records.some(record => [...record.addedNodes, ...record.removedNodes].some(node => node.nodeType === 1 && (node.matches('[data-leaflet-map]') || node.querySelector('[data-leaflet-map]'))))) scan();
}).observe(document.documentElement, { childList: true, subtree: true });
