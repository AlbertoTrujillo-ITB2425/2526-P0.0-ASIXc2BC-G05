# Manual d'Usuari — Sistema Equipaments Educació

Guia per a usuaris que accedeixen a l’aplicació web per consultar equipaments educatius.

---

## Índex

1. [Què és aquest sistema?](#1-què-és-aquest-sistema)  
2. [Com accedir](#2-com-accedir)  
3. [Com utilitzar la web](#3-com-utilitzar-la-web)  
4. [Serveis disponibles “per sota”](#4-serveis-disponibles-per-sota)  
5. [Preguntes freqüents (FAQ)](#5-preguntes-freqüents-faq)  
6. [Si tens problemes…](#6-si-tens-problemes)  

---

## 1. Què és aquest sistema?

És una **aplicació web** que permet consultar informació d’**equipaments educatius** (escoles, instituts, centres de formació, etc.).

### Què hi pots fer com a usuari?

- Visualitzar una **llista de centres** amb la informació bàsica.
- Filtrar centres per:
  - **Nom**.
  - **Districte**.
  - **Tipus de centre**.
- Veure:
  - **Adreça** del centre.
  - **Localització aproximada** (si està disponible).

No cal tenir coneixements tècnics: només un navegador web actualitzat.

---

## 2. Com accedir

### 2.1 Requisits

- Un navegador actualitzat (qualsevol d’aquests):
  - Google Chrome  
  - Mozilla Firefox  
  - Microsoft Edge  
  - Safari  
- Connexió:
  - A Internet, o
  - A la **xarxa del centre** / **VPN**, si el professor o administrador t’ho indica.

### 2.2 Passos d’accés

1. Obre el navegador.  
2. A la barra d’adreces, escriu:

   `https://g5.cat`  

3. Si apareix un **avís de seguretat**:
   - Fes clic a **“Avançat”**.  
   - Tria **“Continuar”** o **“Accedir igualment”**.

> El certificat és **autofirmat de proves**. És normal veure aquest avís dins d’un entorn de pràctiques.

4. Espera que es carregui la pàgina principal.  
   - Si trigues massa o no carrega, consulta l’apartat [Si tens problemes…](#6-si-tens-problemes).

---

## 3. Com utilitzar la web

> L’aspecte pot variar lleugerament segons la versió, però l’estructura general és sempre similar.

### 3.1 Pàgina principal

Elements típics que hi trobaràs:

- **Capçalera** amb el títol del sistema.
- **Zona de cerca / filtres**, on pots:
  - Escriure el nom d’un centre.
  - Seleccionar un districte.
  - Triar un tipus de centre.
- **Taula o llista de resultats**:
  - Nom del centre.
  - Adreça.
  - Tipus de centre.
  - Altres dades si estan disponibles.

### 3.2 Buscar un centre

Hi ha diverses formes de trobar un centre:

- **Cerca per nom**
  - Escriu el nom complet o parcial del centre.
  - Prem **“Cercar”** o l’equivalent.
  - La llista s’actualitzarà mostrant només centres que coincideixin.

- **Filtrar per districte**
  - Selecciona un districte del menú desplegable (si està disponible).
  - Es mostraran només els centres d’aquell districte.

- **Filtrar per tipus**
  - Per exemple: escola, institut, centre de formació…
  - En seleccionar un tipus, la llista es filtra automàticament.

> Pots combinar filtres (nom + districte, etc.) per reduir els resultats.

### 3.3 Veure detalls d’un centre

A la taula/llista de resultats:

- Fes clic sobre el **nom** (o sobre un botó del tipus **“Més informació”**, si existeix) per veure:
  - Adreça detallada.
  - Informació de contacte disponible (telèfon, etc.).
  - Altres dades que el sistema ofereixi.

---

## 4. Serveis disponibles “per sota”

No cal que configuris res, però entendre què hi ha al darrere pot ajudar si parles amb el professor o l’administrador.

- **Servidor web (W‑NCC)**  
  Allà és on “viu” l’aplicació web que veus al navegador.

- **Servidor de base de dades (B‑NCC)**  
  Guarda les dades dels equipaments, filtres, adreces, etc.

- **Servidor de fitxers (F‑NCC)**  
  S’utilitza internament per pujar/baixar fitxers relacionats amb el projecte (no és necessari per a un ús normal com a usuari final).

- **DNS i DHCP**  
  Permeten que les màquines del centre:
  - Obtinguin IP automàticament.
  - Resolguin noms com `g5.cat` dins la xarxa.

---

## 5. Preguntes freqüents (FAQ)

### ❓ No puc accedir a la web

**Possibles causes:**

- No tens connexió a Internet.
- No estàs dins de la xarxa del centre (o falta VPN).
- No has acceptat el certificat de seguretat.

**Què pots fer?**

1. Prova d’obrir una altra web (p. ex. `https://www.google.com`).
2. Torna a provar `https://g5.cat`.
3. Si apareix l’avís de seguretat:
   - Ves a **“Avançat”**.
   - Fes clic a **“Continuar”**.
4. Si segueix sense carregar:
   - Comprova que estàs connectat al Wi-Fi/Xarxa correcta.
   - Demana ajuda a l’administrador o professor.

---

### ❓ La web va molt lenta

**Possibles causes:**

- Connexió d’Internet lenta.
- Massa pestanyes o programes oberts.
- El servidor està momentàniament carregat.

**Què pots fer?**

1. Tanca pestanyes que no facin falta.
2. Actualitza la pàgina (`F5`).
3. Si continua igual, avisa el professor o responsable de TI.

---

### ❓ No trobo el centre que busco

**Possibles causes:**

- El nom està escrit d’una manera diferent (accents, abreviatures…).
- El centre no està donat d’alta a la base de dades.

**Què pots fer?**

1. Escriu només **una part del nom** (per ex. una paraula distintiva).
2. Utilitza els **filtres per districte o tipus**.
3. Si no apareix, comunica-ho a suport/professor per revisar la base de dades.

---

## 6. Si tens problemes…

### 6.1 Avís de “lloc no segur” o “certificat no vàlid”

Quan accedeixes a `https://g5.cat` és possible que vegis un avís:

- *“La connexió no és privada”*  
- *“El certificat no és vàlid”*  

Això és normal en aquest entorn de pràctiques.

**Què has de fer?**

1. Fes clic a **“Avançat”** o **“Més informació”**.  
2. Tria l’opció de **“Continuar amb el lloc”** o similar.

Si, tot i això, la pàgina no carrega:

- Comprova la connexió a Internet.
- Comprova si has de fer servir una VPN del centre.
- Demana ajuda al professor o a l’administrador.

---

### 6.2 Sense IP o missatge “Sense Internet”

Si el PC ha de tenir IP automàtica i no tens Internet:

#### En Windows

1. Obre el menú Inici i escriu `cmd`.  
2. Botó dret sobre *Símbol del sistema* → **“Executar com a administrador”**.  
3. Escriu:

```bash
ipconfig /release
ipconfig /renew
```

4. Espera uns segons i comprova si ja tens connexió.

#### En Linux

1. Obre un terminal.  
2. Escriu:

```bash
sudo dhclient -r
sudo dhclient
```

3. Verifica si l’equip ja torna a tenir IP i accés.

Si segueixes sense connexió:

- Pot ser un problema del servidor DHCP o de la xarxa.
- En aquest cas l’ha de revisar l’administrador del sistema.

---

Per a problemes més tècnics (logs, configuracions, xarxa…), l’administrador pot consultar el:  
**Manual d’Administrador** — [`ADMIN_MANUAL.md`](ADMIN_MANUAL.md)
