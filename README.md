# Travel Maroc Agency — Plateforme de Réservation Touristique

[![GitHub](https://img.shields.io/badge/GitHub-travel--maroc--agency-0d2b55?style=flat&logo=github)](https://github.com/joumailabderrahmen-ctrl/travel-maroc-agency)

Application Web de réservation touristique développée dans le cadre du module **Conception et Développement de Logiciels** à l'École Supérieure de Technologie de Dakhla.

> **Dépôt** : https://github.com/joumailabderrahmen-ctrl/travel-maroc-agency

---

## Table des matières

1. [Présentation](#présentation)
2. [Stack technique](#stack-technique)
3. [Fonctionnalités](#fonctionnalités)
4. [Architecture du projet](#architecture-du-projet)
5. [Télécharger et installer le projet](#télécharger-et-installer-le-projet)
6. [Plugin Travel Maroc Booking](#plugin-travel-maroc-booking)
7. [Thème Travel Maroc](#thème-travel-maroc)
8. [Base de données](#base-de-données)
9. [Rôles et permissions](#rôles-et-permissions)
10. [API REST](#api-rest)
11. [Multilingue](#multilingue)
12. [Documentation](#documentation)
13. [Auteur](#auteur)

---

## Présentation

**Travel Maroc Agency** est une plateforme e-commerce de réservation de voyages au Maroc, construite sur WordPress + WooCommerce. Elle permet aux clients de parcourir des offres touristiques, de réserver en ligne avec sélection de date et de voyageurs, et de bénéficier d'un programme de fidélité à points.

L'ensemble des fonctionnalités métier est encapsulé dans un plugin WordPress sur-mesure (`travel-maroc-booking`) et un thème dédié (`travel-maroc`).

---

## Stack technique

| Couche | Technologie |
|---|---|
| CMS | WordPress 6.x |
| E-commerce | WooCommerce 9.x |
| Langage serveur | PHP 8.0+ |
| Base de données | MySQL 8 (via XAMPP) |
| Frontend | HTML5 · CSS3 · JavaScript ES6 |
| Carte interactive | Leaflet.js + OpenStreetMap |
| Animation logo | GSAP 3 |
| Multilingue | Polylang (FR / AR) |
| Serveur local | XAMPP (Apache + MySQL) |

---

## Fonctionnalités

### Côté client
- Catalogue d'offres touristiques avec filtres
- Fiche produit enrichie (carte Leaflet, galerie, infos voyage)
- Checkout avec champs spécifiques voyage (date départ, nb adultes/enfants, demandes spéciales)
- Validation des dates bloquées par produit en temps réel
- Programme de fidélité à points (earn + redeem avec coupon automatique)
- Espace "Mes réservations" enrichi (statut coloré, date départ, voyageurs)
- Interface bilingue Français / Arabe (RTL)

### Côté administration
- Dashboard KPIs : CA total, commandes, points fidélité en circulation
- Export CSV des réservations (UTF-8 BOM, séparateur `;` pour Excel)
- Calendrier de disponibilité par produit (dates bloquées cliquables)
- Gestion des ressources : Guides, Hôtels, Transports, Localisations
- Journal d'audit immuable (LOG_ADMIN) avec filtres IP / utilisateur / action
- Panneau de notifications multi-canal (email, admin, WhatsApp)
- Gestion des rôles personnalisés (`tma_agent`, `shop_manager`)
- Données démo injectables en un clic (Settings → Données démo)

### Technique
- Plugin WordPress PSR-style avec classes statiques + hooks WordPress
- 10 tables MySQL personnalisées (`wp_tma_*`) créées via `dbDelta()`
- REST API JSON (3 endpoints : offres, disponibilités, fidélité)
- Open Graph + Twitter Cards + Schema.org JSON-LD
- SEO sitemap automatique (`/wp-sitemap.xml`)
- Emails transactionnels personnalisés avec lien WhatsApp

---

## Architecture du projet

```
boutique/
├── wp-admin/                         ← WordPress admin (core)
├── wp-includes/                      ← WordPress core
├── wp-*.php                          ← Fichiers racine WordPress
│
├── wp-content/
│   ├── plugins/
│   │   └── travel-maroc-booking/     ← Plugin métier (sur-mesure)
│   │       ├── admin/                ← UI administration
│   │       ├── includes/             ← Classes PHP (logique métier)
│   │       ├── public/               ← Rendu frontend
│   │       └── travel-maroc-booking.php
│   │
│   ├── themes/
│   │   └── travel-maroc/            ← Thème sur-mesure
│   │       ├── assets/              ← CSS · JS · images
│   │       ├── template-parts/      ← Composants réutilisables
│   │       └── woocommerce/         ← Overrides templates WC
│   │
│   └── uploads/                     ← Médias (images offres, vidéos)
│       └── 2026/05/                 ← Photos destinations, témoignages
│
├── GUIDE-UTILISATEUR.md             ← Guide complet par rôle
└── README.md                        ← Ce fichier
```

> **Note :** `wp-config.php` (identifiants BDD) et les plugins tiers (WooCommerce, Polylang) ne sont pas inclus dans le dépôt.

---

## Télécharger et installer le projet

> ℹ️ **Ce dépôt contient WordPress core, le plugin et le thème sur-mesure, ainsi que tous les médias.**  
> Il suffit de copier les fichiers dans XAMPP, créer la base de données, et configurer `wp-config.php`.

---

### Étape 1 — Télécharger le projet depuis GitHub

Tu n'as **pas besoin de connaître Git** pour récupérer le projet.

1. Va sur la page GitHub : **https://github.com/joumailabderrahmen-ctrl/travel-maroc-agency**
2. Clique sur le bouton vert **`<> Code`** (en haut à droite de la liste des fichiers)
3. Dans le menu qui s'ouvre, clique sur **`Download ZIP`**
4. Un fichier `travel-maroc-agency-master.zip` va se télécharger sur ton PC
5. **Fais un clic droit** sur le ZIP → **Extraire tout** → extraire dans `C:\xampp\htdocs\`
6. **Renomme** le dossier extrait `travel-maroc-agency-master` en **`boutique`**

> 💡 Si tu connais Git, tu peux aussi cloner directement dans le bon dossier :
> ```bash
> git clone https://github.com/joumailabderrahmen-ctrl/travel-maroc-agency.git C:\xampp\htdocs\boutique
> ```

---

### Étape 2 — Installer XAMPP

Si XAMPP n'est pas encore installé sur ton PC :

1. Va sur **https://www.apachefriends.org**
2. Télécharge et installe **XAMPP pour Windows**
3. Lance **XAMPP Control Panel**
4. Clique sur **Start** pour **Apache** et **MySQL**
5. Les deux doivent afficher `Running` en vert

---

### Étape 3 — Créer la base de données

1. Ouvre ton navigateur et va sur **http://localhost/phpmyadmin**
2. Clique sur **Nouvelle base de données** (colonne de gauche)
3. Tape `boutique` dans le champ, puis clique sur **Créer**

---

### Étape 4 — Configurer WordPress

1. Dans `C:\xampp\htdocs\boutique\`, trouve le fichier `wp-config-sample.php`
2. **Copie-le** et renomme la copie en `wp-config.php`
3. Ouvre `wp-config.php` avec le Bloc-notes et modifie ces 3 lignes :

```php
define( 'DB_NAME',     'boutique' );  // nom de ta base de données
define( 'DB_USER',     'root' );      // utilisateur XAMPP par défaut
define( 'DB_PASSWORD', '' );          // mot de passe vide par défaut
```

4. Sauvegarde le fichier

---

### Étape 5 — Installer WooCommerce et Polylang

Dans WP Admin (`http://localhost/boutique/wp-admin`) :

1. Va dans **Extensions → Ajouter**
2. Recherche **WooCommerce** → **Installer** → **Activer**
3. Recherche **Polylang** → **Installer** → **Activer**

---

### Étape 6 — Finaliser l'installation WordPress

Va sur **http://localhost/boutique** dans ton navigateur et suis l'assistant :
- Choisis la langue
- Remplis le nom du site, email admin, identifiant et mot de passe
- Clique sur **Installer WordPress**

---

### Étape 7 — Activer le thème et le plugin

Dans WP Admin (`http://localhost/boutique/wp-admin`) :

1. **Apparence → Thèmes** → Clique sur **Activer** sous `Travel Maroc`
2. **Extensions** → Clique sur **Activer** sous `Travel Maroc Booking`

---

### Étape 8 — Injecter les données de démonstration

1. Dans WP Admin, va dans le menu **Travel Maroc → Paramètres**
2. Clique sur **Insérer les données démo**

Cela crée automatiquement : 4 guides, 6 hôtels, 5 moyens de transport, 3 localisations.

---

### ✅ Le site est prêt !

Ouvre **http://localhost/boutique** dans ton navigateur.

---

## Plugin Travel Maroc Booking

```
includes/
├── class-tmb-activator.php     Activation · désactivation · uninstall · seed data
├── class-tmb-fidelite.php      Programme de fidélité (points, niveaux, coupons)
├── class-tmb-log-admin.php     Journal d'audit immuable
├── class-tmb-notifications.php Notifications multi-canal
├── class-tmb-rest-api.php      Endpoints REST JSON
├── class-tmb-checkout.php      Champs voyage au checkout + validation
├── class-tmb-availability.php  Calendrier de disponibilité par produit
└── class-tmb-roles.php         Rôles personnalisés WordPress

admin/
├── class-tmb-admin.php         Dashboard KPIs + export CSV
├── class-tmb-product-meta.php  Meta box produit (guide, hôtel, transport)
├── class-tmb-resources-admin.php CRUD Guides / Hôtels / Transports
└── class-tmb-settings.php      Page de paramètres globaux

public/
└── class-tmb-public.php        Scripts frontend (tmaData, datepicker)
```

### Points de fidélité

| Niveau | Points requis | Réduction |
|---|---|---|
| Standard | 0 | 0 % |
| Silver | 500 | 5 % |
| Gold | 1 500 | 10 % |
| VIP | 5 000 | 15 % |
| Entreprise | Manuel | 20 % |

---

## Thème Travel Maroc

```
travel-maroc/
├── front-page.php          Page d'accueil (hero + offres vedettes)
├── page-a-propos.php       Page À propos
├── page-contact.php        Formulaire de contact
├── assets/
│   ├── css/main.css        Styles principaux + RTL Arabe
│   ├── js/main.js          Interactions frontend
│   └── js/logo-animation.js  Animation GSAP du logo
└── woocommerce/
    ├── archive-product.php   Catalogue offres
    ├── single-product.php    Fiche offre (Leaflet, galerie)
    ├── myaccount/orders.php  Mes réservations enrichi
    ├── checkout/thankyou.php Page de confirmation
    └── emails/               Emails transactionnels personnalisés
```

---

## Base de données

Le plugin crée automatiquement 10 tables personnalisées avec le préfixe `wp_tma_` :

| Table | Description |
|---|---|
| `wp_tma_type_client` | Niveaux de fidélité (Standard → Entreprise) |
| `wp_tma_client_profile` | Profil étendu client (points, niveau, devise) |
| `wp_tma_historique_points` | Historique des transactions de points |
| `wp_tma_localisation` | Hiérarchie géographique (Pays → Région → Ville → POI) |
| `wp_tma_destination` | Destinations touristiques |
| `wp_tma_guide` | Guides avec langues et spécialités |
| `wp_tma_hotel` | Hôtels avec catégorie et services |
| `wp_tma_transport` | Moyens de transport (vol, bus, train, voiture) |
| `wp_tma_log_admin` | Journal d'audit immuable (IP, user, action, données) |
| `wp_tma_notification` | Notifications multi-canal avec statut envoi |

> Ces tables sont créées automatiquement lors de l'activation du plugin. Aucune manipulation SQL manuelle n'est nécessaire.

---

## Rôles et permissions

| Capacité | Visiteur | Client | Agent TMA | Shop Manager | Admin |
|---|---|---|---|---|---|
| Voir les offres | ✅ | ✅ | ✅ | ✅ | ✅ |
| Réserver | ❌ | ✅ | ✅ | ✅ | ✅ |
| Utiliser les points | ❌ | ✅ | ✅ | ✅ | ✅ |
| Gérer les ressources | ❌ | ❌ | ✅ | ✅ | ✅ |
| Dashboard KPIs | ❌ | ❌ | ✅ | ✅ | ✅ |
| Export CSV | ❌ | ❌ | ❌ | ✅ | ✅ |
| Journal audit | ❌ | ❌ | ❌ | ❌ | ✅ |
| Paramètres plugin | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## API REST

Base URL : `http://localhost/boutique/wp-json/tma/v1/`

| Méthode | Endpoint | Description |
|---|---|---|
| `GET` | `/offres` | Liste des offres actives (titre, prix, description) |
| `GET` | `/disponibilites/{product_id}` | Dates bloquées pour un produit |
| `GET` | `/fidelite/{user_id}` | Points et niveau fidélité d'un client |

Exemple de réponse `/offres` :
```json
{
  "id": 42,
  "titre": "Circuit Imperial Cities",
  "prix": 4500,
  "devise": "MAD",
  "description": "7 jours Fès - Meknès - Rabat - Casablanca"
}
```

---

## Multilingue

Le site supporte **Français (FR)** et **Arabe (AR)** via Polylang :

- RTL automatique pour l'Arabe (attribut `dir="rtl"` + styles CSS dédiés)
- Switcher de langue dans le header
- URLs séparées : `/boutique/` (FR) · `/boutique/ar/` (AR)

> **Note :** Les produits créés avant l'activation de Polylang doivent être assignés manuellement à une langue via **Produits → Sélectionner tout → Action groupée → Définir la langue**.

---

## Documentation

| Fichier | Description |
|---|---|
| `GUIDE-UTILISATEUR.md` | Guide complet par rôle (Visiteur, Client, Agent, Admin) |

---

## Auteur

**JOUMAIL Abderrahmane**  
1ère année — Conception et Développement de Logiciel (CDL)  
École Supérieure de Technologie de Dakhla · Année universitaire 2025–2026  
[joumailabderrahmen@gmail.com](mailto:joumailabderrahmen@gmail.com)

---
