# Nega i fotografije

Aktivni staratelj otvara kratkotrajnu sesiju preko email veze, zatim šalje aktivnost, datum i opis. Aktivnosti: zalivanje, čišćenje, pregled, orezivanje, prihrana, prijava problema i drugo. Budući datumi nisu dozvoljeni. Staratelj ne menja zvanične podatke stabla niti tuđe usvajanje. Administrator može nezavisno uneti službeni zapis u „Nega stabala”.

Dozvoljeno je do pet JPEG/PNG/WebP fotografija po prijavi, do 5 MB pojedinačno, dimenzija 100–6000 px i najviše 8 miliona piksela. Slike se ponovo kodiraju u JPEG sa UUID nazivima, bez izvornog naziva i metapodataka. Nalaze se na privatnom local disku, van javne storage veze. Zaštitite i uključite privatni disk u rezervne kopije.

Novi tekst i fotografije čekaju pregled. Administrator u Filamentu vidi original, interne napomene i privatni pregled slika. Za objavu upisuje zaseban javni tekst bez privatnih podataka, izričito bira fotografije i potvrđuje bezbednost sadržaja. Neizabrane/odbijene fotografije uklanjaju se posle potvrđene transakcije; ponovno objavljivanje zahteva novi fajl. Odbijeni izveštaj nije javan.

Javni profil prikazuje samo odobren javni tekst i pojedinačno odobrene fotografije objavljenog stabla. Privatni original i beleške se ne objavljuju. Kontrolisana ruta slike proverava javnu dozvolu, administratorski pristup ili važeću sesiju vlasnika. Pogađanje URL-a ne daje pristup neodobrenoj slici.

Za lokalnu proveru koristite `php artisan test --filter=AdoptionMaintenanceTest`; slučajevi pokrivaju autorizaciju, isteke, moderaciju, validaciju slika i privatnost. Pre javne objave pregledajte sadržaj i saglasnosti za osobe vidljive na fotografijama.
