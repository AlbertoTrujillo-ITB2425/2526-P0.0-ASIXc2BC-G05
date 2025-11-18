# Manual d'Usuari — Sistema Equipaments Educació

## Índex

1. [Què és aquest sistema?](#1-què-és-aquest-sistema)  
2. [Com accedir](#2-com-accedir)  
3. [Com utilitzar la web](#3-com-utilitzar-la-web)  
4. [Serveis disponibles](#4-serveis-disponibles)  
5. [Preguntes freqüents (FAQ)](#5-preguntes-freqüents-faq)  
6. [Si tens problemes…](#6-si-tens-problemes)  

---

## 1. Què és aquest sistema?

És una **aplicació web** que et permet consultar informació sobre **equipaments educatius** (escoles, instituts, etc.).

### Què hi pots fer?

- Veure **llista de centres** i dades bàsiques.
- Filtrar per **nom**, **districte** o **tipus de centre**.
- Veure **adreça** i **localització** aproximada.
- Accedir de forma **segura** via **HTTPS**.

No cal tenir coneixements tècnics; només un navegador web actualitzat.

---

## 2. Com accedir

### 2.1 Requisits

- Un d’aquests navegadors actualitzats:
  - Google Chrome  
  - Mozilla Firefox  
  - Microsoft Edge  
  - Safari  
- Connexió a internet (o a la xarxa del centre / VPN si s’indica).

### 2.2 Passos per entrar a la web

1. Obre el navegador.  
2. Escriu l’adreça a la barra:  

   `https://g5.cat`  

3. Si el navegador mostra un avís de seguretat:
   - Fes clic a **“Avançat”**.  
   - Fes clic a **“Continuar”** o similar.  

> El certificat és **autofirmat** (de prova). És normal veure un avís la primera vegada.

4. Espera uns segons fins que es carregui la pàgina principal.

---

## 3. Com utilitzar la web

### 3.1 Pàgina principal

Quan entres veuràs una pàgina amb:

- Encapçalament amb el títol del sistema.  
- Zona de **cerca o filtres** (nom, districte, tipus…).  
- Una **taula o llista** amb els equipaments educatius.  

(La disposició exacta pot variar segons la versió, però sempre tindrà una zona de llista i una zona de filtres.)

### 3.2 Cercar un equipament

Opcions típiques:

- **Cerca per nom**  
  - Escriu el nom (o part del nom) del centre.  
  - Prem **“Cercar”** o el botó equivalent.

- **Filtrar per districte**  
  - Tria un districte del desplegable (si existeix).  
  - S’actualitza la llista de centres.

- **Filtrar per tipus**  
  - Exemples: escola, institut, centre de formació…  
  - Selecciona el tipus i s’actualitza la llista.

### 3.3 Veure informació detallada

A la taula/llista, per cada equipament acostumaràs a veure:

- **Nom del centre**  
- **Adreça**  
- **Tipus d’equipament**  
- (Opcional) **Horari**  
- (Opcional) **Coordenades / informació de localització**

En alguns casos pot aparèixer un enllaç o botó del tipus **“Més informació”**.

---

## 4. Serveis disponibles

No cal que configuris res, però és útil saber què hi ha “per sota”.

### 4.1 Web (W‑NCC)

- Allà és on viu l’aplicació que veus al navegador.  
- Adreça: `https://g5.cat`  

### 4.2 Servidor de fitxers (F‑NCC)

- El centre pot tenir un servidor de fitxers associat.  
- L’accés normalment és només **intern** (xarxa del centre).

### 4.3 Xarxa i connexió

- Les màquines solen obtenir la IP **automàticament (DHCP)**.  
- La resolució de noms (DNS) s’aplica també de forma automàtica.  

Si estàs en un ordinador del centre, normalment **ja està tot preparat**.

---

## 5. Preguntes freqüents (FAQ)

### ❓ No puc accedir a la web

**Possibles causes:**

- No tens connexió a internet.  
- No estàs dins la xarxa del centre (si cal).  
- No has acceptat el certificat de seguretat.

**Què puc fer?**

1. Comprova que pots obrir altres pàgines (p. ex. `https://www.google.com`).  
2. Torna a obrir `https://g5.cat` i:
   - Si surt avís de seguretat, fes **“Avançat” → “Continuar”**.  
3. Si encara no funciona, contacta amb l’administrador del sistema.

---

### ❓ La web va molt lenta

**Possibles causes:**

- Connexió d’internet lenta.  
- Massa pestanyes obertes.  
- Servidor carregat puntualment.

**Què puc fer?**

1. Tanca pestanyes o programes que no calguin.  
2. Prova a **actualitzar** la pàgina (tecla `F5`).  
3. Si el problema és continu, avisa a suport.

---

### ❓ No trobo el centre que busco

**Possibles causes:**

- El nom s’ha escrit de forma diferent.  
- El centre encara no està a la base de dades.

**Què puc fer?**

1. Prova amb parts del nom (per exemple, només una paraula).  
2. Utilitza el filtre per **districte** o **tipus**.  
3. Si tot i així no apareix, pots comunicar-ho a suport perquè revisin la base de dades.

---

## 6. Si tens problemes

### 6.1 Problemes de connexió o “lloc no segur”

1. Torna a provar d’entrar a: `https://g5.cat`  
2. Si el navegador diu **“Lloc no segur”** o **“Certificat no vàlid”**:
   - Fes clic a **“Més informació”** o **“Avançat”**.  
   - Escull **“Continuar amb el lloc”**.  

Si després d’això continua sense carregar:

- Comprova si tens internet.  
- Si estàs a casa, consulta si cal VPN del centre.  
- Contacta amb el professor o amb el responsable de TI.

### 6.2 No obtens IP (el PC diu “Sense internet”)

Si l’ordinador ha de rebre IP automàticament i veus que no tens xarxa:

**Windows**

1. Obre el menú Inici, escriu `cmd`.  
2. Botó dret → “Executar com a administrador”.  
3. Escriu:

```bash
ipconfig /release
ipconfig /renew
```

**Linux**

1. Obre un terminal.  
2. Escriu:

```bash
sudo dhclient -r
sudo dhclient
```

Si segueix sense funcionar, probablement és un problema de xarxa o del servidor DHCP i ho ha de revisar l’administrador.

---

Si necessites una ajuda més tècnica (per exemple, revisar logs, configuració de xarxa, etc.), consulta el **Manual d’Administrador**: [`docs/ADMIN_MANUAL.md`](ADMIN_MANUAL.md).
