# E2E izveštaj — 23.11.2026.

## Okruženja i sigurnost

Početno stanje ponovo je provereno: 46 testova, 420 provera. Završni automatski rezultat: 67 testova, 592 provere, svi uspešni. PHPUnit i TestCase pre boot-a prisilno postavljaju SQLite :memory: i odbijaju drugu test vezu. Testovi ne šalju stvarni email. Izmišljeni podaci postoje samo u izolovanim bazama.

Lokalni MySQL 8.4: stvarni dump udaljene baze obnovljen je u zasebnoj privremenoj bazi; sadržaj svih 12 originalnih tabela potvrđen sažecima i brojevima redova. Osam aditivnih migracija prošlo je bez promene originalnih redova. U drugoj lokalnoj MySQL bazi dva procesa istovremeno su tražila isto stablo: jedan prihvaćen, drugi blokiran. Paralelna odobravanja ostavila su jedno aktivno usvajanje i jedan događaj obaveštenja. Posle provera migracije su primenjene na udaljenoj bazi uz ponovnu potvrdu neizmenjenosti originalnih podataka. Testni sadržaj tamo nije unošen.

## Pokriveni tokovi

LifecycleTest pokriva prijavu škole, validaciju, outbox, administratora, odobravanje, školu, generaciju 2026, grupu 20, tri pojedinačna stabla, različite test koordinate, javnu mapu bez dupliranja, album sa dve slike i izmenu javne statistike.

AdoptionMaintenanceTest proverava rezervaciju, kriptovane kontakte, hash tokene, POST potvrdu, istek, ponovnu upotrebu i zamenu tokena, obavezno odobravanje, idempotentnost, završetak, anonimnost/inicijale, privatnost JSON-a i profila, pristup staratelja, isteke sesije, tuđe stablo, validaciju fotografija, privatno posluživanje, moderaciju, javni tekst, promenu tipa i privatnosti, otkazivanje, potvrdu nove adrese, uklanjanje podataka, Filament akcije, upite i sprečavanje duplikata. Mail testovi proveravaju očuvanje prijave pri grešci, retry i oporavak prekinute obrade.

Postojeći testovi proveravaju šest zasebnih javnih stranica, stare URL-ove, pristup panelu, veze, grupne brojeve, galeriju i kontrolisane statistike.

## Pronađeno i ispravljeno

- Provera sesije koristila je sistemsko umesto Laravel vremena: koristi now(), test isteka prolazi.
- Model za outbox zahtevao je eksplicitno ime tabele mail_outbox.
- Redovna email obrada sada čuva prijavu nezavisno od SMTP greške; log transport nije označen kao dostava.
- Foto moderacija zahteva zaseban javni tekst i izričit izbor pojedinačnih slika.
- Promena emaila zahteva potvrdu na novoj adresi i opoziva stare sesije.
- Browser skripta prilagođena je različitim Filament i Bootstrap modalima; početni timeout-i bili su problemi test selektora.

## Browser

Headless Chrome/Playwright koristi samo lokalni server, zasebnu SQLite bazu i log mailer. Potvrđeni su prijava škole, administratorski login, Filament odobravanje, Leaflet klik i prevlačenje sa čuvanjem koordinata, izbor stabla na javnoj mapi i slanje stvarnog obrasca usvajanja. Detalji nastavka toka navedeni su u završnoj dopuni ovog izveštaja; neizvršene provere ne smatraju se uspešnim.

## Završne provere i ograničenja

route:list, view:cache i npm run build prošli su. Pretragom resources i package.json nisu nađene Google Maps zavisnosti. Leaflet i OpenStreetMap ostaju aktivni. Porto i šest glavnih stranica su sačuvani.

Stvarni SMTP inbox nije proveravan. Pre javnog rada podesiti SMTP i pošiljaoca, javni HTTPS APP_URL, sigurne cookies, cron, opcion queue worker, nadzor failed/pending poruka, redovne backup-e i urednički pregled javnih tekstova/fotografija. Status sent potvrđuje transport, ne konačnu inbox dostavu.

## Dokončana browser provera

