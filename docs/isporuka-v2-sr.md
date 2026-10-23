# v2.0 provere i isporuka

## Automatizovane provere

Lokalno pokretanje sa razvojnim zavisnostima:

```bash
composer install
vendor/bin/phpunit
npm ci
npm run build
```

GitHub Actions workflow `.github/workflows/ci.yml` pokreće testove i produkcioni build na svakom push-u i pull request-u ka `master` grani.

Testovi pokrivaju obračun rata, ukupan i preostali dug, unos dužnika, godišnji pregled, validaciju, prijavu, uloge administratora/prodavca, uplate i storno.

## Ručna provera

Na računaru i telefonu proveriti:

1. prijavu i odjavu;
2. godišnji pregled, izbor godine, pretragu, filter i paginaciju;
3. unos novog dužnika bez adrese i mesta;
4. izbor rata od 2 do 24 i prikaz rasporeda rata;
5. otvaranje stranice dužnika i prikaz kontakta, zaduženja i istorije;
6. unos validne uplate, odbijanje prevelike uplate i prikaz ostatka;
7. da prodavac ne vidi dugmad za storno, a administrator ih vidi;
8. storno sa razlogom i čuvanje originalne uplate u istoriji.

## Produkciona isporuka

Na serveru koristiti PHP 8.3 ili noviji, podesiti `.env`, uvesti bazu i postaviti domen na `public/` direktorijum. Posle preuzimanja koda:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

`APP_DEBUG=false` mora biti podešen u produkciji. Posle promene `.env` pokrenuti `php artisan optimize:clear`, a zatim `php artisan optimize`.

## Rezervne kopije

Skripta `scripts/backup-mysql.sh` pravi kompresovani `mysqldump`, čuva kopije 30 dana i briše starije. Na cPanel-u je postaviti van `public_html`, dati joj izvršne dozvole i dodati dnevni Cron Job. Promenljive `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` i opciono `BACKUP_DIR` postaviti u Cron komandi ili zaštićenom wrapperu.

Najmanje jednom mesečno probati vraćanje poslednje kopije u odvojenu bazu. `.env` i backup fajlovi ne smeju biti javno dostupni.

## Korisničko uputstvo

Za zaposlene koristiti [kratko uputstvo](uputstvo-za-korisnike.md), a za engleski interfejs [English guide](user-guide-en.md).
