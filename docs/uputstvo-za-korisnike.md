# Kratko uputstvo za korisnike

## Prijava

Prijavite se svojim nalogom. Prodavac može da unosi dužnike, nova zaduženja i uplate. Administrator ima i mogućnost storna uplate.

## Godišnji pregled

Na stranici **Dužnici po godini** izaberite godinu, po potrebi unesite pretragu ili status i kliknite **Primeni**. Kartice prikazuju početni dug, ukupno uplaćeno i preostali dug za celu godinu. Klik na ime otvara stranicu dužnika.

## Unos dužnika i zaduženja

Kliknite **Novi dužnik / zaduženje**. Izaberite postojeću osobu ili unesite podatke nove osobe, zatim datum, ukupan iznos i broj rata od 2 do 24. Iznosi se unose u dinarima, a aplikacija računa raspored rata i ostatak.

## Uplata i storno

Na stranici dužnika izaberite zaduženje, unesite iznos, datum i opcionu napomenu, pa sačuvajte uplatu. Uplata ne može biti veća od preostalog duga. Administrator može da stornira uplatu unosom obaveznog razloga; original ostaje u istoriji.

## Rezervna kopija

Pre produkcione upotrebe podesiti dnevno kopiranje baze i proveriti vraćanje kopije na odvojenom razvojnom okruženju. Kopije i `.env` datoteku čuvati van javnog direktorijuma i sa ograničenim pristupom.
