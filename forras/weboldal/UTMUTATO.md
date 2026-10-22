# 8dnavigator.com – útmutató

## 1. Telepítés (egyszer kell, a tárhely kezelőjének)

1. Tölts fel a tárhely webes gyökérmappájába (általában `public_html`) mindent ebből a mappából, **kivéve** a `_build` mappát és ezt a fájlt.
   A pontosponttal kezdődő `.htaccess` fájlokat is töltsd fel (a gyökérben, a `content`, `inc` és `uploads` mappában).
2. A tárhelyen legyen **PHP 8.0 vagy újabb** (Nethelyen az ügyfélkapuban állítható).
3. A `content` és az `uploads` mappa legyen **írható** a PHP számára (általában alapból az).
4. Legyen a tárhelyen egy **info@8dnavigator.com** postafiók, mert erről mennek ki az értesítők.
5. Nyisd meg: `https://8dnavigator.com/admin/`
   – Kéri a **telepítési kódot**: ez a `content/setup-code.txt` fájlban van.
   – Válassz jelszót (legalább 10 karakter). A kódfájl ezután magától törlődik.
6. Ellenőrzés élesítés után:
   – Küldj egy próbaüzenetet a Contact oldalon. Meg kell jelennie az admin „Üzenetek” menüjében, és e-mailben is meg kell érkeznie.
   – Nyisd meg böngészőben: `https://8dnavigator.com/content/content.json`. **Tiltott (403) hibát kell kapnod.** Ha a fájl tartalma megjelenik, szólj, mert akkor a tárhely nem veszi figyelembe a `.htaccess` fájlokat.
7. A régi WordPress fájlokat ne töröld, hanem mozgasd át egy biztonsági mentés mappába a tárhelyen kívülre.

## 2. Napi használat (a karbantartónak)

Belépés: `https://8dnavigator.com/admin/` → jelszó.

| Mit szeretnél? | Hol? |
|---|---|
| Szöveget javítani a főoldalon | **Főoldal** → átírod → **Mentés** |
| Új kérdést a GYIK-ba | **Kérdések (GYIK)** → „+ Új kérdés” |
| Új sablont közzétenni | 1. **Fájlok és képek** → Feltöltés<br>2. **Erőforrások** → „+ Új dokumentum” → „Csatolt fájl”-nál kiválasztod |
| Egy dokumentumot ideiglenesen elrejteni | **Erőforrások** → a dokumentumnál kiveszed a pipát: „Megjelenik az oldalon” |
| Hogy a látogató azonnal letölthesse a fájlt | a dokumentumnál bepipálod: „Szabadon letölthető” (különben űrlapon kéri, és te küldöd el e-mailben) |
| Kicserélni a főoldali képet | **Fájlok és képek** → Feltöltés, „Legyen ez a főoldali kép” bepipálva |
| Válaszolni egy érdeklődőnek | **Üzenetek** → kinyitod → „Válasz e-mailben” |
| Visszacsinálni egy rossz módosítást | **Korábbi verziók** → a hiba előtti időpontnál „Visszaállítás” |

**Formázás a szövegekben**

- `**szöveg**` → **félkövér**
- `[szöveg]` → kiemelés (a főcímben piros)
- üres sor → új bekezdés
- ahol a mező alatt „Soronként egy elem” áll, ott minden sor külön listaelem

**Jó tudni**

- A weboldal szövegei angolul vannak, az admin magyarul.
- A listák elemeit a ↑ ↓ nyilakkal lehet sorba rendezni, a ✕ töröl. Minden csak a **Mentés** után lesz végleges.
- Mentés billentyűvel: **Ctrl + S**.
- Az űrlapüzeneteket a rendszer a „Beállítások”-ban megadott idő (alapból 24 hónap) után automatikusan törli. Ezt a GDPR írja elő.
- Ha elfelejtetted a jelszót: a tárhelyen töröld (vagy nevezd át) a `content/auth.json` fájlt, és hozz létre egy `content/setup-code.txt` fájlt egy tetszőleges kóddal. Utána az admin újra az első beállítást kéri.
