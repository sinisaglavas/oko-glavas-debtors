# Plan izrade aplikacije za evidenciju dužnika u optici

Status: plan za realizaciju; poslovne funkcionalnosti još nisu implementirane.

Engleska verzija: [Implementation plan](implementation-plan-en.md).

## 1. Cilj i obim prve verzije

Jednostavna interna aplikacija za unos dužnika, pregled zaduženja po godinama, evidentiranje uplata i automatski obračun preostalog duga. Interfejs je na srpskom, prilagođen računaru i telefonu.

Prva verzija obuhvata:

- Prijavu zaposlenog i odjavu, bez javne registracije.
- Unos i izmenu imena, prezimena, adrese, mesta stanovanja i broja telefona.
- Unos iznosa zaduženja, datuma zaduženja i broja rata od 2 do 24.
- Godišnji pregled dužnika sa pretragom i statusom otplate.
- Posebnu stranicu dužnika sa njegovim zaduženjima, uplatama i ostatkom duga.
- Unos uplate i storniranje pogrešne uplate uz sačuvanu istoriju.
- Ukupan početni dug, ukupno uplaćeno i preostali dug svih dužnika za izabranu godinu.

## 2. Polazne pretpostavke

Ovo su predložena pravila za izradu, a ne dodatni zahtevi korisnika:

- Jedna optika i jedna valuta, podrazumevano RSD; nema kamata ni penala.
- Godina evidencije određuje se prema datumu nastanka zaduženja, a ne prema datumu uplate.
- Ista osoba može imati više zaduženja u istoj ili različitim godinama, bez ponovnog unosa ličnih podataka.
- Rate su jednake koliko dozvoljava zaokruživanje; poslednja rata pokriva razliku. Uplata može biti delimična ili pokriti više rata.
- Broj uplata nije isto što i broj otplaćenih rata. Napredak otplate računa se prema iznosima.
- „Single page” znači zasebnu stranicu sa svim podacima dužnika. Za prvu verziju nije potrebna posebna SPA arhitektura.
- Automatski rokovi dospeća, kašnjenja, SMS poruke, štampa potvrda, izvoz i integracija sa kasom ostaju za narednu fazu.

## 3. Ekrani i tok rada

### Godišnji pregled

Na vrhu su izbor godine (tekuća kao početna), dugme „Novi dužnik / zaduženje” i tri kartice: „Početni dug”, „Uplaćeno” i „Preostali dug”. Dostupne su i prethodne godine u kojima postoje zaduženja. Godina bez podataka prikazuje nule i poruku za prazan spisak.

Tabela prikazuje ime i prezime, mesto, telefon, zbir zaduženja za izabranu godinu, uplaćeno, preostalo i status. Ako osoba ima više zaduženja u toj godini, prikazuje se jednom sa zbirnim iznosima. Pretraga radi po imenu, prezimenu i telefonu; filter statusa razlikuje aktivne i izmirene dugove. Lista ima paginaciju. Godišnje kartice uvek predstavljaju celu izabranu godinu, nezavisno od pretrage i stranice tabele.

### Unos dužnika i zaduženja

Zaposleni prvo bira postojeću osobu ili unosi novu. Obavezna polja za osobu su ime, prezime, adresa, mesto i telefon. Za zaduženje su obavezni datum, pozitivan iznos i ceo broj rata od 2 do 24. Godina se prikazuje na osnovu datuma, uz mogućnost unosa starijih zaduženja. Telefon se čuva kao tekst, sa podrškom za `+` i početne nule.

Pre čuvanja prikazati ukupan dug i informativne iznose rata. Podudaranje imena i telefona upozorava na mogući duplikat, ali samo ime ili zajednički porodični telefon ne smeju automatski zabraniti unos.

### Stranica dužnika

Prikazati kontakt podatke, ukupan preostali dug osobe kroz sve godine i listu zaduženja grupisanu po godinama. Dolazak iz godišnjeg pregleda otvara izabranu godinu, uz jasno označene godišnje i ukupne iznose.