Kompletan lokalni tok je uspešno završen: škola → admin odobravanje → javna mapa → obrazac usvajanja → email potvrda preko lokalnog log mailera → admin odobravanje → anoniman javni status → nova jednokratna pristupna veza → sesija staratelja → izveštaj sa privatnom slikom → moderacija javnog teksta i slike → javna istorija i kontrolisana fotografija. Potvrđeni su desktop 1366px, mobilni 390px bez horizontalnog overflow-a, učitana odobrena slika i galerijski lightbox. Nije bilo JavaScript pageerror događaja. Screenshot-i pregledani vizuelno; nalaze se u /tmp/ecosync-phase2-screenshots/.

Početni neuspeh parsiranja test poruke otklonjen je u browser skripti: log mailer sadrži plain-text token i ne sme se naslepo dekodirati kao quoted-printable. Produkcijski token mehanizam nije zahtevao promenu.

# Faza 3 — lokalizacija i UI

Baseline je ponovljen pre izmene: 67 testova / 592 provere. Posle jezičkih ruta ažurirane su postojeće regresione provere da koriste kanonski /sr prefiks; dodat je LocalizationTest. Rezultat: 73 testa / 717 provera, svi uspešni. Pokriveni su svih šest stranica u oba jezika, dinamički profili, switch identiteta i bezbednih filtera, stari redirect-i sa query stringom, nepoznati jezici, validacija, fallback uredničkog sadržaja, sačuvan jezik emaila pri ponovnom pokušaju, potvrda tokena preko drugog jezika, privatni noindex i ikone. Route cache/clear, view cache i Vite build prolaze.

Novi English JSON sadržaj i jezička polja dodati su jednom aditivnom migracijom. Nova kopija 22 tabele obnovljena je na MySQL 8.4; sva originalna polja i redovi su poređeni pre i posle migracije lokalno i udaljeno. Originalni podaci su očuvani, bez testnih javnih zapisa u udaljenoj bazi.

Tokom rada otkrivene su i ispravljene integracione greške: podrazumevani locale za URL generator van web zahteva; translations niz u domenskoj validaciji; vraćanje prethodnog jezika nakon obrade outbox poruke; fallback javnih error metapodataka kada admin koristi sopstveni locale. Stari statusi, tokeni i CSRF/autorizacija nisu menjani.

Početni browser timeout za marker bio je posledica pravilnog klasterovanja škole i stabla na istoj test lokaciji. Provera bira filter pojedinačnih stabala pre otvaranja markera. Browser koristi novu izolovanu SQLite bazu na lokalnom serveru i log mailer, bez stvarne SMTP pošte. Konačni rezultat naveden je u dopuni nakon izvršenja.

Stvarni English urednički prevodi postojećih zapisa ostaju opciona obaveza organizacije u Filamentu. Aplikacija eksplicitno koristi srpski tekst kada prevod nedostaje. Stvarni SMTP i hosting cron/worker i dalje zahtevaju ranije opisanu konfiguraciju i proveru.

## Konačan browser rezultat faze 3

Chrome/Playwright je uspešno proverio svih šest stranica na srpskom i engleskom pri širinama 1366 i 390 px bez horizontalnog overflow-a. Provereni su navigacija, promena jezika sa očuvanim identifikatorom/filterom/anchor-om, školski obrazac i potvrda na engleskom, oba jezička obrasca usvajanja, lokalni English email i POST potvrda bez tokena u adresnoj liniji, map filteri/reset/popup, Font Awesome markeri, galerija/lightbox i dostupnost English grupe u Filamentu. JavaScript pageerror lista je prazna. Snimci su u /tmp/ecosync-phase3-screenshots/; desktop je vizuelno pregledan. Prethodne test timeout-e izazvali su klaster test lokacija i čekanje popup animacije; kod mape nije zahtevao korekciju zbog tih timeout-a.

Vizuelno je pregledan i engleski mobilni screenshot. Završno su prevedeni preostali footer slogan, pojačan prikaz izabranog jezika i povećana minimalna širina desktop filtera. Osnovni jezički testovi i build ponovljeni su nakon tih korekcija.

Nakon poslednjeg tekstualnog audita ponovljeni su LocalizationTest i PublicPagesTest: 14 testova / 286 provera, svi uspešni, kao i view cache i Vite build. Dopunjene su oznake broja fotografija, aktivnih usvajanja, otvaranja albuma, godina i primera lokacije. Privremeni browser server i MySQL kontejner su zaustavljeni, test upload-i i nezaštićeni dump/kredencijali uklonjeni. Proverena šifrovana kopija ostaje sačuvana.

# Faza 4 — UI, sertifikati i QR

