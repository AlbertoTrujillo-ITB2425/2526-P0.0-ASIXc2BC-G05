# 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Introducció](#introducció)
2. [Objectius](#objectius)
3. [Planificació del projecte](#planificació-del-projecte)
4. [Esquema de xarxa](#esquema-de-xarxa)
    - [Descàrrega de l'esquema](#descàrrega-de-lesquema)
    - [Visualització de l'esquema](#visualització-de-lesquema)
5. [Infraestructura desplegada](#infraestructura-desplegada)
6. [Configuració de serveis](#configuració-de-serveis)
7. [Proves realitzades](#proves-realitzades)
8. [Conclusions i millores](#conclusions-i-millores)

---

## 1. Introducció

Aquest document recull la memòria tècnica del projecte **P0.0-ASIXc2BC-G05** corresponent al Mòdul 0379, Projecte Intermodular d'Administració de Sistemes Informàtics en Xarxa. L'objectiu principal és desplegar una infraestructura per a una aplicació multicapa que integra diversos serveis essencials (web, monitoratge de xarxes, SSH, BBDD, DHCP, DNS i FTP) en diferents màquines virtuals i subxarxes, seguint un model de segmentació per zones DMZ, Intranet i NAT.

---

## 2. Objectius

- Dissenyar i desplegar una infraestructura funcional per allotjar una aplicació multicapa.
- Planificar i documentar exhaustivament totes les tasques del projecte.
- Instal·lar, configurar i posar en producció els serveis requerits (Web, BBDD, DNS, DHCP, FTP, etc.).
- Carregar i visualitzar dades obertes a la base de dades.
- Garantir l’accés centralitzat a tots els sistemes mitjançant l’usuari `bchecker`.
- Documentar l’arquitectura mitjançant un diagrama de xarxa clar i complet.

---

## 3. Planificació del projecte

La planificació s'ha realitzat utilitzant **Proofhub**, estructurant el treball en 3 sprints quinzenals de 10 hores cadascun. El backlog i la divisió de tasques han estat definits segons la nomenclatura i els requeriments especificats:

- **Sprints:** 3 (2 setmanes cadascun, 5 h/setmana)
- **Durada total:** 6 setmanes (fins al 18/11)
- **Gestió de documentació i configuració:** tot versionat al repositori git amb el nom **P0.0-ASIXc2BC-G05**

---

## 4. Esquema de xarxa

L’arquitectura desplegada es representa en el següent diagrama:

### Descàrrega de l'esquema

Podeu descarregar el fitxer de l’esquema de xarxa aquí:  
[Descarregar esquema de xarxa (Packet Tracer)](https://drive.google.com/file/d/1atEO0mJYaNl4XfbM8BtlbaUDIN4p2D8S/view?usp=sharing)

### Visualització de l'esquema

A continuació es mostra una imatge representativa de l’esquema de xarxa:

![Esquema de Xarxa](https://github.com/user-attachments/assets/12fdae6a-c0b8-4ae6-8dcf-2a22dcaad1b4)

---
