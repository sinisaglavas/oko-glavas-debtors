# Plan v2.0: više optika i ograničen pristup prodavaca

## Cilj

Verzija 2.0 proširuje postojeću aplikaciju tako da više optika koristi isti sistem, dok svaki prodavac vidi i obrađuje samo podatke optike koja mu je dodeljena. Administrator ima pregled i upravljanje nad svim optikama.

Predložene optike za početak:

- Optika Glavas NS 1;
- Optika Glavas NS 2;
- Optika Glavas Futog.

## Verzije i GitHub strategija

Trenutna verzija se zaključava kao `v1.0.0` tag na postojećem `master` commit-u. Ta verzija ostaje dostupna za vraćanje i održavanje.

Razvoj v2.0 ide na grani `feature/v2-multi-store`, a stabilizacija na `release/v2.0`. Posle prihvatnih testova release grana se spaja u `master` i dobija tag `v2.0.0`. Hotfix za v1.0 ide na `hotfix/v1.0`, a hotfix za v2.0 na `hotfix/v2.0`.

Pre početka migracije treba napraviti backup produkcione baze i označiti tačan commit koji predstavlja v1.0.

## Model podataka

### `stores`

- `id`;
- `name` — puni naziv optike;
- `code` — jedinstvena interna oznaka, na primer `NS-1`;
- `address`, `city`, `phone`;
- `is_active`;
- `created_at`, `updated_at`.

### Veza korisnika i optike

Za prvu verziju v2.0 dovoljan je `store_id` na `users`, jer jedan prodavac vodi jednu optiku. Administrator ima `store_id = NULL` i pristup svim optikama. Ako se kasnije pojavi potreba da jedan prodavac radi u više optika, odnos se može proširiti pivot tabelom `store_user` bez promene poslovnih podataka.

### Veza poslovnih podataka

`debtors.store_id` određuje kojoj optici pripada dužnik. `debts.store_id` se čuva kao eksplicitna veza radi istorije, bržih izveštaja i zaštite od slučajnog premeštanja dugovanja. Uplata pripada dugu i nasleđuje njegovu optiku.

Svi upiti nad dužnicima, zaduženjima, uplatama, godišnjim pregledima i audit logovima moraju biti ograničeni na dozvoljene optike.

## Pravila pristupa

### Administrator

- vidi sve optike;
- kreira, menja i deaktivira optike;
- kreira prodavca i dodeljuje mu optiku;
- može da promeni dodeljenu optiku;
- vidi i upravlja svim dužnicima, dugovima, uplatama i izveštajima;
- može da izvrši storno uz razlog;
- vidi audit log za sve optike.

### Prodavac

- vidi samo jednu dodeljenu aktivnu optiku;
- može da unese dužnika u svoju optiku;
- može da unese zaduženje i uplatu za svoju optiku;
- može da vidi istoriju i godišnje preglede svoje optike;
- ne može da promeni optiku kojoj pripada zapis;
- ne može da pristupi drugim optikama ni direktnim URL-om ili ručnim `store_id` parametrom;
- ne može da stornira uplatu, briše podatke ili upravlja korisnicima.

Autorizacija se sprovodi serverski kroz middleware, policies i query scope-ove. Sakrivanje dugmeta u interfejsu nije dovoljna zaštita.

## Admin panel v2.0

### Optike

- lista aktivnih i deaktiviranih optika;
- kreiranje i izmena podataka optike;
- pregled broja dužnika, dugova i uplata po optici;
- deaktivacija bez brisanja istorije;
- izbor podrazumevane optike u admin izveštajima.

### Korisnici

- izbor optike pri kreiranju prodavca;
- prikaz dodeljene optike u listi korisnika;
- promena dodeljene optike uz audit zapis;
- sprečavanje deaktivacije sopstvenog naloga;
- upozorenje pre deaktivacije poslednjeg aktivnog administratora.

### Pregledi i izveštaji

- admin može izabrati jednu optiku ili „Sve optike“;
- prodavcu se izbor drugih optika ne prikazuje;
- godišnje kartice i izvoz uvek sadrže naziv optike;
- audit log se filtrira po optici, korisniku, akciji i periodu.

## Migracija postojećih podataka

1. Napraviti backup v1.0 baze i proveriti vraćanje kopije.
2. Kreirati početnu optiku, na primer `Optika Glavas NS 1` sa kodom `NS-1`.
3. Postojećim dužnicima, zaduženjima i korisnicima dodeliti tu optiku.
4. Administratore ostaviti sa globalnim pristupom.
5. Postojeće prodavce dodeliti početnoj optici ili ih ostaviti privremeno neaktivnim dok administrator ne izabere optiku.
6. Tek nakon provere podataka učiniti `store_id` obaveznim za prodavce i poslovne zapise.
7. Pokrenuti kontrolni izveštaj koji poredi broj i iznose zapisa pre i posle migracije.

Migracija mora biti ponovljiva, transakciona gde je moguće i bez brisanja postojećih uplata.

## Audit i bezbednost

Audit zapisivati za kreiranje i izmenu optike, dodelu prodavca, promenu optike dužnika, unos uplate, storno i administrativne izmene. Zapis treba da sadrži korisnika, optiku, vreme, akciju, entitet, stare i nove vrednosti i IP adresu kada je dostupna.

Ne dozvoliti premeštanje dužnika sa istorijom uplata bez posebne administratorske potvrde i jasnog audit razloga. Premeštanje ne sme retroaktivno promeniti optiku istorijskih izveštaja bez eksplicitnog poslovnog pravila.

## Redosled izrade

1. Zaključati `v1.0.0` tag i napraviti backup.
2. Dodati `stores` i veze sa korisnicima, dužnicima i dugovima.
3. Napraviti migraciju postojećih podataka u početnu optiku.
4. Dodati store scope, middleware i policies.
5. Proširiti admin panel za optike i dodelu prodavaca.
6. Dodati filtere optike u dashboard, godišnji pregled i audit log.
7. Ažurirati unos, stranice dužnika i izveštaje.
8. Dodati testove izolacije podataka i migracije.
9. Testirati v1 kompatibilnost, v2 prihvatne scenarije i povratak backup-a.
10. Objaviti `v2.0.0` tek nakon pregleda produkcionih podataka.

## Kriterijumi prihvatanja

- Prodavac dodeljen NS 1 ne može da vidi dužnika iz NS 2 ni direktnim zahtevom.
- Admin vidi zbirni pregled i pojedinačni pregled svake optike.
- Novi prodavac se kreira sa obaveznom optikom.
- Deaktivirana optika ne prima nova zaduženja, ali istorija ostaje dostupna administratoru.
- Godišnji dugovi i uplate se tačno slažu po optici i za zbir svih optika.
- Dodela prodavca, premeštanje i administrativne izmene ulaze u audit log.
- Migracija v1 podataka čuva broj dužnika, dugova i uplata i dodeljuje ih početnoj optici.
- V1 tag ostaje moguće pokrenuti nezavisno od v2 razvoja.
