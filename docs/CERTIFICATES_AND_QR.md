# Sertifikati usvajanja i QR kodovi

## Uslovi i preuzimanje

Sertifikat je dostupan samo za aktivno usvajanje sa potvrđenim emailom, datumom administratorskog odobrenja i objavljenim javnim profilom stabla. Pending, odbijeni, istekli, otkazani i završeni zahtevi ne dobijaju nove sertifikate. Neobjavljeno stablo nema javni QR niti novu oznaku/sertifikat koji bi vodio na nedostupan profil.

Aktivni staratelj traži postojeću jednokratnu pristupnu email vezu, potvrđuje pristup i na stranici `/sr/staratelj` ili `/en/staratelj` bira „Preuzmi sertifikat”. Endpoint proverava identitet usvajanja, verziju i rok sesije i status pri svakom preuzimanju, uključujući proveru posle renderovanja. Drugi staratelj, prijavljeni korisnik bez administratorskog prava ili istekla/opozvana sesija ne mogu preuzeti dokument. Pogađanje UUID-a ne omogućava pristup.

Administrator koristi „Usvajanja stabala” → Sertifikat, odnosno ekran uređivanja sa referencom i statusom dostupnosti. Može preuzeti srpsku ili englesku verziju. Postojeći tok email potvrde i odobravanja nije promenjen.

## Sadržaj i privatnost

A4 PDF koristi zelenu/krem paletu, postojeću EcoSync biljnu oznaku iz favicon resursa, naziv Zelena diploma i lokalno ugrađene DejaVu fontove sa srpskim latinicnim dijakriticima. Zvanični odobren logotip nije bio dostupan; privremena oznaka nije predstavljena kao novi zvanični logo. Kada organizacija dostavi logo, ažurirajte kontrolisani lokalni brand resurs u servisu.

Dokument sadrži oznaku stabla, vrstu, datum odobrenja, tip usvajanja, školu/generaciju ako postoje, bezbedan javni prikaz staratelja, javni URL, QR, referencu i datum generisanja. Ime prati izbor full_name/initials/anonymous. Email, telefon, interne beleške, interni numerički ID-jevi i tokeni nikada se ne prosleđuju PDF prikazu. Ime škole i naučno ime se ne prevode. Urednički tekst vrste koristi postojeći English prevod ili srpski fallback.

Sertifikat nije dokaz prava svojine nad stablom ili zemljištem; oba jezika to izričito navode. Na dokumentu piše da predstavlja stanje na datum generisanja i da se trenutno stanje proverava na javnom profilu.

## Referenca i istorija

Referenca `ECO-CERT-<postojeći javni UUID usvajanja>` stabilna je kroz oba jezika i ponovna preuzimanja. Nije pristupni token. Zasniva se na već postojećem jedinstvenom javnom identifikatoru, pa nova migracija ili tabela nisu potrebne. Datum odobrenja ostaje originalni; datum generisanja označava konkretnu kopiju.

PDF nastaje na zahtev i ne skladišti se kao javni fajl. Response ima attachment, no-store/private, noindex/nofollow i no-referrer. Nema trajnih autorizacionih URL-ova. Sačuvana kopija kod korisnika ne može se daljinski opozvati: nakon završetka ostaje istorijski dokument o ranijem stanju, a ne potvrda aktuelnog starateljstva. Backend odbija nove aktivne sertifikate za završeno usvajanje i ne menja istorijske zapise. Naknadna korekcija privatnosti utiče na naredno generisanje, ne menja ranije preuzetu kopiju.

## QR i fizičke oznake

„Stabla” → QR pregled prikazuje kod i linkove na srpski/engleski SVG i PDF oznaku. Direktne akcije „Preuzmi QR” i „Oznaka za štampu” koriste srpski; engleske verzije su u pregledu i na ekranu uređivanja. Prikazane su samo za stabla sa važećim javnim profilom. PDF oznake preuzimaju ovlašćeni administratori. Javni profil ima diskretan sklopivi QR odeljak; javni QR sadrži isključivo javni URL.

