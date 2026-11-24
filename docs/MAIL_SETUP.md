# Email i pouzdana obrada

U `.env` podesite MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_SCHEME, MAIL_FROM_ADDRESS i MAIL_FROM_NAME. Primalac administrativnih poruka je ECOSYNC_NOTIFICATION_EMAIL (podrazumevano eoecosync@gmail.com), naziv ECOSYNC_NOTIFICATION_NAME. Stvarne kredencijale čuvajte samo u zaštićenom okruženju. Posle izmene pokrenite `php artisan config:clear`.

Za STARTTLS provajder obično koristi port 587 i smtp; za implicitni TLS port 465 i smtps. Potvrdite vrednosti kod provajdera i podesite SPF, DKIM i DMARC. APP_URL mora biti javni HTTPS URL jer email veze vode na aplikaciju.

Školska prijava i događaji slanja upisuju se transakciono. Administrator dobija podatke prijave i vezu ka panelu; škola potvrdu prijema, a zatim odluku. Potvrda prijema ne znači prihvatanje. Kontakt i podrška čuvaju se kao upiti i šalju organizaciji. Usvajanje šalje potvrdu adrese, odluku i obaveštenje o završetku. Pristup staratelja i promena adrese imaju posebne jednokratne veze.

## Izvršavanje

Obavezno podesite cron svake minute, iz direktorijuma aplikacije:

```cron
* * * * * cd /putanja/aplikacije && php artisan schedule:run >> /dev/null 2>&1
```

Podrazumevani fallback direktno obrađuje trajni outbox po 25 poruka bez zasebnog worker-a. Ručno: `php artisan ecosync:mail-send --limit=25`. SMTP poziv nije u javnom zahtevu. Greška ne briše prijavu. Red koristi kratku rezervaciju posla, oporavak prekinute obrade i do pet pokušaja sa rastućim razmakom. Dostava je najmanje jednom: prekid odmah nakon SMTP prihvatanja može prouzrokovati duplikat.

Za queue postavite QUEUE_CONNECTION=database i ECOSYNC_MAIL_USE_QUEUE=true. Pokrenite nadzirani proces `php artisan queue:work --tries=3 --timeout=120`. Scheduler tada dodaje posao za obradu outbox-a. Bez worker-a poruke čekaju. Proveravajte `php artisan queue:failed`; nakon otklanjanja uzroka koristite `php artisan queue:retry ID`.

U Filamentu „Red email poruka” prikazuje pending, processing, sent, logged, failed i cancelled. Posle otklanjanja SMTP greške ponovite failed poruku akcijom. Školska prijava ima eksplicitnu akciju za ponovno administratorsko obaveštenje. Ne ponavljajte prijavu škole radi slanja.

MAIL_MAILER=log zapisuje sadržaj, uključujući privatne podatke i kratkotrajne veze, u zaštićeni log. To NIJE dostava; status je logged. SMTP status sent znači prihvatanje transporta, ne dokaz prijema u inbox. Ograničite pristup i rok čuvanja logova. Automatski testovi koriste fakes/array, bez stvarne pošte.

Tek na odobrenoj instalaciji pokrenite `php artisan ecosync:test-mail` i proverite inbox konfigurisanog primaoca. Stvarna SMTP isporuka u ovoj fazi nije proverena.

## Jezik poruka

Faza 3 čuva preferred_locale na novim prijavama/usvajanjima i locale na svakom outbox događaju. Worker eksplicitno primenjuje jezik teksta i URL-a, nezavisno od trenutnog web zahteva. Retry koristi originalni jezik poruke. Administratorske poruke su srpske. Stare email veze ostaju kompatibilne; tokeni nisu promenjeni.
