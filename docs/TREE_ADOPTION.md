# Usvajanje stabla

Posetilac na „Naš zeleni trag” bira pojedinačno objavljeno dostupno stablo, otvara profil i „Usvoji drvo”. Grupe se ne usvajaju. Obrazac podržava simbolično usvajanje i aktivnu brigu, ime, email, opcion telefon i saglasnost za javno puno ime, inicijale ili anonimnost. Email i telefon nikada nisu javni.

Uspešan zahtev rezerviše stablo 60 minuta i čeka email potvrdu. Nasumičan token ima samo hash u bazi, šalje se u URL fragmentu, uklanja iz adresne linije i potvrđuje POST zahtevom sa CSRF zaštitom. GET ne aktivira usvajanje. Token je jednokratan i važi 60 minuta; ponovno slanje poništava prethodni. Ne produžava prvobitnu nepotvrđenu rezervaciju.

Potvrda vodi u pending_approval. Administrator mora odobriti zahtev; potvrđena rezervacija čeka pregled najviše 168 sati. Podešavanja su ADOPTION_VERIFICATION_EXPIRES_MINUTES, ADOPTION_RESERVATION_EXPIRES_MINUTES i ADOPTION_REVIEW_EXPIRES_HOURS. Aktivno usvajanje nema automatski rok rezervacije.

Filament „Usvajanja stabala” omogućava pregled kontakata, filter statusa, pretragu identifikatora i tačnog imena/emaila, odobravanje potvrđenog zahteva, odbijanje i završetak. Baza jedinstvenim rezervacionim ključem i zaključavanjem stabla sprečava dva staratelja. Ponovljeno odobravanje ne pravi novo usvajanje. Statusi su pending_verification, pending_approval, active, rejected, expired, cancelled, ended. Odbijanje, istek i završetak oslobađaju rezervaciju uz očuvanje istorije. Neobjavljena i administrativno nedostupna stabla ostaju nedostupna.

Aktivni staratelj traži novu vezu na `/staratelj/pristup` unosom identifikatora i emaila. Odgovor je generički radi privatnosti. Veza i sesija traju 20 minuta (GUARDIAN_ACCESS_EXPIRES_MINUTES), token je jednokratan. Sesija omogućava promenu tipa usvajanja/javnog prikaza, prijavu nege i otkazivanje. Promena emaila zahteva potvrdu na novoj adresi i opoziva prethodne sesije. Nema naloga niti trajnih pristupnih URL-ova.

Administrator može ispraviti kontaktne podatke. Za uklanjanje ličnih podataka najpre odbija ili završava usvajanje, zatim koristi posebnu akciju. Čuva se nepersonalna istorija statusa. Saglasnost ima verziju i vreme. APP_KEY čuvajte: šifruje kontakte i utiče na privatnu pretragu.

`php artisan ecosync:expire-reservations` obrađuje napuštene zahteve; scheduler ga izvršava svakog minuta. Aktivna usvajanja se ne brišu. Lokalno testiranje: `php artisan test --filter=AdoptionMaintenanceTest`; testovi prisilno koriste SQLite memoriju i lažnu poštu.

## Dvojezični pristup

Javne adrese imaju /sr ili /en prefiks. Stare veze i POST potvrde ostaju kompatibilne. Pet informativnih koraka objašnjava ceo tok bez menjanja statusa ili pravila sigurnosti. Jezik zahteva određuje jezik narednih poruka; sama promena jezika prikaza ne aktivira usvajanje i ne produžava pristup.
