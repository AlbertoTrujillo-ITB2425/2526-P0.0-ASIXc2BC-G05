# Manual d'Usuari - Sistema Equipaments Educació

## Índex

1. [Introducció](#introducció)
2. [Accés al sistema](#accés-al-sistema)
3. [Utilització de l'aplicació web](#utilització-de-laplicació-web)
4. [Serveis disponibles](#serveis-disponibles)
5. [Preguntes freqüents](#preguntes-freqüents)
6. [Resolució de problemes](#resolució-de-problems)
7. [Contacte i suport](#contacte-i-suport)

---

## 1. Introducció

Benvingut al sistema d'informació d'equipaments educatius. Aquest sistema permet consultar informació sobre centres educatius i altres equipaments relacionats amb l'educació.

### Característiques principals:
- **Consulta d'equipaments educatius** amb informació detallada
- **Accés segur** mitjançant connexió HTTPS
- **Interfície web** intuïtiva i fàcil d'utilitzar
- **Informació geolocalitzada** dels centres

---

## 2. Accés al sistema

### 2.1 Requisits del sistema

**Navegadors compatibles:**
- Google Chrome (versió 90 o superior)
- Mozilla Firefox (versió 88 o superior)
- Microsoft Edge (versió 90 o superior)
- Safari (versió 14 o superior)

**Connexió a internet:**
- Connexió estable a internet
- Accés a la xarxa corporativa o VPN si és necessari

### 2.2 Com accedir

1. **Obre el navegador web**
2. **Introdueix l'adreça:** `https://g5.cat`
3. **Accepta el certificat de seguretat** si el navegador ho demana
4. **Espera que carregui** la pàgina principal

⚠️ **Nota de seguretat:** El sistema utilitza un certificat SSL autosignat. És normal que el navegador mostri un avís de seguretat la primera vegada. Pots continuar de forma segura.

---

## 3. Utilització de l'aplicació web

### 3.1 Pàgina principal

Quan accedeixis al sistema, veuràs:

<img width="1775" height="650" alt="Pàgina principal" src="https://github.com/user-attachments/assets/d36ccf60-b453-4f5d-977e-124bfbc8baa2" />

### 3.2 Funcionalitats disponibles

#### Consulta d'equipaments educatius
- **Cerca per nom:** Introdueix el nom del centre que busques
- **Filtra per districte:** Selecciona el districte d'interès
- **Filtra per tipus:** Escoles, instituts, centres de formació, etc.
- **Visualitza ubicació:** Consulta la localització exacta

#### Informació detallada
Cada equipament mostra:
- **Nom del centre**
- **Adreça completa**
- **Tipus d'equipament**
- **Horaris** (si està disponible)
- **Coordenades geogràfiques**
- **Informació addicional**

---

## 4. Serveis disponibles

### 4.1 Servidor web (W-NCC)
- **Funció:** Aplicació web principal
- **Adreça:** https://g5.cat
- **Protocol:** HTTPS segur

### 4.2 Servidor de fitxers (F-NCC)
- **Funció:** Compartició de documents i recursos
- **Accés:** A través de la xarxa corporativa

### 4.3 Connectivitat de xarxa
- **DHCP automàtic:** Els clients obtenen IP automàticament
- **Resolució DNS:** Configuració automàtica
- **Accés a internet:** A través del router corporatiu

---

## 5. Preguntes freqüents

### ❓ No puc accedir a la web
**Possible causa:** Problema de conectivitat o certificat SSL
**Solució:** 
1. Comprova la connexió a internet
2. Accepta el certificat de seguretat del navegador
3. Contacta amb l'administrador si persisteix

### ❓ La web carrega lentament
**Possible causa:** Sobrecàrrega del servidor o connexió lenta
**Solució:**
1. Actualitza la pàgina (F5)
2. Comprova la velocitat de la connexió
3. Tanca altres aplicacions que utilitzin internet

### ❓ No trobo la informació que busco
**Possible causa:** L'equipament no està a la base de dades
**Solució:**
1. Verifica l'ortografia del nom
2. Prova amb diferents termes de cerca
3. Contacta amb suport per afegir nous equipaments

### ❓ Els meus fitxers no es comparteixen correctament
**Possible causa:** Problema amb el servidor de fitxers
**Solució:**
1. Comprova que estàs connectat a la xarxa corporativa
2. Verifica els permisos d'accés
3. Contacta amb l'administrador del sistema

---

## 6. Resolució de problemes

### 6.1 Problemes de connexió

#### Error "Lloc no segur" o "Certificat no vàlid"
1. Fes clic a **"Avançat"** o **"Opcions avançades"**
2. Selecciona **"Continuar cap a g5.cat (no segur)"**
3. La pàgina hauria de carregar correctament

#### No es pot connectar al servidor
1. **Comprova la connexió:** Verifica que tens accés a internet
2. **Comprova la IP:** Assegura't que tens una IP vàlida (normalment assignada per DHCP)
3. **Contacta suport:** Si el problema persisteix

### 6.2 Problemes de rendiment

#### La web va lenta
1. Tanca pestanyes innecessàries del navegador
2. Desactiva extensions del navegador temporalment
3. Reinicia el navegador
4. Comprova la connexió de xarxa

#### Errors de càrrega de dades
1. Actualitza la pàgina
2. Neteja la memòria cau del navegador
3. Prova amb un navegador diferent

### 6.3 Problemes amb el DHCP

#### No obtic IP automàticament
1. **Windows:** 
   - Obre `cmd` com a administrador
   - Executa: `ipconfig /release` i després `ipconfig /renew`
2. **Linux:**
   - Executa: `sudo dhclient -r` i després `sudo dhclient`

---
