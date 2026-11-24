# MySQL i bezbedne migracije

Lokalna `.env` datoteka sadrži pristupne podatke. U repozitorijumu je samo primer:

```dotenv
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Pre svake promene potvrdite naziv ciljne baze sa odgovornom osobom. Proverite DNS/IP, mrežni pristup portu 3306 i firewall. Ako provajder podržava TLS, podesite njegov CA sertifikat kroz MYSQL_ATTR_SSL_CA. Ne ispisujte lozinku niti koristite lozinku kao argument komandne linije.

```bash
php artisan config:clear
php artisan ecosync:database-inspect
php artisan migrate:status
```

Prva komanda briše samo lokalni konfiguracioni keš. Inspekcija je samo za čitanje i prikazuje izabranu bazu i broj redova u svakoj tabeli. Pre migracije pregledajte postojeće tabele, migracije i podatke. Inicijalna preflight migracija odbija postojeće istoimene domenske tabele ili users tabelu. Za postojeću aplikacionu users tabelu najpre pripremite posebnu pregledanu aditivnu migraciju i uskladite model, bez prepisivanja podataka.

Napravite punu rezervnu kopiju kod hosting provajdera ili kroz mysqldump sa interaktivnim unosom lozinke ili zaštićenim option fajlom. Uključite strukturu, podatke i eventualne trigere/rutine. Proverite integritet i obnavljanje u zasebnoj bazi. Prazna baza takođe zahteva dokumentovan snimak koji potvrđuje da nije bilo tabela ni redova. Privatne kopije ne čuvajte u public direktorijumu i ne dodajte u Git.

Na početku ove faze udaljena baza proverena je kao prazna. Snimak praznog stanja i SHA-256 manifest čuvaju se lokalno u `storage/app/private/backups/`. Ovaj početni snimak nije zamena za novu punu kopiju nakon što baza počne da sadrži podatke.

Posle provere kopije i kolizija:

```bash
php artisan migrate --pretend
php artisan migrate
php artisan db:seed --class=SiteStatisticSeeder
php artisan ecosync:create-admin
```

Prikaz migrate --pretend može inicijalno napraviti migrations evidencionu tabelu ako ne postoji, zato ga pokrenite tek nakon dokumentovane početne inspekcije i kopije. U produkciji Laravel traži potvrdu. Opciju --force koristite samo u prethodno pregledanom deployment postupku.

Migracije su odvojene po tabelama i imaju strane ključeve sa ograničenim brisanjem. Prave users, password_reset_tokens, schools, school_generations, tree_species, tree_groups, trees, school_applications, gallery_albums, gallery_images i site_statistics, uz Laravel migrations evidenciju. MySQL DDL nije u potpunosti transakcioni. Ako migracija stane, uporedite migrate:status sa stvarnom šemom i oporavite isključivo potvrđeni parcijalni korak. Ne brišite tabele radi ponovnog pokušaja. Inicijalne down operacije namerno odbijaju automatsko uklanjanje podataka.

Ne koristite migrate:fresh, migrate:refresh, db:wipe, migrate:reset niti destruktivni rollback nad zajedničkom bazom. Testovi koriste isključivo SQLite :memory:, sa dva sloja zaštite u phpunit.xml i tests/TestCase.php pre bilo kakvih test migracija. Obični testovi proveravaju domenske veze i SQLite kompatibilnost. Odvojeno proverite status migracija i ograničenja na stvarnom MySQL serveru bez brisanja podataka.

Za oporavak zamrznite upis, sačuvajte trenutno stanje, obnovite proverenu punu kopiju u zasebnoj bazi, uporedite podatke i tek zatim koordinisano prebacite aplikaciju. Ne automatizujte destruktivni povratak nad deljenom bazom.

## Faza 2 — 23.11.2026.

Udaljena baza je pre izmene sadržala 12 tabela sa stvarnim podacima. Napravljena je konzistentna mysqldump kopija, obnovljena u privremenom MySQL 8.4 i proverena brojevima i SHA-256 sažecima svih originalnih redova. Osam aditivnih migracija provereno je na obnovljenoj bazi, zatim primenjeno na udaljenoj uz preflight proveru kolizija i poređenje podataka pre i posle. Originalni podaci ostali su neizmenjeni. Nisu uneti izmišljeni javni podaci.

Nove tabele: inquiries, mail_outbox, tree_adoptions, guardian_access_grants, tree_adoption_events, tree_maintenance_records, maintenance_photos i standardne jobs/failed_jobs/job_batches ako nedostaju. Migracije nose datum 2026_11_23 i odbijaju destruktivni down.

Proverena šifrovana kopija je `storage/app/private/backups/phase2-before-20261123.sql.enc`, manifest je susedni `.json`. Šifrovana je Laravel Crypt-om: oporavak zahteva sačuvani APP_KEY. Kopirajte oba fajla i ključ u odvojeno zaštićeno skladište. Za obnovu, u pouzdanom lokalnom Laravel okruženju sa istim ključem koristite Crypt::decryptString(file_get_contents($path)), upišite SQL u fajl dozvole 0600, proverite sql_sha256 iz manifesta i uvezite u novu izolovanu MySQL bazu. Nikada ne prikazujte SQL u terminalu. Posle potvrde obnove obrišite nezaštićeni SQL. Kopija nije zamena za redovne backup-e baze i privatnih fotografija.

## Faza 3

Migracija 2026_11_24_000001_add_public_translations dodaje nullable translations JSON na devet uredničkih tabela, preferred_locale na prijave/upite/usvajanja i locale na mail_outbox, sa sr podrazumevanim jezikom. Pre primene 23.11.2026. napravljena je nova kopija svih 22 tabele, obnovljena u lokalnom MySQL 8.4 i proverena sažecima svih originalnih redova i kolona. Migracija je proverena lokalno i zatim primenjena udaljeno; postojeće vrednosti i veze su očuvane. Nisu dodavani testni sadržaji ni izmišljeni prevodi u udaljenu bazu.

Šifrovana kopija: storage/app/private/backups/phase3-before-20261123.sql.enc uz JSON manifest. Isti Crypt/APP_KEY postupak obnove važi kao za fazu 2. Sačuvajte ključ odvojeno. Redosled deployment-a: backup i provera, aditivna migracija, novi kod/build, config/view/route keš i restart dugotrajnih worker-a. Ne primenjujte kod koji upisuje jezička polja pre migracije.
