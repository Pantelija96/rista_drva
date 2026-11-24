# EcoSync administracija

## Prijava i administrator

Otvorite `/admin`, unesite email i lozinku. Javno otvaranje administratorskih naloga nije omogućeno. Prvi nalog odgovorna tehnička osoba kreira iz korena aplikacije:

```bash
php artisan ecosync:create-admin
```

Komanda traži ime, email, skrivenu lozinku od najmanje 12 znakova sa slovima i brojevima i njenu potvrdu. Postojeći email se odbija. Odjava je u korisničkom meniju. Za oporavak lozinke koristite link na prijavi kada je SMTP podešen. U delu Administratori možete dodati drugog administratora ili ukinuti pristup postojećem. Poslednjem administratoru pristup se ne može ukinuti.

## Zahtevi škola

U EDUKACIJA → Zahtevi škola pregledajte kontakt podatke i datum prijave. Oznaka Razmotri menja status u razmatranju. U izmeni zapisa možete dodati interne beleške. Privatni kontakt podaci i beleške dostupni su samo administratorima.

Odobri otvara potvrdu i izbor postojeće škole. Ako ista škola već postoji, izričito je izaberite. Prazan izbor pravi novu školu kao nacrt, osim ako isti naziv i grad već postoje. Sličan naziv sam po sebi ne spaja škole. Po želji unesite godinu generacije i izaberite konačan model sadnje. Godina završetka školovanja može se razlikovati od godine sadnje. Ponovljeno odobravanje istog zahteva ne pravi duplikat. Originalna prijava ostaje sačuvana. Odobravanje označava obradu prijave, bez obećanja termina sadnje.

Odbij obrađuje zahtev bez kreiranja škole. Pošalji obaveštenje ponovo koristi trenutno podešeni email transport. Ako je transport log, poruka je samo u dnevniku.

## Škole i lokacije

U Škole izaberite novu školu ili otvorite postojeću. Unesite naziv, tip ustanove, grad i javni opis. URL oznaku možete ostaviti praznu pri kreiranju i sistem će napraviti jedinstvenu oznaku. Kontakt podaci su privatni. Lokacija nije obavezna. Škola može ostati bez koordinata dok tačna pozicija ne bude potvrđena.

Za lokaciju pomerite Leaflet/OpenStreetMap mapu do škole, a zatim je uvećajte tasterima +/− ili gestom na ekranu. Klik na potvrđeno mesto postavlja marker. Početni centar kod Grocke služi samo za navigaciju. Marker se može prevući, a koordinatama se može upravljati i ručno. Obriši lokaciju prazni obe vrednosti. Sačuvajte obrazac. Ne koristite centar grada kao zamenu za nepoznatu lokaciju.

Status Objavljeno omogućava javni profil. Nacrt i Neaktivno nisu javni. Objava roditeljske škole utiče i na javnu dostupnost njenih generacija, albuma i stabala.

## Generacije

U Generacije izaberite školu, godinu završetka školovanja, program/smer i model sadnje. Program general predstavlja opštu generaciju. Za legitimno različite programe iste godine koristite različite oznake programa. Ista škola, godina i program ne mogu se ponoviti. Interne beleške se ne prikazuju javno. Objavite generaciju kada su podaci potvrđeni.

## Vrste, grupe i stabla

Najpre dodajte vrstu u Vrste drveća. Nepoznate botaničke vrednosti ostavite prazne. Objavite vrstu da bi njena javna stabla bila vidljiva.

U Grupe stabala unesite naziv, URL oznaku, povezanu školu/generaciju ako postoje i prijavljeni ukupan broj. Taj broj uključuje pojedinačno registrovana stabla. Grupa od 30 sa pet pojedinačnih zapisa i dalje predstavlja 30 stabala. Lokacija grupe je posebna i ne popunjava lokacije pojedinačnih stabala. Sačuvana lokacija treba da bude potvrđena za samu grupu.

U Stabla možete praviti pojedinačne zapise ili izabrati Registruj više stabala. Odaberite školu, generaciju, grupu i vrstu, pa količinu do 500. Zavisni izbori prikazuju samo odgovarajuće generacije i grupe. Broj ne može preći prijavljeni broj grupe. Masovni unos pravi neobjavljena stabla bez koordinata. Svako dobija trajan ECO identifikator. Zatim otvorite pojedinačno stablo, unesite datum sadnje, javni opis, stanje i potvrđenu lokaciju.

Fizičko stanje i status usvajanja su nezavisni. Status Usvojeno bira se samo kada osoblje ima stvaran dogovor o usvajanju. Interne beleške su privatne. Školu ili generaciju postojeće grupe sa stablima najpre razvežite kroz izmenu pojedinačnih stabala.

## Galerije

Napravite album u Galerije, izaberite kategoriju, školu/generaciju ako postoje, lokaciju, datum i naslovnu fotografiju. U izmeni albuma Dodaj fotografije prihvata više slika odjednom i pravi ih kao nacrte. U Fotografije izmenite pojedinačne opise, natpise i status objave. Redosled fotografija možete menjati prevlačenjem u tabeli ili unosom redosleda.

