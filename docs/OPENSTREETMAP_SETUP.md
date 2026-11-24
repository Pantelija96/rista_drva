# Leaflet i OpenStreetMap

Leaflet pruža interaktivnu mapu unutar postojećih Blade i Filament stranica. OpenStreetMap je izvor kartografskih podataka, a izabrani tile provajder isporučuje podlogu. Ova kombinacija podržava školske i pojedinačne lokacije bez promene WGS84 koordinata sačuvanih u MySQL bazi.

## Instalacija i konfiguracija

Zavisnosti `leaflet` 1.9.4 i `leaflet.markercluster` 1.5.3 instalirane su kroz npm i zaključane u package-lock.json. To su stabilne međusobno kompatibilne verzije izabrane pri ovoj implementaciji. Na novoj instalaciji dovoljno je:

```bash
npm ci
npm run build
```

Leaflet stilovi, ikone i clustering dodatak učitavaju se kroz Vite. `resources/js/maps.js` sadrži jednu zajedničku implementaciju, a `resources/views/components/leaflet-map.blade.php` omogućava izbor markera, centra, uvećanja, kategorija, filtera, klasterovanja i prilagođavanja prikaza markerima. Ista implementacija služi javnim stranicama i administratorskim biračima.

U `.env` podesite:

```dotenv
MAP_TILE_URL=https://tile.openstreetmap.org/{z}/{x}/{y}.png
MAP_ATTRIBUTION="&copy; OpenStreetMap contributors"
MAP_DEFAULT_LAT=44.6780
MAP_DEFAULT_LNG=20.7180
MAP_DEFAULT_ZOOM=11
MAP_MAX_ZOOM=19
```

Podešavanja se čitaju isključivo u `config/maps.php`. Posle izmene izvršite `php artisan config:clear`, a na produkciji obnovite konfiguracioni keš u sklopu deployment postupka. Početni centar kod Grocke određuje samo prikaz prazne mape. Ne upisuje lokaciju nijednom zapisu.

MAP_TILE_URL mora biti HTTPS XYZ tile adresa provajdera, sa oznakama `{z}`, `{x}` i `{y}`. Pri promeni provajdera podesite njegov URL, podržano najveće uvećanje i tekst dodatne atribucije. Neki provajderi zahtevaju pretplatu ili browser ključ u URL-u. Takav ključ ima ograničenja koja određuje provajder i vidljiv je u zahtevima pregledača. MAP_ATTRIBUTION je tekstualna oznaka provajdera. Povezana obavezna OpenStreetMap atribucija ostaje vidljiva nezavisno od te oznake.

## Pravila korišćenja podloge

Podrazumevani javni OSM tile servis koristi se direktno iz pregledača uz vidljivu atribuciju © OpenStreetMap contributors i link ka licenci. Pregledač šalje uobičajeni User-Agent i Referer i poštuje HTTP keš zaglavlja. Ne postavljajte Referrer-Policy koja uklanja Referer pri učitavanju podloge. Aplikacija ne menja zahteve u no-cache režim, ne proksira tile zahteve kroz Laravel, ne preuzima cele regione i nema offline ili bulk preuzimanje.

Mapa se inicijalizuje jednom po elementu. Promena filtera koristi već učitane markere. Otvaranje skrivenog panela ili promena dimenzija osvežava veličinu postojeće mape, bez ponovnog pravljenja. Uklonjeni Livewire elementi oslobađaju mapu i posmatrače.

Javni tile servis ima ograničen kapacitet i dostupnost bez garancije. Za veći produkcijski saobraćaj izaberite namenski ili upravljani OSM-kompatibilni tile servis sa odgovarajućim planom. Pre puštanja proverite pravila konkretnog provajdera. [Zvanična OSM pravila](https://operations.osmfoundation.org/policies/tiles/)

## Lokalna provera i rad osoblja

Pokrenite `php artisan serve`, otvorite početnu i `/nas-zeleni-trag` i proverite podlogu, vidljivu atribuciju i zoom tastere. Prazna baza daje mapu sa konfiguracionim centrom i porukom da potvrđene lokacije još nisu objavljene.

U administraciji napravite ili otvorite školu. Pomerite i uvećajte mapu, kliknite potvrđenu lokaciju i proverite koordinate. Prevucite marker i sačuvajte. Ponovo otvorite zapis da proverite sačuvanu tačku. Proverite ručni unos, promenu i brisanje obe koordinate. Isti postupak koristite za pojedinačno stablo i grupu. Zapis može biti sačuvan bez lokacije. Detaljan postupak je u `ADMIN_GUIDE.md`.

Javni endpoint `/mapa/podaci` podržava validirane parametre `category`, `city`, `school_id`, `graduation_year`, `species_id`, `adoption_status` i `search`. Nepoznate vrednosti kategorija/statusa i neispravni tipovi daju validacionu grešku. Odgovor sadrži samo potvrđene objavljene lokacije i eksplicitno izabrana javna polja. Javni interfejs filtrira već učitani skup, dok se isti filteri mogu koristiti i direktno na endpointu. Trenutna ograničenja su 2.000 škola, 2.000 grupa i 5.000 stabala. Sledeći korak pri većem obimu je učitavanje prema vidljivim granicama mape.

## Grupe i vizuelni klasteri

Škola je plavi školski marker. Stablo je zeleni marker drveta, usvojeno stablo amber marker sa srcem, a logička grupa tamnozeleni kvadrat sa simbolom više stabala. Boje dopunjuju različiti simboli, opisi, popup i linkovi ka profilima.

Logička grupa ima prijavljen ukupan broj i zaseban broj registrovanih pojedinačnih stabala. Grupa sa 30 prijavljenih i pet pojedinačnih zapisa predstavlja 30 stabala. Na udaljenom prikazu lokacija grupe zamenjuje njena pojedinačna stabla. Pri približavanju se prikazuju njihove potvrđene lokacije. Stabla bez koordinata ostaju bez markera.

Vizuelni kružni klaster privremeno objedinjuje bliske markere, sa brojem lokacija i mogućnošću približavanja. Taj broj nije prijavljeni broj fizičkih stabala grupe. Aplikacija ne računa zbir grupnih totala i pojedinačnih zapisa.

Pretraga adresa trenutno nije uključena. Osoblje koristi navigaciju mapom i ručne koordinate. Budući geokoding treba posebno odabrati i podesiti prema pravilima izabranog provajdera.

Dokumentacija biblioteka je na [Leaflet](https://leafletjs.com/) i [Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster).
