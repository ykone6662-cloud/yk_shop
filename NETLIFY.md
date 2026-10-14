# Déployer YK Shop sur Netlify

Cette version remplace les pages PHP exécutables par une interface statique dans `public/` et des fonctions Node.js dans `netlify/functions/`. Les anciennes pages PHP restent dans le dépôt comme référence, mais ne sont pas publiées par Netlify.

## Prérequis

- Un serveur MySQL joignable depuis les fonctions Netlify. Le serveur MySQL de XAMPP sur `127.0.0.1` n'est pas accessible depuis Netlify. Certains hébergeurs mutualisés bloquent aussi les connexions distantes ou les adresses sortantes variables des fonctions serverless; si votre hébergeur ne les autorise pas, utilisez un hébergeur MySQL externe compatible.
- Une base importée à partir du schéma et des données de YK Shop. Cette version s'appuie sur les tables et colonnes `users`, `admins`, `products`, `cart`, `orders`, `order_items` et `customer_reviews` déjà utilisées par le site PHP.
- Les colonnes de paiement `orders.payment_reference` et `orders.payment_method` doivent exister. Le panier doit avoir une contrainte unique sur `(user_id, product_id)` pour garantir qu'un client ne crée qu'une ligne par produit. Vérifiez d'abord les doublons avec:

  ```sql
  SELECT user_id, product_id, COUNT(*) AS rows_per_product
  FROM cart
  GROUP BY user_id, product_id
  HAVING COUNT(*) > 1;
  ```

  Après avoir résolu manuellement tout doublon, ajoutez la contrainte si elle n'existe pas déjà:

  ```sql
  ALTER TABLE cart
  ADD UNIQUE KEY cart_user_product_unique (user_id, product_id);
  ```

- Après avoir importé la base, exécutez `netlify/migrations.sql` une seule fois pour créer la table d'avis et les colonnes de livraison sur les commandes. Si la table des avis existe déjà, retirez son instruction `CREATE TABLE` ou sautez-la; n'exécutez pas de nouveau les `ALTER TABLE` si les colonnes de livraison ont déjà été ajoutées.

## Configuration Netlify

Connectez le dépôt à Netlify. Le fichier `netlify.toml` publie `public/` et détecte automatiquement les fonctions. Aucun build frontend n'est requis. Netlify installe les dépendances depuis `package.json`.

Ajoutez ces variables dans **Site configuration → Environment variables**, pour les contextes Production, Deploy Previews et Branch deploys pertinents:

| Variable | Obligatoire | Description |
| --- | --- | --- |
| `DB_HOST` | oui | Hôte MySQL public ou privé accessible depuis Netlify |
| `DB_PORT` | non | Port MySQL, par défaut `3306` |
| `DB_USER` | oui | Compte MySQL à privilèges minimaux |
| `DB_PASSWORD` | recommandé | Mot de passe MySQL |
| `DB_NAME` | oui | Nom de la base importée |
| `DB_SSL` | recommandé | Mettre `true` si l'hôte fournit TLS |
| `NETLIFY_SESSION_SECRET` | oui | Secret aléatoire d'au moins 32 caractères, différent par environnement |
| `GENIUSPAY_API_KEY` | paiement | Clé API GeniusPay |
| `GENIUSPAY_API_SECRET` | paiement | Secret API GeniusPay |
| `GENIUSPAY_WEBHOOK_SECRET` | webhook | Secret HMAC communiqué par GeniusPay |
| `GENIUSPAY_BASE_URL` | non | URL API GeniusPay; valeur par défaut incluse dans le code |
| `ALLOWED_ORIGIN` | optionnel | Origine HTTPS exacte si les URLs Netlify automatiques ne conviennent pas |

Ne mettez aucun secret dans les fichiers versionnés. Créez `NETLIFY_SESSION_SECRET` avec un générateur cryptographique; par exemple `node -e "console.log(require('node:crypto').randomBytes(48).toString('base64url'))"` puis collez le résultat dans les paramètres Netlify.

Le premier compte administrateur doit exister dans la table `admins` avec un hash compatible `password_verify` PHP / bcrypt. N'exposez pas l'outil de création d'administrateur sur le web.

Pour GeniusPay, configurez l'URL du webhook comme `https://VOTRE-DOMAINE/webhooks/geniuspay`. Le webhook vérifie la signature HMAC et le montant avant de marquer la commande payée.

## Tester en local

1. Installez Node.js 20 ou plus récent et Netlify CLI.
2. Copiez `.env.example` vers `.env` et complétez les valeurs d'une base distante accessible.
3. Exécutez `npm install`, puis `netlify dev`.
4. Testez la boutique, l'inscription, la connexion, les commandes, l'espace administrateur et le webhook avant d'activer le domaine public.

La page Contact et les pages légales sont des contenus statiques à compléter avec les coordonnées et règles réelles. L'envoi d'e-mails et le téléversement d'images vers un stockage persistant doivent être fournis par un service externe; l'administration accepte pour l'instant une URL HTTPS d'image ou un fichier statique sous `/images/`.
