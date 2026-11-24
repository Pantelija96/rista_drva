# Dvojezični EcoSync

## Jezici i rute

Javni jezici su sr (srpski latinicom, podrazumevan) i en (engleski). Isti Blade prikazi služe oba jezika. Osnovne adrese su `/sr`, `/en`, a zatim isti segmenti `nasa-misija`, `pridruzi-se`, `nas-zeleni-trag`, `galerija`, `kontakt`. Profili, obrasci i JSON mape takođe imaju prefiks. `/admin` ostaje nezavisan.

PublicLocale prihvata isključivo sr/en, postavlja Laravel locale i URL defaults pre model binding-a, zatim uklanja infrastrukturni parametar iz argumenata kontrolera. ResolvePublicLanguage određuje jezik i za greške pre rutiranja. Unsupported locale vraća 404. Rute mogu da se keširaju.

Stari GET URL-ovi vraćaju 301 na srpski ekvivalent uz query string; stare .html adrese ostaju kompatibilne. Prethodno otvoreni POST obrasci i email potvrde i dalje se prihvataju bez promene tokena. Token je u fragmentu, hash u bazi i POST ostaju neizmenjeni; nema zavisnosti od jezika niti novog potpisivanja. Ne dodajte tokene u query parametre. Administratorove rute nemaju javni jezički prefiks.

## Izbor jezika

Header sadrži SR / EN, uključujući mobilnu navigaciju. Link čuva putanju i identifikator resursa, bezbedne query filtere i običan anchor kroz JS. Token fragmenti se ne kopiraju u navigaciju. Izbor je sačuvan u sesiji; eksplicitni URL uvek ima prednost. Nema preusmeravanja prema jeziku browser-a. Stare adrese imaju deterministički podrazumevani sr jezik. Ako je obrazac menjan, izbor jezika upozorava na ponovno učitavanje; otkazivanje čuva unos. Privatni formulari se ne skladište u browser storage-u.

## Jezičke datoteke

`lang/sr/public.php` i `lang/en/public.php` sadrže semantičke ključeve javnog teksta. `flows.php` sadrži korake i objašnjenja; `validation.php` poruke i imena polja. Novi tekst poziva se kao `__('public.key')`, a parametri kao `__('flows.expiry', ['minutes' => $minutes])`. Zadržite iste ključeve u oba jezika i prirodan engleski prevod. Ne menjajte postojeći odobren srpski tekst bez uredničkog razloga. PublicText prevodi samo unapred poznate enum oznake; nikada imena osoba ili proizvoljan korisnički tekst. JS poruke mape/galerije biraju isti URL jezik.

## Urednički sadržaj

Aditivna migracija dodaje nullable JSON `translations` na schools, school_generations, tree_species, trees, tree_groups, gallery_albums, gallery_images, site_statistics i tree_maintenance_records. Postojeći srpski atributi ostaju osnovni. English vrednosti su u `translations.en.FIELD`. Modeli imaju `localized('field')`; ako je engleski prevod prazan, eksplicitno koriste srpski atribut. Ne postoje duplirana stabla ni škole po jeziku.

U Filamentu su srpski sadržaji u osnovnim poljima, a zasebna sklopiva English grupa pokazuje opcione prevode i upozorenje za prazna polja. Prevode se opisi, urednički naslovi, botanički tekst, natpisi/alt tekst fotografija, oznake statistika i odobreni javni tekst nege. Imena škola i osoba, identifikatori, naučna imena, koordinate, datumi, brojevi i putanje se ne prevode. Administrator proverava tačnost; aplikacija ne izmišlja niti automatski upisuje prevode postojećih zapisa.

## Email i red

Nove školske prijave, usvajanja i upiti čuvaju preferred_locale. Svaki outbox događaj pri nastanku snima locale; administratorske poruke su srpske. Pristupne veze i promene emaila koriste jezik usvajanja. Outbox eksplicitno postavlja jezik teksta i URL-a i locale Mailable-a, zatim vraća prethodni kontekst. Retry koristi sačuvani jezik poruke čak i kada se podesi drugi jezik web zahteva. Stari zapisi podrazumevano koriste sr. Ne pokrećite email slanje tokom automatskih testova bez fakes/array.

## SEO i privatnost

Zajednički layout daje lokalizovane title/description/OG, HTML lang, canonical bez query filtera, sr/en i x-default alternate. Stari URL-ovi ne prikazuju duplirani sadržaj. Privatni pristup, potvrde i prijem usvajanja imaju noindex/nofollow i no-store. Nedostajući English urednički sadržaj ima srpski fallback na postojećoj validnoj stranici, ne vodi na nepostojeći profil.

## Provere

`php artisan test` koristi isključivo SQLite memoriju. LocalizationTest proverava jezike/rute, fallback, privatnost switch URL-a, validaciju, trajno sačuvani jezik emaila i cross-locale potvrdu. `php artisan route:cache`, `php artisan route:clear`, `php artisan view:cache`, `npm run build` proveravaju deployment. Realni SMTP zahteva zasebnu proveru.

## Sertifikati i oznake

`certificates.php` u oba jezička direktorijuma sadrži nove PDF/QR poruke. Jedan zajednički Blade skup služi oba jezika; reference usvajanja ostaju iste. Jezik PDF-a i ciljnog javnog QR URL-a dolazi iz validiranog URL prefiksa. QR nikada ne vodi na privatni pristup staratelja. Desktop SR/EN je uz slogan u gornjem redu, mobilni je u meniju; samo jedan selector je vidljiv na datoj širini. Postojeće očuvanje resursa/filtera/anchor-a je sačuvano.
