# Guide Utilisateur — Travel Maroc Agency
**Version 1.0 — Mai 2026**  
Stack : WordPress 6.x · WooCommerce 10.x · Plugin Travel Maroc Booking · Thème Travel Maroc · Polylang 3.8

---

## Table des matières

1. [Vue d'ensemble du site](#1-vue-densemble-du-site)
2. [Architecture des rôles](#2-architecture-des-rôles)
3. [Rôle : Visiteur (non connecté)](#3-rôle--visiteur-non-connecté)
4. [Rôle : Client](#4-rôle--client)
5. [Rôle : Agent TMA](#5-rôle--agent-tma)
6. [Rôle : Shop Manager](#6-rôle--shop-manager)
7. [Rôle : Administrateur](#7-rôle--administrateur)
8. [Programme de fidélité — Référence complète](#8-programme-de-fidélité--référence-complète)
9. [Système de notifications](#9-système-de-notifications)
10. [Base de données — Référence des tables](#10-base-de-données--référence-des-tables)
11. [API REST](#11-api-rest)
12. [Multilingue (Polylang)](#12-multilingue-polylang)

---

## 1. Vue d'ensemble du site

**Travel Maroc Agency** est une agence de voyage en ligne basée à Dakhla, Maroc. Le site permet de :

- Parcourir et réserver des offres de voyage (circuits, séjours, excursions)
- Gérer un programme de fidélité à points avec paliers et remises automatiques
- Administrer les ressources logistiques (guides, hôtels, transports)
- Communiquer avec les clients via notifications in-app et emails
- Consulter les statistiques de vente en temps réel

**URLs importantes**

| Page | URL |
|------|-----|
| Accueil | `http://localhost/boutique/` |
| Boutique / offres | `http://localhost/boutique/boutique-offres/` |
| Mon compte | `http://localhost/boutique/mon-compte/` |
| À propos | `http://localhost/boutique/a-propos/` |
| Contact | `http://localhost/boutique/contact/` |
| Administration WP | `http://localhost/boutique/wp-admin/` |
| Dashboard TMA | `http://localhost/boutique/wp-admin/admin.php?page=tma-dashboard` |
| Sitemap XML | `http://localhost/boutique/wp-sitemap.xml` |

---

## 2. Architecture des rôles

```
┌─────────────────────────────────────────────────────────────┐
│                      ADMINISTRATEUR                         │
│  Accès total : WP admin + TMA + Réglages + Journal d'audit  │
├─────────────────────────────────────────────────────────────┤
│                      SHOP MANAGER                           │
│  WP admin + TMA dashboard + CRUD complet + Export CSV       │
├─────────────────────────────────────────────────────────────┤
│                       AGENT TMA                             │
│  Dashboard TMA (lecture) + Clients + Commandes              │
├─────────────────────────────────────────────────────────────┤
│                         CLIENT                              │
│  Mon compte + Réservations + Fidélité + Notifications       │
├─────────────────────────────────────────────────────────────┤
│                        VISITEUR                             │
│  Navigation publique + Inscription + Formulaire contact     │
└─────────────────────────────────────────────────────────────┘
```

### Tableau des permissions

| Fonctionnalité | Visiteur | Client | Agent TMA | Shop Manager | Admin |
|----------------|----------|--------|-----------|--------------|-------|
| Voir les offres | ✅ | ✅ | ✅ | ✅ | ✅ |
| Réserver (checkout) | ❌ | ✅ | ✅ | ✅ | ✅ |
| Mon compte / fidélité | ❌ | ✅ | ✅ | ✅ | ✅ |
| Dashboard TMA | ❌ | ❌ | ✅ | ✅ | ✅ |
| Gérer les offres (produits) | ❌ | ❌ | ❌ | ✅ | ✅ |
| CRUD Guides / Hôtels / Transport | ❌ | ❌ | ❌ | ✅ | ✅ |
| CRUD Destinations | ❌ | ❌ | ❌ | ✅ | ✅ |
| Export CSV réservations | ❌ | ❌ | ❌ | ✅ | ✅ |
| Réglages TMA | ❌ | ❌ | ❌ | ❌ | ✅ |
| Journal d'audit | ❌ | ❌ | ❌ | ❌ | ✅ |
| Gestion des rôles / comptes | ❌ | ❌ | ❌ | ❌ | ✅ |

### Comptes système

| Compte | Rôle | Créé par |
|--------|------|----------|
| `admin` | Administrateur WordPress | Installation WP |
| `shop_manager_tma` | Shop Manager | Réglages TMA → bouton "Créer le compte" |
| Tout client inscrit | Customer (WC) | Auto-inscription ou commande |
| Agents créés manuellement | `tma_agent` | Admin WP → Utilisateurs |

---

## 3. Rôle : Visiteur (non connecté)

### 3.1 Navigation générale

Le visiteur accède à toutes les pages publiques via le menu principal :
- **Accueil** — Page héro avec vidéo/image de fond, offres mises en avant, carte interactive des destinations
- **Offres** — Boutique WooCommerce avec filtre par catégorie
- **À propos** — Histoire de l'agence, équipe, statistiques
- **Contact** — Formulaire de contact + coordonnées

Le **sélecteur de langue** (🇫🇷 FR / 🇲🇦 AR) est visible dans le header si Polylang est configuré avec 2 langues ou plus.

### 3.2 Page d'accueil

**Héro vidéo / image**
- Si une URL de vidéo `.mp4` est configurée dans les Réglages TMA, elle s'affiche en arrière-plan en lecture automatique (muette, en boucle)
- Sinon, une image statique (`tma_hero_bg_url`) est utilisée comme fond
- Boutons : "Voir nos offres" → boutique, "En savoir plus" → à propos

**Section offres**
- Grille de 3 produits mis en avant (les plus récents publiés)
- Chaque carte affiche : photo, nom, destination, durée, prix, points fidélité gagnables

**Carte interactive des destinations**
- Rendue via **Leaflet.js + OpenStreetMap**
- Marqueurs oranges sur chaque destination active ayant des coordonnées GPS
- Clic sur un marqueur → popup avec nom, pays, description
- Shortcode utilisé dans la page : `[tma_destinations_map]`

### 3.3 Boutique des offres

URL : `/boutique-offres/`

**Filtres par catégorie** (barre horizontale) :
- 🌍 Tout voir
- 🇲🇦 Voyages nationaux
- ✈️ Voyages internationaux
- 🏕️ Excursions

**Tri** : pertinence, prix croissant/décroissant, nouveautés (menu déroulant WooCommerce standard)

**Carte produit** affiche :
- Photo principale
- Nom de l'offre
- Destination (📍) et durée (🕐)
- Prix en MAD
- Points fidélité gagnables (+X points ⭐)
- Bouton "Réserver maintenant"

### 3.4 Fiche produit (offre de voyage)

**Informations affichées :**
- Galerie photos
- Nom, description complète
- Prix en MAD (sans décimales inutiles)
- Métadonnées TMA : destination, durée, points gagnables
- **Ressources logistiques liées** (si configurées dans la fiche admin) :
  - 🧭 Guide : prénom + nom
  - 🏨 Hôtel : nom + étoiles (★)
  - 🚌 Transport : trajet (départ → arrivée) + compagnie
- Onglet "Avis voyageurs" (commentaires WooCommerce)
- Bouton "Réserver maintenant" → redirige vers le checkout si connecté, sinon vers connexion

**Schema JSON-LD** : chaque fiche produit génère automatiquement un bloc `TouristTrip` pour le référencement Google.

### 3.5 Page À propos

- Texte de présentation (éditable dans Réglages TMA)
- Si une image est configurée (`tma_about_maroc_url`), elle s'affiche ; sinon 4 statistiques clés (500+ clients, 10+ destinations, 5★, 3 guides)
- Section équipe : si `tma_about_team_url` est configuré, photo d'équipe ; sinon 3 cartes membres par défaut
- Bouton CTA → boutique

### 3.6 Page Contact

**Formulaire** (traitement AJAX) :
- Nom complet (requis)
- Email (requis)
- Téléphone
- Destination souhaitée (liste déroulante)
- Message (requis)

Envoi via `wp_mail()` vers l'email administrateur. Réponse JSON : succès ou erreur.

**Informations de contact** (lues depuis les options TMA) :
- Adresse (modifiable dans Réglages)
- Téléphone
- Lien WhatsApp (ouvre `wa.me/{numéro}`)
- Email cliquable
- Horaires : Lun–Sam 9h–18h, Dimanche fermé

### 3.7 Inscription

Via WooCommerce standard (`/mon-compte/`) :
- Formulaire d'inscription → crée un compte `customer`
- Un **profil client TMA** est automatiquement créé en base (`tma_client_profile`) avec :
  - Type : Standard (palier initial)
  - Solde points : 0
  - Date d'inscription

---

## 4. Rôle : Client

### 4.1 Connexion et Mon compte

URL : `/mon-compte/`

Menu Mon compte personnalisé :
- Tableau de bord
- Mes réservations
- Mes adresses
- Mon profil
- Déconnexion

### 4.2 Tableau de bord client

**Bloc fidélité** :
- Solde de points actuel
- Type de client actuel avec badge coloré (Standard / Silver / Gold / VIP / Entreprise)
- Barre de progression vers le palier suivant
- Historique des 5 dernières transactions de points

**Bloc notifications** (5 dernières) :
- Badge rouge indiquant le nombre de notifications non lues
- Icône selon le type : ✅ CONFIRMATION, ⭐ POINTS_GAGNES, ⬆️ MONTEE_PALIER, ❌ ANNULATION, ℹ️ SYSTEME
- Fond bleu clair pour les non lues
- Date de création

### 4.3 Processus de réservation (Checkout)

**Étape 1 — Panier**
- Si le client a un **taux de remise** (paliers Silver, Gold, VIP), une ligne de remise est automatiquement appliquée :
  - Calcul : `total_panier × taux_remise_type_client`
  - Libellé : "Remise fidélité"
  - S'affiche en valeur négative dans le récapitulatif

**Étape 2 — Checkout**

En plus des champs WooCommerce standard (facturation, livraison), un bloc **"🧳 Détails du voyage"** apparaît :

| Champ | Type | Requis |
|-------|------|--------|
| Date de départ souhaitée | Sélecteur de date (min : demain) | ✅ Oui |
| Adultes (12 ans et +) | Nombre (min 1, max 20) | ✅ Oui |
| Enfants (moins de 12 ans) | Nombre (min 0, max 10) | Non |
| Demandes spéciales | Zone de texte libre | Non |

**Validation côté serveur :**
- Date de départ obligatoire et ≥ demain
- Date non bloquée par l'administrateur pour ce produit
- Au moins 1 adulte

**Étape 3 — Confirmation**

Email de confirmation automatique envoyé avec :
- Numéro de commande TMA (format `TMA-YYYY-XXXX`)
- Détails voyage : date de départ, nombre de voyageurs
- Points fidélité gagnés (si > 0)
- Étapes de traitement : reçu → en cours → confirmé → bon voyage
- Bouton WhatsApp pour contacter le support

### 4.4 Mes réservations

URL : `/mon-compte/commandes/`

Tableau enrichi avec colonnes :

| Colonne | Contenu |
|---------|---------|
| Réservation | N° de commande + nom de l'offre |
| Date commande | Date de passage de commande |
| Départ prévu | Date de départ choisie au checkout (en bleu) |
| Voyageurs | 👤 adultes 🧒 enfants |
| Statut | Badge coloré : En attente / En traitement / Confirmée / Annulée |
| Total | Montant total formaté en MAD |
| Actions | Boutons : Voir, Payer, Annuler (selon statut) |

### 4.5 Détail d'une commande

En plus du récapitulatif WooCommerce standard, une section **"🧳 Détails du voyage"** s'affiche en bas avec :
- Date de départ
- Nombre de voyageurs (adultes / enfants)

### 4.6 Programme de fidélité

**Gain de points :**
- **1 point pour 10 MAD dépensés** (taux : 0,1 pts/MAD)
- Les points sont attribués quand la commande passe au statut **"Terminée"**
- Expiration automatique : **24 mois** après la date de gain
- Exception : clients VIP → points sans expiration

**Paliers et avantages :**

| Palier | Seuil points | Remise auto | Couleur badge |
|--------|-------------|-------------|---------------|
| Standard | 0 pts | 0% | Gris `#6c757d` |
| Silver | 500 pts | 5% | Argent `#adb5bd` |
| Gold | 1 500 pts | 10% | Or `#ffc107` |
| VIP | 5 000 pts | 20% | Violet `#6f42c1` |
| Entreprise | 0 pts | 0% | Bleu `#0d6efd` |

**Montée de palier :**
- Vérifiée automatiquement après chaque attribution de points
- Notification in-app et email envoyée au client lors de la montée

**Annulation de commande :**
- Si une commande est annulée, les points gagnés sur cette commande sont automatiquement annulés (écriture `ANNULATION` dans l'historique)

---

## 5. Rôle : Agent TMA

### 5.1 Accès

**Capacités du rôle `tma_agent` :**
- `tma_view_dashboard` — Voir le dashboard TMA
- `tma_view_clients` — Voir la liste des clients
- `tma_view_orders` — Voir les commandes
- `tma_manage_bookings` — Gérer les réservations

**L'agent TMA n'a pas accès à :**
- WooCommerce complet (pas de `manage_woocommerce`)
- Réglages WordPress
- Réglages TMA
- Journal d'audit
- CRUD guides/hôtels/transport/destinations

### 5.2 Création d'un compte agent

1. WP Admin → **Utilisateurs → Ajouter**
2. Saisir login, email, mot de passe
3. Rôle → sélectionner **"Agent TMA"**
4. Enregistrer

### 5.3 Fonctionnalités disponibles

- **Dashboard TMA** : KPIs (revenus, réservations, clients, points distribués)
- **Liste clients** : profils, types, soldes de points
- **Historique fidélité** : transactions de points par client
- **Notifications** : liste des notifications envoyées/en attente

---

## 6. Rôle : Shop Manager

### 6.1 Accès

Le **Shop Manager** (`shop_manager` ou `shop_manager_tma`) hérite de toutes les capacités `tma_*` en plus des capacités WooCommerce standard (`manage_woocommerce`).

Création du compte dédié :
> **Réglages TMA → Comptes & Rôles → "Créer le compte Shop Manager"**  
> Le mot de passe généré s'affiche **une seule fois** — le noter immédiatement.

### 6.2 Dashboard TMA

URL : `wp-admin/admin.php?page=tma-dashboard`

**KPIs affichés en temps réel :**

| KPI | Source |
|-----|--------|
| Revenus totaux | Somme des commandes `wc-completed` |
| Revenus ce mois | Idem filtré sur le mois courant |
| Réservations | Toutes commandes hors annulées/supprimées |
| Clients inscrits | Nombre de profils `tma_client_profile` |
| Points distribués | Somme des GAIN dans `tma_historique_points` |
| Notifs en attente | Notifications avec statut `EN_ATTENTE` |

**Tableaux :**
- Clients par type (Standard / Silver / Gold / VIP / Entreprise)
- Top 5 offres les plus réservées
- 10 dernières actions du journal d'audit

**Bouton Export CSV :**
> "⬇ Exporter CSV réservations" — génère un fichier `reservations-tma-YYYY-MM-DD.csv`

**Colonnes du CSV :**
N° Commande, Date commande, Statut, Client, Email, Téléphone, Offre, Prix HT, Total TTC, Date départ, Adultes, Enfants, Demandes spéciales

Le fichier est encodé **UTF-8 avec BOM** (compatible Excel sans problème d'accents).

### 6.3 Gestion des offres (Produits WooCommerce)

URL : `wp-admin/edit.php?post_type=product`

**Création d'une offre :**

1. Produits → **Ajouter**
2. Saisir : titre, description, prix, photos
3. Dans la meta box **"Informations TMA"** (panneau latéral droit) :

| Champ | Description |
|-------|-------------|
| Destination | Texte libre (ex : "Marrakech, Maroc") |
| Durée | Texte libre (ex : "7 jours / 6 nuits") |
| Points fidélité gagnés | Nombre entier |
| Guide assigné | Sélecteur → liste des guides ACTIF en base |
| Hôtel assigné | Sélecteur → liste des hôtels ACTIF en base |
| Transport assigné | Sélecteur → liste des transports ACTIF en base |

4. Dans la meta box **"📅 Disponibilités & Capacité"** :
   - **Capacité max par départ** : nombre maximum de voyageurs acceptés par date (0 = illimité)
   - **Calendrier de disponibilité** : cliquer sur les dates à bloquer (fond rouge = bloqué)
   - Les dates bloquées empêchent la réservation au checkout (validation serveur)

5. Catégorie produit : Voyages nationaux / Voyages internationaux / Excursions
6. Publier

**Note Polylang :** Pour créer une traduction arabe d'une offre, cliquer sur le drapeau 🇲🇦 dans la colonne "Langues" de la liste des produits. Les meta TMA numériques (`_tma_points`, `_tma_guide_id`, `_tma_hotel_id`, `_tma_transport_id`) sont **synchronisées automatiquement** entre les traductions.

### 6.4 Gestion des destinations

URL : `wp-admin/admin.php?page=tma-destinations`

**Champs d'une destination :**

| Champ | Requis | Description |
|-------|--------|-------------|
| Nom | ✅ | Nom de la destination |
| Localisation | ✅ | Ville/région de rattachement (liste des localisations en base) |
| Pays | ✅ | Pays (défaut : Maroc) |
| Région | Non | Région ou zone géographique |
| Description | Non | Texte de présentation (affiché dans les popups de la carte) |
| Image URL | Non | URL de l'image principale |
| Destination populaire | Non | Coché = mise en avant |
| Statut | ✅ | ACTIF / INACTIF |

**Note :** Les destinations avec une localisation ayant des coordonnées GPS (latitude/longitude) apparaissent sur la carte Leaflet en page d'accueil.

### 6.5 Gestion des guides touristiques

URL : `wp-admin/admin.php?page=tma-guides`

**Champs d'un guide :**

| Champ | Requis | Description |
|-------|--------|-------------|
| Localisation | ✅ | Ville de base du guide |
| Prénom / Nom | ✅ | Identité |
| Email | Non | Contact email |
| Téléphone | ✅ | Contact principal |
| Langues | Non | Ex : "Arabe, Français, Anglais" |
| Spécialités | Non | Ex : "Sahara, Villes impériales" |
| Tarif journalier (MAD) | ✅ | Tarif de prestation |
| Expérience (années) | Non | Nombre d'années d'expérience |
| Photo URL | Non | URL de la photo du guide |
| Statut | ✅ | ACTIF (visible dans les sélecteurs produit) / INACTIF |

### 6.6 Gestion des hôtels

URL : `wp-admin/admin.php?page=tma-hotels`

**Champs d'un hôtel :**

| Champ | Requis | Description |
|-------|--------|-------------|
| Localisation | ✅ | Ville de l'hôtel |
| Nom | ✅ | Nom complet |
| Classement | Non | 1 à 5 étoiles |
| Adresse | Non | Adresse complète |
| Email / Téléphone / Site web | Non | Coordonnées |
| Description | Non | Texte de présentation |
| Prix/nuit (MAD) | ✅ | Tarif chambre standard |
| Nb. chambres | Non | Capacité totale |
| Image URL | Non | Photo principale |
| Statut | ✅ | ACTIF / INACTIF |

### 6.7 Gestion des transports

URL : `wp-admin/admin.php?page=tma-transport`

**Champs d'une liaison transport :**

| Champ | Requis | Description |
|-------|--------|-------------|
| Départ | ✅ | Localisation de départ |
| Arrivée | ✅ | Localisation d'arrivée |
| Type | ✅ | AVION / BUS / TRAIN / VOITURE_PRIVEE / BATEAU |
| Compagnie | Non | Ex : "Royal Air Maroc", "ONCF", "CTM" |
| N° liaison | Non | Ex : "AT 600", "TR 5" |
| Durée (minutes) | Non | Durée du trajet |
| Prix/pers. (MAD) | ✅ | Tarif par personne |
| Capacité (places) | Non | Nombre de places |
| Statut | ✅ | ACTIF / INACTIF |

### 6.8 Gestion des commandes

URL : `wp-admin/edit.php?post_type=shop_order`

Dans chaque fiche commande, un bloc **"🧳 Détails du voyage"** affiche :
- Date de départ choisie par le client
- Nombre d'adultes et d'enfants
- Demandes spéciales éventuelles

Ces informations sont également incluses dans les emails WooCommerce (champs de métadonnées de commande).

**Statuts de commande :**

| Statut WC | Signification TMA |
|-----------|-------------------|
| `pending` | Paiement en attente |
| `processing` | Réservation reçue, en cours de traitement |
| `on-hold` | En attente de confirmation manuelle |
| `completed` | Voyage confirmé → points attribués au client |
| `cancelled` | Annulée → points annulés |
| `refunded` | Remboursée |

### 6.9 Fidélité — Vue administrateur

URL : `wp-admin/admin.php?page=tma-fidelite`

- Liste complète de l'historique des points (tous clients)
- Filtrage par client, type d'opération (GAIN / ANNULATION / EXPIRATION)
- Soldes actuels par client

### 6.10 Notifications

URL : `wp-admin/admin.php?page=tma-notifications`

**Types de notifications générées automatiquement :**

| Type | Déclencheur |
|------|-------------|
| `CONFIRMATION_RESERVATION` | Nouvelle commande créée |
| `POINTS_GAGNES` | Commande passée en "Terminée" |
| `MONTEE_PALIER` | Client monte de palier |
| `ANNULATION` | Commande annulée |

**Statuts de notification :**
- `EN_ATTENTE` — En queue, pas encore envoyée
- `ENVOYE` — Email envoyé avec succès
- `ERREUR` — Échec d'envoi (détail dans `erreur_details`)
- `LU` — Notification lue par le client

Les emails sont envoyés en **traitement par lot toutes les heures** via WP-Cron (`tmb_send_notifications_cron`).

---

## 7. Rôle : Administrateur

L'administrateur a accès à tout ce que le Shop Manager peut faire, plus les fonctionnalités exclusives suivantes.

### 7.1 Réglages TMA

URL : `wp-admin/admin.php?page=tma-settings`

**Section Héro & Médias :**

| Option | Clé option | Description |
|--------|-----------|-------------|
| Vidéo hero | `tma_video_hero_url` | URL d'un fichier `.mp4` pour le fond animé de la page d'accueil |
| Image hero | `tma_hero_bg_url` | Image de fallback si pas de vidéo (aussi utilisée pour les Open Graph) |
| Image À-propos | `tma_about_image_url` | Photo affichée dans la section "Notre histoire" |

**Section Analytics Google :**

| Option | Clé option | Description |
|--------|-----------|-------------|
| ID Measurement GA4 | `tma_analytics_id` | Format `G-XXXXXXXXXX` — laisser vide pour désactiver |

Le snippet GA4 est injecté automatiquement dans le `<head>` de chaque page.

**Section Coordonnées :**

| Option | Clé option | Description |
|--------|-----------|-------------|
| Téléphone | `tma_contact_phone` | Affiché sur la page Contact |
| WhatsApp | `tma_whatsapp_number` | Format international sans + (ex: `212661234567`) — utilisé dans les emails et la page Contact |
| Email contact | `tma_contact_email` | Affiché et cliquable sur la page Contact |
| Adresse | `tma_contact_address` | Texte multiligne, affiché sur la page Contact |

**Section Réseaux sociaux :**

| Option | Clé option |
|--------|-----------|
| Facebook | `tma_facebook_url` |
| Instagram | `tma_instagram_url` |

**Section À-propos :**

| Option | Clé option | Description |
|--------|-----------|-------------|
| Texte | `tma_about_text` | Texte HTML affiché dans "Notre histoire" (textarea WYSIWYG-ready) |

**Section Comptes & Rôles :**
- Affiche l'état du compte `shop_manager_tma` (ID, lien modifier)
- Bouton de création si le compte n'existe pas encore
- État du rôle `tma_agent` (existe / absent)

**Section Données démo :**
- Bouton **"Insérer les données démo"** : insère des guides, hôtels et transports de démonstration dans les tables si elles sont vides

### 7.2 Journal d'audit

URL : `wp-admin/admin.php?page=tma-logs`

Toutes les actions effectuées dans le backoffice TMA sont tracées :

| Colonne | Description |
|---------|-------------|
| Date | Horodatage de l'action |
| Admin | Nom de l'utilisateur WP ayant effectué l'action |
| Action | CREATE / UPDATE / DELETE / LOGIN / EXPORT |
| Entité | Table cible (tma_destination, tma_guide, tma_hotel, etc.) |
| ID | ID de l'enregistrement concerné |
| IP | Adresse IP réelle du navigateur (uniquement `REMOTE_ADDR`, pas de proxy spoofable) |

**Note sécurité :** L'IP est enregistrée uniquement depuis `$_SERVER['REMOTE_ADDR']`. Les headers `X-Forwarded-For` et `X-Real-IP` sont ignorés pour éviter le spoofing.

### 7.3 SEO — Open Graph et méta

Les balises Open Graph et Twitter Cards sont générées automatiquement dans le `<head>` de chaque page :

| Page | og:title | og:image |
|------|----------|----------|
| Page d'accueil | Nom du site | Image hero |
| Fiche produit | Nom de l'offre — destination | Photo principale du produit |
| Catégorie | Nom catégorie — Nom du site | Image hero |
| Autres pages | Titre de la page — Nom du site | Image hero |

**Sitemap XML natif :** `/wp-sitemap.xml` inclut automatiquement les produits (offres) et les catégories de produits.

### 7.4 Données structurées JSON-LD

Chaque fiche produit injecte automatiquement un schéma `TouristTrip` :
```json
{
  "@context": "https://schema.org",
  "@type": "TouristTrip",
  "name": "Nom de l'offre",
  "description": "...",
  "offers": {
    "@type": "Offer",
    "price": 2500,
    "priceCurrency": "MAD",
    "availability": "https://schema.org/InStock"
  }
}
```

### 7.5 Gestion du multilingue (Polylang)

URL : `wp-admin/admin.php?page=mlang`

**Langues configurées :** Français (FR — défaut) + العربية (AR)

**Ce qui est traduit automatiquement :**

| Élément | Mécanisme |
|---------|-----------|
| Produits (offres) | Traduction manuelle via l'éditeur Polylang |
| Catégories de produits | Traduction manuelle |
| Strings du thème (boutons, menus) | Langues → Traductions de chaînes → groupe "Travel Maroc Thème" |
| Options (texte à propos, adresse) | Langues → Traductions de chaînes → groupe "Travel Maroc Options" |

**Meta TMA synchronisées entre traductions (même valeur) :**
- `_tma_points` — Points fidélité
- `_tma_guide_id` — Guide assigné
- `_tma_hotel_id` — Hôtel assigné
- `_tma_transport_id` — Transport assigné

**Support RTL arabe :** Quand la langue courante est l'arabe, la classe `tma-rtl` est ajoutée au `<body>`. Cela active automatiquement :
- `direction: rtl`
- Police arabe (`Noto Naskh Arabic, serif`)
- Inversion des flex-directions (navigation, grilles)

### 7.6 Crons automatiques

| Cron | Fréquence | Action |
|------|-----------|--------|
| `tma_cron_expirer_points` | Quotidien | Expire les points de fidélité dont la `date_expiration` est passée (crée une écriture `EXPIRATION` en base) |
| `tmb_send_notifications_cron` | Toutes les heures | Envoie les emails en attente dans la file `tma_notification` |

Les deux crons sont planifiés à l'activation du plugin et re-vérifiés à chaque chargement de page.

### 7.7 Désactivation et désinstallation

**Désactivation :**
- Supprime le rôle `tma_agent`
- Retire les capacités `tma_*` du rôle `shop_manager`
- Annule les deux crons
- **Ne supprime pas les données**

**Désinstallation (Supprimer dans WP) :**
- Supprime les 10 tables TMA
- Supprime toutes les options `tma_*`
- Annule les crons
- **Irréversible**

---

## 8. Programme de fidélité — Référence complète

### 8.1 Règles de calcul

```
Points gagnés = floor(total_commande × 0,1)
Exemple : commande de 2 500 MAD → 250 points

Remise palier = total_panier × taux_remise
Exemple : client Gold (10%), panier 1 500 MAD → remise de 150 MAD
```

### 8.2 Expiration

- Durée : **24 mois** à compter de la date d'opération GAIN
- Exception : clients **VIP** ont `points_jamais_expirent = 1` → pas d'expiration
- Le cron quotidien cherche les GAIN dont `date_expiration < CURDATE()` et sans EXPIRATION correspondante → crée une ligne EXPIRATION et met à jour le solde

### 8.3 Types d'opérations en historique

| `operation` | Déclencheur | Effet sur solde |
|------------|-------------|-----------------|
| `GAIN` | Commande terminée | + points |
| `ANNULATION` | Commande annulée | - points (inverse le GAIN) |
| `EXPIRATION` | Cron quotidien | - points expirés |
| `DEDUCTION` | Remise panier appliquée | - points (si déduction manuelle) |
| `AJOUT_ADMIN` | Ajout manuel par admin | + points |

### 8.4 Montée de palier

Vérifiée après chaque attribution de points. Si `points_cumules_total` dépasse le `seuil_points` d'un palier supérieur :
1. Mise à jour de `type_client_id` dans `tma_client_profile`
2. Création d'une notification `MONTEE_PALIER`
3. Email envoyé au client

---

## 9. Système de notifications

### 9.1 Flux de notification

```
Événement WC (nouvelle commande, commande terminée, annulation)
        ↓
TMB_Notifications::creer() → INSERT dans tma_notification (statut: EN_ATTENTE)
        ↓
Cron hourly → TMB_Notifications::process_queue()
        ↓
wp_mail() → Email envoyé → statut: ENVOYE ou ERREUR
        ↓
Client → Mon compte → Dashboard → Notifications affichées
```

### 9.2 Canaux

Actuellement implémenté : **EMAIL** (via `wp_mail()`)  
Prévu : IN_APP (affiché dans le dashboard client, lu via flag `date_lecture`)

### 9.3 Affichage client

5 notifications les plus récentes visibles dans le tableau de bord client :
- Badge de comptage des non lues (fond rouge)
- Fond bleu clair sur les non lues
- Icône selon le type

---

## 10. Base de données — Référence des tables

Toutes les tables utilisent le préfixe WordPress (par défaut `wp_`).

### `tma_localisation`
Points géographiques de référence (villes, aéroports, régions).

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | Clé primaire |
| `nom` | VARCHAR(100) | Nom du lieu |
| `type` | VARCHAR(20) | VILLE / AEROPORT / REGION / PORT |
| `code_pays` | VARCHAR(3) | Code ISO (MA, FR, TR…) |
| `latitude` | DECIMAL(9,6) | Coordonnée GPS |
| `longitude` | DECIMAL(9,6) | Coordonnée GPS |
| `actif` | TINYINT | 1 = actif |

**Données pré-chargées :** 16 localisations marocaines (Casablanca, Marrakech, Fès, Rabat, Agadir, Meknès, Tanger, Ouarzazate, Merzouga, Chefchaouen, Essaouira, Dakhla, Aéroport Mohammed V, Aéroport Marrakech-Menara, Sahara marocain, Haut Atlas)

### `tma_destination`
Destinations touristiques présentées sur le site.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | Clé primaire |
| `localisation_id` | INT | FK → tma_localisation |
| `nom` | VARCHAR(100) | Nom de la destination |
| `pays` | VARCHAR(100) | Pays (défaut : Maroc) |
| `region` | VARCHAR(100) | Région |
| `description` | TEXT | Texte affiché sur la carte |
| `image_url` | VARCHAR(255) | Photo principale |
| `est_populaire` | TINYINT | 1 = mise en avant |
| `statut` | VARCHAR(10) | ACTIF / INACTIF |

### `tma_guide`
Guides touristiques disponibles à l'assignation sur les offres.

### `tma_hotel`
Hôtels disponibles à l'assignation sur les offres.

### `tma_transport`
Liaisons transport (départ → arrivée) disponibles à l'assignation.

### `tma_type_client`
Paliers du programme de fidélité.

| Colonne | Description |
|---------|-------------|
| `libelle` | Standard / Silver / Gold / VIP / Entreprise |
| `taux_remise` | Pourcentage de remise automatique (0–100) |
| `seuil_points` | Points nécessaires pour atteindre ce palier |
| `points_jamais_expirent` | 1 = points permanents (VIP) |
| `couleur_badge` | Code hex pour l'affichage |

### `tma_client_profile`
Profil fidélité de chaque client inscrit.

| Colonne | Description |
|---------|-------------|
| `wp_user_id` | Lien vers `wp_users.ID` |
| `type_client_id` | Palier actuel |
| `points_fidelite_solde` | Solde actuel de points |
| `points_cumules_total` | Total des points jamais gagnés (pour les paliers) |
| `statut` | ACTIF / SUSPENDU |

### `tma_historique_points`
Toutes les transactions de points (GAIN, ANNULATION, EXPIRATION…).

### `tma_notification`
File d'attente et historique de toutes les notifications.

### `tma_log_admin`
Journal d'audit de toutes les actions effectuées en backoffice.

---

## 11. API REST

Base URL : `/wp-json/tma/v1/`

### `GET /destinations`

Retourne la liste des destinations actives.

**Réponse :**
```json
[
  {
    "id": 1,
    "nom": "Désert de Merzouga",
    "pays": "Maroc",
    "region": "Drâa-Tafilalet",
    "description": "Dunes de sable doré...",
    "image_url": "https://...",
    "est_populaire": true,
    "localisation": {
      "nom": "Merzouga",
      "latitude": 31.08,
      "longitude": -4.014
    }
  }
]
```

**Filtres :** `?pays=Maroc` · `?populaire=1`

### `GET /offres`

Retourne les offres publiées avec leurs métadonnées TMA.

**Réponse :**
```json
[
  {
    "id": 42,
    "titre": "Circuit Désert 7 jours",
    "prix": 2500,
    "points": 250,
    "destination": "Merzouga",
    "duree": "7 jours / 6 nuits",
    "image": "https://...",
    "url": "https://..."
  }
]
```

### `GET /client/{wp_user_id}/fidelite`

Retourne le profil fidélité d'un client. Nécessite authentification WP.

**Réponse :**
```json
{
  "type_client": "Gold",
  "solde_points": 1850,
  "taux_remise": 10,
  "prochaine_montee": {
    "palier": "VIP",
    "points_manquants": 3150
  }
}
```

---

## 12. Multilingue (Polylang)

### 12.1 Configuration initiale

1. **WP Admin → Langues → Ajouter une langue**
   - Ajouter **Français (fr)** comme langue par défaut
   - Ajouter **العربية (ar)** avec "Langue de droite à gauche" coché

2. **Assigner une langue aux contenus existants**
   - Produits → sélectionner tout → Action groupée "Set language: Français"
   - Idem pour les pages et catégories

### 12.2 Traduire les contenus

**Produits :**
- Liste produits → colonne Langues → cliquer sur le + de la colonne Arabe
- Traduire titre, description, courte description

**Strings du thème :**
- Langues → Traductions de chaînes → groupe **"Travel Maroc Thème"**
- Traduire : "Réserver maintenant", "Connexion", "Mon compte", etc.

**Options (About, Contact) :**
- Langues → Traductions de chaînes → groupe **"Travel Maroc Options"**
- Traduire le texte À-propos et l'adresse de contact

### 12.3 Sélecteur de langue

Le sélecteur s'affiche automatiquement dans le header **dès que 2 langues ou plus sont configurées**. Il utilise les drapeaux et codes de langue Polylang (FR / AR). Langue active en gras avec classe `tma-lang--active`.

### 12.4 RTL Arabe

Quand `get_bloginfo('text_direction') === 'rtl'` (langue arabe active) :
- Classe `tma-rtl` ajoutée au `<body>`
- Direction : droite à gauche
- Navigation : ordre inversé
- Grilles : colonnes inversées
- Police : Noto Naskh Arabic (importée via CSS)

---

*Guide généré le 09 mai 2026 — Travel Maroc Agency — EST Dakhla CDL 2025/2026*
