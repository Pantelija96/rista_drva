# UI i UX smernice

Porto i postojeća zelena paleta ostaju osnova. Koristite postojeće ngo klase, grid, kartice i modal strukturu; bez novog frontend framework-a. Šest osnovnih javnih stranica ostaju zasebne.

## Ikone

Koristi se postojeći Font Awesome Free 6.4.0, jedna verzija iz Porto assets. Solid sintaksa je `fas fa-tree`, `fa-school`, `fa-leaf`, `fa-heart`, `fa-layer-group`, `fa-location-dot`, `fa-droplet`, `fa-graduation-cap`, `fa-calendar`, `fa-image`, `fa-envelope`, `fa-check`, `fa-triangle-exclamation`, `fa-clock`. Ne uvoditi novu biblioteku za ove potrebe. Dekorativne ikone imaju aria-hidden; samostalna dugmad moraju imati pristupačno ime. Korisnički emoji tekst se ne uklanja.

Leaflet DivIcon i lista rezultata koriste iste profesionalne simbole. Boje razlikuju školu, stablo, usvojeno stablo i grupu, uz tekstualnu legendu. Logička grupa stabala nije vizuelni kružni klaster; sačuvana je napomena o razlikama u brojanju. Reset filtera i uklanjanje izbora ne menjaju podatke.

## Navigacija i obrasci

SR/EN čuva trenutni resurs i bezbedne filtere. Trenutna stranica ima aria-current, jezik je jasno označen, mobilni meni ima pristupačno dugme. Podržani su tab, Escape, Bootstrap fokus unutar modala i vraćanje fokusa otvaraču. Fokus ima vidljiv obris. Skip link vodi na main. Reduced motion poštuje sistemsku postavku.

Greške imaju role=alert, uspeh/status role=status. Zadržite HTML i serversku validaciju, CSRF, rate limit i autorizaciju. Ne prikazujte „poslato emailom” samo zato što je zapis sačuvan. Pristup staratelja daje generički odgovor da se ne otkriva postojanje tuđe adrese. Promena jezika upozorava na neposlati unos, bez skladištenja ličnih podataka u sessionStorage/localStorage.

## Usvajanje

Prikažite pet informativnih koraka: izbor stabla, podaci, email potvrda, administratorski pregled, aktivno usvajanje. Koraci se prelamaju na mobilnom ekranu i ne uvode novi backend status. Objasnite simbolično usvajanje naspram aktivne brige i konfigurisan rok potvrde. Potvrda emaila sama po sebi nije aktivno usvajanje. Privatnost imena ostaje izbor staratelja; kontakt podaci nikad nisu javni.

## Škole

Prikažite izbor modela, prijavu škole, EcoSync pregled, dogovor o sadnji i učešće. Posle čuvanja jasno objasnite da zahtev čeka pregled i da datum sadnje nije obećan. Ne menjajte administratorski tok odobravanja.

## Pregled objave

Prazne mape, galerije i istorija nege daju jasne poruke. Slike imaju alt tekst; urednik može uneti engleski prevod. Javna nega ima odvojen odobren tekst i pojedinačno odobrene slike. Pre objave proverite oba jezika na desktopu i mobilnom prikazu, kontrast, fokus, čitljivost i tekstualna imena kontrola.
