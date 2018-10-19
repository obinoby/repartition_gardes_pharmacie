# Répartition des gardes de pharmacies
Répartition des tours de gardes jour par jour pour les pharmacies.
Ce logiciel a été créé pour générer une fois un tour de garde pour un groupe de 14 pharmacies.
Il a ensuite été adapté pour pouvoir relancer la génération à partir d'autres dates.
Quelques autres améliorations sont venues après comme la mise à disposition d'un lien ics pour le calendrier de chaque pharmacie.

Il manque de l'ergonomie et de la sécurité (aucune authentification n'est assurée pour l'instant).

## Pour qui
Ce dépot existe avant tout pour me permettre de garder ce développement sous le coude.
Il sert aussi à m'assurer que ce développement ne sera pas récupéré à des fins commerciales.

## Evolutions
J'invite qui voudra à continuer le développement de cet outil comme il l'entend.
Personnellement je ne pense pas y retoucher.

## Installation
### Pré-requis
Le logiciel est conçu pour fonctionner sur un serveur web équipé de php5 et MySQL.

### Installation des scripts php
Placer les fichiers à la racine de votre vHost Apache ou nginx.

### Base de données
Créer une base de données dédiée.
Restaurer le dump gardes.sql pour créer les tables

### Configurer l'accès à la base de données
Copier le fichier connect_sample.php vers connect.php.
Modifier le contenu de connect.php en fonction des besoins.

# Usages
## 1. Ajouter les pharmaciens
Cette opération se fait directement dans MySQL.
Il faudra surement modifier les adresses emails si import de la base de donnée historique.

## 2. Lancer une génération
<URL>/calculs.php?startyear=2022
Cette adresse lancera une génération à partir de l'année 2022. Si l'arguement startyear n'est pas fourni alors l'année de départ sera l'année prochaine.
  
### Regles de génération
#### Ordre d'attribution
1. Calculer les jourts fériés français et les placer dans le calendrier
2. Attrinuer les Noels et Jours de l'an
3. Attribuer les jours fériés
4. Attribuer les weekends
5. Attribuer les autres jours

#### Règles d'attribution
- Sélection au hasard parmi une sélection de pharmacies qui sont à égalité sur le plus bas nombre de ces jours attribués.
- Un jour férié (y compris Noel et Jour de l'an) entraine systématiquement l'attribution du jour ouvré précédant à la même pharmacie.
- Un samedi entraine systématiquement l'attribution du dimanche suivant à la même pharmacie.

## 3. Alertes par email
Si besoin configurer un CRON quotidien le matin qui appelera le script bash gardes-alertes.sh
Ce script intéroge MySQL pour déterminer l'adresse email et le nom de la pharmacie qui doit prendre son tour de garde et lui envoyer une email.
