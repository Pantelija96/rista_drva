<x-filament-panels::page>
<p>Pregled objavljenih potvrđenih lokacija. Lokacije nacrta i pojedinačne koordinate uređuju se u resursima Škole, Stabla i Grupe stabala.</p>
<x-leaflet-map id="admin-overview-map" :markers="app(App\Services\PublicMapData::class)->markers()" />
</x-filament-panels::page>
