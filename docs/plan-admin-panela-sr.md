# Plan admin panela

Admin panel je centralno mesto za upravljanje korisnicima, kontrolu finansijskih podataka i izveštaje. Prodavac zadržava jednostavan rad sa dužnicima i uplatama, dok administrator dobija dodatne funkcije za kontrolu i ispravke.

## 1. Kontrolna tabla

- ukupan početni dug, ukupno uplaćeno i preostali dug;
- broj aktivnih i izmirenih dužnika;
- pregled po godinama;
- poslednje uplate i najnovija zaduženja;
- upozorenja za nepotpune ili sumnjive podatke.

## 2. Upravljanje korisnicima

- kreiranje prodavca i administratora;
- izmena imena, emaila i statusa naloga;
- reset lozinke;
- deaktivacija korisnika;
- prikaz poslednje prijave;
- zaštita od brisanja korisnika koji ima evidentirane izmene.

## 3. Upravljanje dužnicima

- pretraga i napredni filteri;
- izmena kontakt podataka;
- spajanje duplih dužnika;
- pregled svih zaduženja kroz godine;
- arhiviranje bez trajnog brisanja.

## 4. Upravljanje zaduženjima

- korekcija iznosa, datuma i broja rata samo uz obavezan razlog;
- istorija svih izmena;
- zaključavanje finansijskih podataka nakon prve uplate;
- prikaz nepotpunih ili neispravnih zaduženja;
- zabrana trajnog brisanja zaduženja sa istorijom uplata.

## 5. Upravljanje uplatama

- pregled svih uplata;
- pretraga po datumu, korisniku i dužniku;
- storno uz obavezan razlog;
- prikaz korisnika koji je uneo i korisnika koji je stornirao uplatu;
- zaštita od duplog unosa i preplate.

## 6. Izveštaji

- godišnji pregled dugova;
- promet uplata po periodu;
- izveštaj po prodavcu;
- lista aktivnih i izmirenih dugova;
- izvoz u CSV, Excel i PDF;
- verzija izveštaja pogodna za štampu.

## 7. Revizija i bezbednost

Audit log treba da beleži prijave, izmene dužnika, izmene zaduženja, uplate i storna. Za svaku finansijsku izmenu čuvati korisnika, vreme, razlog i prethodnu i novu vrednost. Rizične akcije zahtevaju potvrdu i serversku autorizaciju.

## 8. Podešavanja aplikacije

- naziv optike;
- valuta i format prikaza novca;
- podrazumevani broj rata;
- vremenska zona;
- pravila arhiviranja;
- status integracija i sistemskih obaveštenja.

## 9. Backup i održavanje

- pregled poslednje rezervne kopije;
- ručno pokretanje backupa;
- status baze i aplikacije;
- evidencija neuspelih backup operacija;
- provera vraćanja podataka u odvojenom okruženju.

## 10. Predloženi redosled izrade

1. Kontrolna tabla i upravljanje korisnicima.
2. Audit log i administrativne dozvole.
3. Izmena i arhiviranje dužnika.
4. Kontrola zaduženja i uplata.
5. Izveštaji i izvoz podataka.
6. Podešavanja i nadzor backup-a.

Najvažnije pravilo je da administrator može da ispravlja podatke, ali svaka finansijska izmena mora ostati zabeležena: ko ju je izvršio, kada, šta je promenjeno i zbog čega.