Za svako zaduženje prikazati početni iznos, broj rata, informativni raspored iznosa rata, ukupno uplaćeno, preostalo i status „Aktivan” ili „Izmiren”. Pored njega je obrazac „Upiši uplatu”: iznos, datum uplate i opciona napomena. Zaduženje kojem uplata pripada mora biti nedvosmisleno izabrano.

Istorija uplata prikazuje datum, iznos, napomenu, zaposlenog koji je uneo uplatu i oznaku eventualnog storna. Nakon čuvanja odmah osvežiti stanje. Za storniranje tražiti potvrdu i razlog; originalna uplata ostaje vidljiva.

## 4. Model podataka

| Entitet | Ključna polja i pravila |
| --- | --- |
| `users` | Postojeći Laravel korisnici; pristup samo zaposlenima. |
| `debtors` | `id`, `first_name`, `last_name`, `address`, `city`, `phone`, vremenske oznake. |
| `debts` | `id`, `debtor_id`, `incurred_on`, `original_amount_minor`, `installment_count`, `created_by`, vremenske oznake. Godina se izvodi iz `incurred_on`. |
| `payments` | `id`, `debt_id`, `amount_minor`, `paid_on`, `note`, `created_by`, jedinstveni `submission_token`, `voided_at`, `voided_by`, `void_reason`, vremenske oznake. |

Veze: dužnik ima više zaduženja; zaduženje ima više uplata. Iznose čuvati kao cele brojeve u najmanjoj novčanoj jedinici (para za RSD), bez računanja preko `float` vrednosti. Indeksirati strane ključeve i datum zaduženja. Godišnje upite ograničiti rasponom datuma.

Preostali dug i status izvoditi iz početnog iznosa i važećih uplata, bez nezavisnog ručnog održavanja tih vrednosti. Za prvu verziju nije potrebna posebna tabela rata jer se plan iznosa izvodi iz iznosa duga i broja rata.

## 5. Obračun i pravila integriteta

- Uplaćeno = zbir svih nestorniranih uplata konkretnog zaduženja.
- Preostalo = početni iznos zaduženja − uplaćeno.
- Dug je izmiren kada je preostalo tačno nula.
- Godišnji početni dug = zbir početnih iznosa zaduženja nastalih u toj godini.
- Godišnje uplaćeno = zbir važećih uplata na ta zaduženja, uključujući uplate iz kasnijih godina.
- Godišnji preostali dug = godišnji početni dug − godišnje uplaćeno. Ovo je trenutno stanje zaduženja iz godine, a ne istorijski presek na 31. decembar.
- Prvih N−1 rata dobija celobrojni količnik ukupnog iznosa u parama i N; poslednja dobija ostatak do punog iznosa.
- Uplata mora biti pozitivna i ne sme premašiti preostali dug. Datum uplate ne sme prethoditi zaduženju niti biti u budućnosti; datum zaduženja ne sme biti u budućnosti.
- Čuvanje uplate i ponovna provera ostatka odvijaju se u jednoj transakciji sa zaključavanjem zaduženja. Jedinstveni token sprečava duplo knjiženje istog slanja obrasca.
- Storno koristi istu zaštitu od konkurentnih izmena i ponovo otvara dug ako ostatak postane pozitivan. Ista uplata može biti stornirana samo jednom.
- Posle prve evidentirane uplate zaključati iznos, datum i broj rata zaduženja, čak i ako je uplata kasnije stornirana. Kontakt podaci ostaju izmenjivi. Naknadne korekcije zaduženja zahtevaju zaseban budući postupak sa istorijom.
- Ne omogućavati trajno brisanje dužnika sa zaduženjima niti zaduženja sa istorijom uplata u prvoj verziji.

Primer: dug iz 2026. je 24.000 RSD na 12 rata po 2.000 RSD. Nakon uplata od 2.000 i 3.000 RSD, uplaćeno je 5.000, a preostalo 19.000 RSD. Ako druga uplata stigne 2027, i dalje umanjuje stanje zaduženja iz 2026. Dug od 10.000 RSD na 3 rate daje 3.333,33 + 3.333,33 + 3.333,34 RSD.

