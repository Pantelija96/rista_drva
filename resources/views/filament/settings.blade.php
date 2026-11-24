<x-filament-panels::page>
<p>Servisna podešavanja uređuje odgovorna tehnička osoba u .env datoteci.</p>
<ul>
<li>Mapa — Leaflet i OpenStreetMap; izabrani tile provajder podesite kroz MAP_TILE_URL.</li>
<li>Pošta — {{ config('mail.default') === 'log' ? 'Samo dnevnik, email se ne isporučuje' : 'Transport je izabran; proverite isporuku' }}</li>
<li>Primalac obaveštenja — {{ config('ecosync.notification_email') ? 'Podešen' : 'Nije podešen' }}</li>
</ul>
</x-filament-panels::page>
