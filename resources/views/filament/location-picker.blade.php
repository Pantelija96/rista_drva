<div data-location-picker
    data-latitude="{{ $get('latitude') }}" data-longitude="{{ $get('longitude') }}"
    x-data="{ lat: $wire.entangle('data.latitude'), lng: $wire.entangle('data.longitude') }"
    x-on:ecosync-location="lat = $event.detail.lat; lng = $event.detail.lng"
    x-bind:data-latitude="lat" x-bind:data-longitude="lng"
    x-effect="$el.dispatchEvent(new CustomEvent('ecosync-coordinate-update', {detail:{lat:lat,lng:lng}}))">
    <div class="ecosync-picker-actions">
        <button type="button" x-on:click="lat=null;lng=null">Obriši lokaciju</button>
    </div>
    <p>Pomerite i uvećajte mapu, zatim kliknite na potvrđenu lokaciju. Marker možete prevući ili koordinate uneti ručno. Sačuvajte obrazac da biste upisali izmenu.</p>
    <div wire:ignore><x-leaflet-map :editable="true" :filters="false" /></div>
</div>