## 6. Tehnički pristup

Zadržati postojeći Laravel projekat, Blade prikaze, Tailwind CSS i Vite. Koristiti serverske forme i standardne kontrolere; JavaScript dodavati samo za unapređenje unosa i prikaza. Plan ne zahteva poseban frontend framework.

Koristiti migracije i Eloquent modele za podatke, Form Request klase za validaciju i servis za upis/storniranje uplata i obračun. Za zajednički produkcioni rad planirati MySQL, sa podrškom za transakcije i zaključavanje redova; završne provere konkurentnih uplata izvršiti na istoj vrsti baze. Razvojnu bazu izabrati u fazi pripreme.

Sve poslovne stranice zaštititi prijavom, serverskom autorizacijom i CSRF zaštitom. Prvu verziju mogu koristiti zaposleni sa istim ovlašćenjima. Lozinke čuvati preko Laravel mehanizma; lične podatke ne zapisivati u nepotrebne logove. Produkcija koristi HTTPS, dnevni rezervni primerak baze i proverenu proceduru vraćanja podataka.

## 7. Redosled realizacije

1. **Priprema:** potvrditi polazna pravila, podesiti bazu, srpski interfejs, valutu i vremensku zonu Europe/Belgrade; napraviti prijavu i nalog zaposlenog. Ishod: zaštićen pristup aplikaciji.
2. **Podaci i obračun:** migracije, modeli, veze, validacija, servis uplata i storna, fabrike i probni podaci. Ishod: proverljivi obračuni i zaštita integriteta.
3. **Godišnji pregled i unos:** izbor godine, zbirne kartice, pretraga, filteri, paginacija i forme. Ishod: unos osobe i zaduženja sa ispravnim prikazom po godinama.
4. **Stranica dužnika:** kontakt podaci, zaduženja, rate, istorija, unos i storno uplata. Ishod: kompletan dnevni tok rada zaposlenog.
5. **Provere i isporuka:** automatizovani testovi obračuna i pristupa, ručna provera na telefonu i računaru, produkcioni build, podešavanje rezervnih kopija i kratko korisničko uputstvo. Ishod: aplikacija spremna za unos stvarnih podataka.

## 8. Kriterijumi prihvatanja

- Moguće je sačuvati sva obavezna polja i zaduženja na 2 i 24 rate; 1, 25 i decimalni broj rata se odbijaju.
- Ista osoba ima zaduženja u dve godine; godišnji pregledi prikazuju samo odgovarajuća zaduženja, a profil jasno prikazuje oba.
- Delimična uplata, uplata više rata odjednom i konačna uplata daju tačan ostatak i status.
- Nulti, negativni i previsoki iznosi, kao i nedozvoljeni datumi, daju jasnu poruku bez izmene stanja.
- Primeri iz odeljka 5 daju navedene rezultate, uključujući zaokruživanje i uplatu u narednoj godini.
- Dvostruko slanje istog obrasca knjiži jednu uplatu; dve konkurentne uplate ne mogu preplatiti dug.
- Storno čuva originalnu uplatu, zaposlenog i razlog, i tačno menja zbirne iznose.
- Godišnje kartice se slažu sa sumom zaduženja cele godine i ostaju iste pri pretrazi ili promeni stranice tabele.
- Neprijavljen korisnik nema pristup podacima niti može upisati uplatu direktnim zahtevom.
- Prazna godina, izmiren dug i dužnik sa više zaduženja imaju jasan prikaz; vraćanje rezervne kopije je provereno pre produkcione upotrebe.

## 9. Trenutno stanje projekta

Repozitorijum sada sadrži pripremnu fazu i fazu podataka i obračuna: prijavu i odjavu, kolonu `is_admin`, administrativni middleware, modele dužnika, zaduženja i uplata, validaciju, servis knjiženja/storna i testove obračuna i kontrole pristupa. PHP, Composer, Laravel Boost i frontend build su podešeni. Ekrani za unos, godišnji pregled i profil dužnika slede u narednom koraku.