Početna regresiona provera: 73 testa / 717 provera. Dodati CertificatesQrTest proverava payment toggle, sve nedozvoljene statuse, potvrdu emaila/odobrenje, administratora i sopstvenu sesiju, tuđu/isteklu/opozvanu sesiju, javnu privatnost imena, stabilnu referencu, odobrene podatke dokumenta, ugrađene fontove, jednu A4 stranu sa dugim imenima, obe jezičke QR destinacije i nezavisno dekodiranje. Završna puna provera: 80 testova / 791 provera, svi uspešni. SQLite :memory: i Mail::fake() ostaju obavezni; nema udaljenih test zapisa ni stvarnih email poruka.

Nema novih migracija niti udaljenih upisa u ovoj fazi. Reference se izvode iz postojećeg javnog UUID-a. Dodati su lokalni dompdf 3.1.6 i BaconQrCode 3.1.1, uz razvojni nezavisni QR dekoder. Composer nije prijavio bezbednosne advisories; platform requirements prolaze. Route cache/clear, view cache i Vite build prolaze.

PDF QA je koristio isključivo izolovane test podatke. Poppler je renderovao srpski/engleski A4 sertifikat, PDF oznaku i primer sa dugim imenima. U prvom renderu referenca je prešla na drugu stranu; korigovani su razmaci i gustina dugog sadržaja. Konačni primeri imaju jednu A4 stranu, čitljivu referencu i srpske dijakritike čćšžđ. Ugrađeni QR izdvojen iz stvarnog PDF-a nezavisno je dekodiran u očekivani javni URL. Slike su vizuelno pregledane. Dokument ne sadrži email, telefon, interne beleške ni tokene.

Browser QA koristi novu zasebnu SQLite bazu i lokalni log mailer. Početni test selektor je razlikovao logo headera od footer logotipa; scenario početne mape prilagođen je njenom postojećem radu bez filtera, otvaranjem klastera. Ove test greške nisu prijavljene kao funkcionalni uspeh. Konačan browser rezultat je u dopuni ispod.

## Konačan browser i PDF rezultat faze 4

Chrome/Playwright je potvrdio desktop 1366px i mobilni 390px na srpskom/engleskom: SR/EN krajnje desno uz slogan u gornjem redu na desktopu, samo mobilni izbor u otvorenom meniju, očuvanje profila pri promeni jezika, odsustvo horizontalnog overflow-a, nulti border i bez senke početne mape, otvaranje klastera/markera/popup-a, skriven payment block, punu širinu podrške i uspešno slanje upita. Potvrđeni su neautorizovano 404 odbijanje, jednokratni lokalni pristup staratelja i stvarno preuzimanje srpskog/engleskog PDF-a. Filament QR modal je prikazao SVG, a preuzimanja SVG-a i English PDF oznake prošla su. JavaScript pageerror lista je prazna. Screenshot-i su u /tmp/ecosync-phase4-screenshots/ i vizuelno su pregledani.

Tekst oba stvarno preuzeta sertifikata izdvojen je Poppler-om: dozvoljeni inicijali Č. Đ. su prisutni, puno ime/email/telefon/interne beleške i tokeni odsutni. DejaVu Sans/Serif fontovi su ugrađeni sa Unicode mapama. Nezavisni dekoder potvrdio je i QR iz PNG-a/PDF-a i SVG koji je browser rasterizovao. Srpska/engleska PDF oznaka i sertifikati sa običnim i dugim imenima vizuelno su pregledani kao jedna A4 strana. Stvarna fizička štampa i skeniranje mobilnom kamerom ostaju praktična provera pre postavljanja oznaka.

Nisu korišćeni realni SMTP, spoljašnji QR API niti udaljeni upisi. Pre štampe potreban je produkcijski HTTPS APP_URL. Zvanični logo nije bio dostavljen; koristi se postojeća EcoSync biljna oznaka. Uočeni ranije neprevedeni homepage slogan povezan je sa postojećim prevodom uz očuvanje srpskog teksta.

Posle završne korekcije homepage slogana ponovljeni su relevantni UI/lokalizacioni/PDF testovi: 21 test / 360 provera, svi uspešni; Blade keš je obnovljen. Test upload-i i privremeni kredencijali/log mailer su uklonjeni, a lokalni browser server zaustavljen. Dokumentacija za aktivaciju payment bloka i produkcijski APP_URL je dopunjena.