QR kodovi se generišu lokalno pomoću BaconQrCode 3.1.1, bez eksternih API-ja. SVG je vektorski format pogodan za štampu; PDF ugrađuje 900px PNG. Crna/bela boja, četiri modula prazne zone i M korekcija greške čuvaju čitljivost. PDF oznaka je A4 sa velikim QR kodom, identifikatorom, vrstom i pozivom „Skeniraj i upoznaj ovo stablo”. Štampajte bez odsecanja bele zone, proverite skeniranje sa stvarne probne štampe i koristite prikladan materijal za spoljašnju upotrebu.

URL se formira iz stvarnog `APP_URL` i postojećih lokalizovanih ruta, bez dolaznog Host zaglavlja, query filtera ili privatnih tokena. Pre štampanja obavezno postavite javni HTTPS domen u APP_URL i očistite konfiguracioni keš. Promena domena kasnije ne menja već odštampan QR: sačuvajte stari domen/preusmeravanja. Ako je APP_URL razvojni localhost, dobijeni kod je samo za lokalni test.

## Biblioteke i server

Dompdf 3.1.6 generiše PDF kroz Laravel servis/Blade. Kompatibilan je sa deklarisanim PHP 8.3 projektom. Ne učitava udaljene resurse; PHP i JavaScript u PDF-u su isključeni. Potrebne su postojeće DOM, mbstring, GD, iconv/XMLWriter ekstenzije i standardne Composer zavisnosti. DejaVu fontovi dolaze uz dompdf. Razvojni QR dekoder khanamiryan/qrcode-detector-decoder služi samo za nezavisne testove skeniranja.

Deployment: `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`, `php artisan view:cache`, a zatim uobičajeno osvežavanje konfiguracionog/rutnog keša. Nova migracija nije potrebna i udaljena baza nije menjana u fazi 4. Sačuvajte postojeći APP_KEY i kontrolu pristupa privatnom storage-u.

## Prevodi i izgled

Svi novi tekstovi su u `lang/sr/certificates.php` i `lang/en/certificates.php`. PDF prikazi su zajednički: `resources/views/pdf/base.blade.php`, `certificate.blade.php`, `tree-label.blade.php`. Pristup i bezbedna projekcija su u TreeDocumentsController, AdoptionCertificates i TreeQr. Pri izmeni prevoda renderujte oba jezika i duga imena, proverite jednu A4 stranu, ugrađene fontove, referencu i QR dekodiranje. Ne ubacujte kontakte ili privatne beleške u prikaze.

## Skriveno uputstvo za uplatu

Primer uputstva ostaje sačuvan u postojećem Blade prikazu, CSS-u i prevodima. Podrazumevano je skriven i forma podrške koristi punu širinu. Za kasniju aktivaciju postavite:

```dotenv
ECOSYNC_SHOW_PAYMENT_INSTRUCTIONS=true
```

Pokrenite `php artisan config:clear` ili obnovite produkcijski config cache. Pre aktivacije organizacija mora odobriti stvarne podatke i formulacije; postojeći sadržaj je samo primer. Isključivanje je false. Nema online plaćanja.

## Provere

CertificatesQrTest pokriva uslove/status, administratorski i sopstveni pristup, tuđi/istekli/opozvani pristup, privatnost imena i kontakata, stabilnu referencu, ugrađene fontove/A4, oba jezika, nezavisno dekodiranje QR-a, privatnost neobjavljenih stabala i payment toggle. Automatski testovi koriste SQLite :memory: i Mail::fake(), nikada udaljenu bazu ili SMTP. Poppler render i izvlačenje teksta proveravaju dijakritiku, izgled i QR iz stvarnog PDF-a. Rezultati browser/regresionih provera su u E2E_TEST_REPORT.md.