Dozvoljeni su JPG, PNG i WebP do 5 MB i dimenzija od 100 × 100 do 10000 × 10000 piksela. Koristite samo materijal odobren za javno objavljivanje. Public disk služi fajlove preko javnih URL-ova čak i dok je zapis nacrt, zato se osetljivi ili neodobreni materijali ne postavljaju. Naziv fajla se generiše automatski. Objavite i album i fotografije. Album sa povezanim fotografijama prvo ispraznite kroz resurs Fotografije, zatim obrišite album. Brisanje i zamena fajla proveravaju da li ga drugi zapis još koristi.

## Statistika

Otvorite SADRŽAJ → Statistika sajta. Za svaki pokazatelj unesite ključ, naziv, broj, opcioni sufiks, redosled i vidljivost. Broj održanih akcija dodajte tek kada ga EcoSync potvrdi. Operativni brojevi na kontrolnoj tabli broje zapise u bazi i ne menjaju istorijske brojke prikazane na početnoj.

Za fajlove ostale posle prekida unosa, tehnička osoba periodično pokreće `php artisan ecosync:clean-media` za pregled. Opcija `--delete` uklanja samo nereferencirane fajlove starije od sedam dana iz EcoSync upload direktorijuma. Pre automatskog raspoređivanja proverite pregled na konkretnoj instalaciji.

## Provera javnog prikaza lokacija

Posle čuvanja ponovo otvorite školu, stablo ili grupu. Marker mora biti na sačuvanom mestu. Za promenu kliknite novu tačku ili prevucite postojeći marker, pa sačuvajte obrazac. Ručni unos menja marker kada su obe koordinate ispravne. Obriši lokaciju prazni obe koordinate. Brisanje potvrdite čuvanjem obrasca.

Za mapiranje pojedinačnog stabla otvorite STABLA I MAPA → Stabla, izaberite stablo, postavite njegovu stvarnu lokaciju i sačuvajte. Grupu mapirajte kroz Grupe stabala. Pozicija grupe predstavlja grupu kao celinu i ne popunjava koordinate pojedinačnih stabala. Izbor lokacije škole, stabla i grupe koristi isti postupak.

Javno su vidljivi samo objavljeni zapisi sa obe potvrđene koordinate. Proverite i objavu škole, generacije, vrste drveta i grupe kojima stablo pripada. Zapis bez koordinata može ostati sačuvan i dobiti lokaciju kasnije. Na javnim profilima, početnoj i stranici Naš zeleni trag proverite marker i link ka profilu. Filteri obuhvataju grad/opštinu, školu, godinu generacije, vrstu, usvajanje i kategoriju. Filter vrste ili usvajanja izdvaja pojedinačna stabla.

Kvadratni marker sa simbolom više stabala predstavlja stvarnu grupu. Kružni klaster sa brojem privremeno objedinjuje bliske lokacije. Klik na klaster približava mapu, a broj označava lokacije u trenutnom prikazu. Na pregledu grupe uvek upoređujte ukupan prijavljeni broj i broj pojedinačno registrovanih stabala.

## Faza 2: upiti, usvajanje i nega

„Upiti i podrška” sadrži kontakt i šest vrsta podrške. Obeležite obradu, unesite interne beleške i po potrebi uklonite lične podatke posebnom akcijom. Nema online naplate.

„Usvajanja stabala” prikazuje email potvrdu i status. Odobrite isključivo potvrđene zahteve; odbijte ili završite usvajanje uz privatno obrazloženje. Kontakti su samo za ovlašćene administratore. Promena emaila traži potvrdu nove adrese. „Nega stabala” omogućava službeni unos i moderaciju starateljskih prijava. Za javnost napišite bezbedan tekst i izaberite svaku dozvoljenu sliku; originalne privatne napomene nisu javne.

„Red email poruka” pratite zbog zastoja i grešaka. Logged nije dostavljeno. Posle popravke transporta ponovite failed poruku. Detalji su u MAIL_SETUP.md, TREE_ADOPTION.md i TREE_MAINTENANCE.md. Pristup staratelja nije administratorski nalog.

## Dvojezični sadržaj (faza 3)

Osnovna polja sadrže odobren srpski tekst. Grupa „English — javni sadržaj” sadrži opcione engleske prevode, sa naznakom kada prevod nedostaje. Bez prevoda engleska stranica prikazuje srpski izvor. Ne prevodite imena škola, osoba, naučna imena i identifikatore. Pregledajte oba javna URL-a /sr i /en posle izmene; podaci i veze nisu duplirani. Javni tekst nege prevodite samo kada je bezbedan i odobren za objavu.

Nove prijave/usvajanja čuvaju jezik primaoca. Već kreirana email poruka pri retry-u zadržava svoj jezik. Administrativna obaveštenja ostaju srpska. Više detalja: MULTILANGUAGE.md i UI_UX_GUIDELINES.md.

## Sertifikati i QR (faza 4)

Usvajanja sada imaju pregled reference, uslova i preuzimanje PDF sertifikata na srpskom/engleskom. Potrebni su aktivno usvajanje, potvrđen email, odobrenje i objavljeno stablo. Završetkom usvajanja prestaje mogućnost novog preuzimanja aktivnog sertifikata. Prikaz imena prati saglasnost staratelja, bez emaila i telefona.

Na stablima su QR pregled, SVG preuzimanje i PDF oznaka za štampu; oba jezika su u pregledu. Neobjavljena stabla nemaju te akcije. Pre fizičke štampe proverite javni APP_URL i skenirajte probni primerak. Detalji i lifecycle ograničenja: CERTIFICATES_AND_QR.md.
