# EcoSync — Zelena diploma

Laravel 13.43 i Filament 5.12 proširuju postojeći Porto sajt. Javni deo zadržava šest stranica, uz profile škola, generacija, stabala, grupa, vrsta i albuma. Podaci dolaze iz MySQL baze.

## Pokretanje

Potrebni su PHP 8.3 ili noviji kompatibilan sa Laravelom 13, Composer 2, Node.js 20.19+ iz grane 20 ili 22.12+ sa npm-om, i MySQL sa InnoDB tabelama. PHP mora imati PDO MySQL, PDO SQLite za testove, intl, mbstring, fileinfo, DOM, XML, curl, OpenSSL, GD i standardne Laravel ekstenzije.

```bash
composer install
cp -n .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan storage:link
```

U `.env` podesite DB_*, MAP_* i SMTP parametre prema posebnim uputstvima. Na već podešenoj instalaciji sačuvajte postojeći APP_KEY. Javne stranice zahtevaju migrirane tabele.

Pre migracije pročitajte `docs/DATABASE_SETUP.md`, proverite izabranu bazu, kolizije i obnovljivu rezervnu kopiju.

```bash
php artisan ecosync:database-inspect
php artisan migrate:status
php artisan migrate --pretend
php artisan migrate
php artisan db:seed --class=SiteStatisticSeeder
php artisan ecosync:create-admin
php artisan serve
```

Seeder unosi samo pet potvrđenih istorijskih statistika ako ne postoje. Postojeće ručno izmenjene vrednosti ostaju sačuvane. Broj održanih sadnji nije poznat i nije unapred unet. Administracija je `/admin`. Prvi administrator se kreira interaktivnom komandom, bez podrazumevane lozinke. Za razvojni Vite server pokrenite `npm run dev` u drugom terminalu.

## Struktura

- `app/Models` i `database/migrations` sadrže domenske modele, veze, indekse i aditivne migracije.
- `app/Services` sadrži odobravanje prijava, masovni unos, slanje obaveštenja i eksplicitno izabrane javne podatke mape.
- `app/Filament` sadrži administrativne resurse, zavisne izbore, ponovo upotrebljiv izbor lokacije i kontrolnu tablu.
- `app/Policies` ograničava rad sa podacima na administratore. Korisnici bez is_admin oznake nemaju pristup panelu.
- `resources/views/layouts`, `partials` i `pages` zadržavaju Porto strukturu. `profiles` sadrži nove detaljne stranice.
- `resources/js/maps.js` učitava Leaflet i Leaflet.markercluster kroz Vite. `config/maps.php` određuje tile URL, atribuciju i početni prikaz kod Grocke. Zajednički Blade prikaz je `resources/views/components/leaflet-map.blade.php`.
- `public/assets` sadrži postojeće Porto resurse, ikone i ilustracije. Vite obrađuje CSS/JS aplikacije. Filament ima sopstvene stilove, bez promene javnog Porto interfejsa.
- Galerijske fotografije koriste Laravel public disk i `public/storage` vezu. Administratori mogu postavljati isključivo fotografije odobrene za javnu objavu. Izmena i brisanje zapisa čiste fajlove nakon potvrđene transakcije, uz proveru drugih referenci.

## Rute

| URL | Sadržaj |
| --- | --- |
| `/`, `/nasa-misija`, `/pridruzi-se`, `/nas-zeleni-trag`, `/galerija`, `/kontakt` | Postojeće javne stranice |
| POST `/pridruzi-se` | Prijava škole, CSRF, honeypot i ograničenje učestalosti |
| `/mapa/podaci` | JSON objavljenih potvrđenih lokacija, bez privatnih podataka |
| `/skole/{slug}` | Profil objavljene škole |
| `/skole/{slug}/generacije/{id}` | Profil generacije te škole |
| `/stabla/{public_identifier}` | Profil javnog stabla |
| `/grupe-stabala/{slug}` | Profil grupe i odvojeni prijavljeni/pojedinačni brojevi |
| `/vrste-drveca/{slug}` | Botanički profil objavljene vrste |
| `/galerija/{slug}` | Objavljene fotografije albuma i lightbox |
| `/admin` | Zaštićena administracija |

Stare `.html` adrese zadržavaju trajna preusmerenja.

## Konfiguracija i provere

Primeri promenljivih su u `.env.example`. Pristupne podatke ne stavljajte u repozitorijum. U produkciji postavite `APP_ENV=production`, `APP_DEBUG=false`, stvarni HTTPS `APP_URL` i `SESSION_SECURE_COOKIE=true`. Podesite veb server da služi samo `public/`. Zaštitite i ograničite rok čuvanja logova i rezervnih kopija jer log mail transport sadrži podatke iz prijava.

```bash
php artisan route:list
php artisan migrate:status
php artisan test
npm run build
php artisan view:cache
```

Testovi silom biraju SQLite `:memory:` pre podizanja aplikacije i odbijaju drugačiju test vezu. Nikada ne pokrećite testove sa ručno zamenjenom produkcijskom vezom. Testovi proveravaju javni interfejs, pravila pristupa, prijave i odobravanje, veze, masovni unos, privatnost mapa, galerije i statistiku.

## Faza 2

Školske prijave, kontakt i podrška čuvaju se u bazi. Trajni email outbox podržava ponovne pokušaje i scheduler sa opcionim queue worker-om. Usvajanje pojedinačnih stabala uključuje rezervaciju, email potvrdu i obavezno administratorsko odobravanje. Staratelji bez naloga dobijaju kratkotrajne jednokratne veze za negu, privatne fotografije i podešavanja. Javno se objavljuju samo odobreni podaci i izbor imena prema saglasnosti.

Porto, šest osnovnih stranica, Leaflet/OpenStreetMap, postojeće adrese, partneri i ručno uređivane statistike ostaju sačuvani. Nema plaćanja. Mape ne zahtevaju Google ključ. Javni JSON je ograničen na 2.000 škola, 2.000 grupa i 5.000 stabala; za veći obim planirati upit po vidljivom području.

Pročitajte [email podešavanja](docs/MAIL_SETUP.md), [usvajanje](docs/TREE_ADOPTION.md), [negu](docs/TREE_MAINTENANCE.md) i [izveštaj testiranja](docs/E2E_TEST_REPORT.md). Pre javnog rada neophodni su HTTPS, ispravan APP_URL, SMTP pošiljalac i proveren inbox, cron za scheduler, nadzor obrade i rezervne kopije. Ne menjajte postojeći APP_KEY.

Pre ove faze izvorni kod je arhiviran u `/tmp/ecosync-before-backend-20261122.tar.gz`. Ranija kompletna frontend arhiva je u susednom direktorijumu projekta.

Uputstva za osoblje su u `docs/ADMIN_GUIDE.md`, a servisna podešavanja u `docs/OPENSTREETMAP_SETUP.md`, `docs/MAIL_SETUP.md` i `docs/DATABASE_SETUP.md`.

## Istorija prethodne faze

Primenjeno je 12 migracija na prethodno proverenu praznu udaljenu MySQL bazu. Uneto je pet potvrđenih statistika. U zajedničkoj bazi nisu napravljeni testni administratori, škole, prijave ni lokacije. Prošlo je 46 automatskih testova sa 420 provera. Chrome provera koristila je zasebnu SQLite test bazu za prijavu škole, administratorsku prijavu, čuvanje i brisanje koordinata, galeriju, lightbox, mobilni meni i CSRF odbijanje. Mape koriste Leaflet i OSM tile provajder. Stvarna SMTP isporuka čeka konfiguraciju.

Leaflet/OpenStreetMap zamena koristi postojeće WGS84 koordinate i ne zahteva migraciju baze. Chrome je potvrdio stvarne OSM tile odgovore i atribuciju, markere svih kategorija, klasterovanje, filtere, popup i detaljne prikaze, mobilne dimenzije i Filament upis posle klika, prevlačenja, ručnog unosa i brisanja. Testni sadržaj mape nalazi se samo u izolovanoj SQLite bazi.

## Provera faze 2

Završna automatska provera: 67 testova i 592 assertions. Osam aditivnih migracija primenjeno je nakon provere obnovljene kopije; postojeći udaljeni podaci su očuvani. Aktuelni obim i browser ograničenja navedeni su u docs/E2E_TEST_REPORT.md.

## Faza 3 — srpski i engleski

Javni deo koristi /sr i /en, zajedničke Blade prikaze i Laravel jezičke datoteke. Stare adrese preusmeravaju na srpsku verziju; /admin ostaje nezavisan. Urednički English tekst je opcion i ima eksplicitan srpski fallback. Outbox pamti jezik primaoca i pri ponovnom pokušaju. Porto Font Awesome Free 6.4.0 zamenjuje emoji markere. Mape imaju reset filtera i uklanjanje izbora, a obrasci informativne korake.

Uputstva: [MULTILANGUAGE](docs/MULTILANGUAGE.md), [UI/UX](docs/UI_UX_GUIDELINES.md). Existing English editorial records need manual translation/approval in Filament; no automatic translations were persisted. Stvarna SMTP dostava i dalje zahteva serversku proveru.

## Faza 4 — sertifikati, QR i UI

Aktivna, emailom potvrđena i administrativno odobrena usvajanja imaju privatno preuzimanje dvojezičnog A4 PDF sertifikata kroz postojeću sesiju staratelja ili administraciju. Javno objavljena stabla imaju lokalno generisan QR, Filament pregled/SVG preuzimanje i PDF oznaku za štampu. Referenca koristi postojeći javni UUID; nema novih migracija. Dompdf i BaconQrCode rade lokalno, bez plaćenih API-ja.

Desktop jezički izbor je u gornjem redu uz slogan; mobilni ostaje u meniju. Spoljni border početne mape uklonjen je. Primer uplatnice je podrazumevano sakriven pomoću `ECOSYNC_SHOW_PAYMENT_INSTRUCTIONS=false`, a podrška koristi punu širinu. Aktivacija, prava pristupa, prevodi i štampa: [CERTIFICATES_AND_QR](docs/CERTIFICATES_AND_QR.md). Pre štampanja QR-a obavezno podesite stvarni HTTPS APP_URL. Zvanični logo i dalje zahteva odobren resurs organizacije; koristi se postojeća biljna oznaka.
