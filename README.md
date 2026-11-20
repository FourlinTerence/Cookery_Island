# Cookery Island 🍳

**Cookery Island** est une application web communautaire de partage de recettes de cuisine développée sous **Symfony**.

---

## 👥 Matrice des Rôles & Permissions

Le tableau ci-dessous synthétise les fonctionnalités disponibles selon le profil de l'utilisateur :

| Fonctionnalités | Visiteur non inscrit | Utilisateur inscrit (`ROLE_USER`) | Administrateur (`ROLE_ADMIN`) |
| :--- | :---: | :---: | :---: |
| **Consulter les recettes & ingrédients** | ✅ | ✅ | ✅ |
| **S'inscrire / Se connecter** | ✅ | ❌ *(déjà connecté)* | ❌ *(déjà connecté)* |
| **Se déconnecter** | ❌ | ✅ | ✅ |
| **Liker / Retirer un like** | ❌ | ✅ | ✅ |
| **Commenter une recette** | ❌ | ✅ | ✅ |
| **Modifier son propre commentaire** | ❌ | ✅ *(ses commentaires)* | ✅ *(ses commentaires)* |
| **Supprimer son propre commentaire** | ❌ | ✅ *(ses commentaires)* | ✅ *(ses commentaires)* |
| **Supprimer le commentaire d'un tiers** | ❌ | ❌ | ✅ *(modération)* |
| **Publier une recette avec ingrédients** | ❌ | ✅ | ✅ |
| **Créer un ingrédient / une unité** | ❌ | ✅ *(à la volée dans le formulaire)* | ✅ |
| **Modifier sa propre recette** | ❌ | ✅ *(ses recettes)* | ✅ *(ses recettes)* |
| **Supprimer sa propre recette** | ❌ | ✅ *(ses recettes)* | ✅ *(ses recettes)* |
| **Supprimer la recette d'un tiers** | ❌ | ❌ | ✅ *(modération)* |
| **Accéder au panneau de contrôle (Dashboard)** | ❌ | ❌ | ✅ |
| **Exclure / Bannir un abonné** | ❌ | ❌ | ✅ *(depuis le dashboard)* |

---

## 📁 Documentation & Conception (`docs/`)

La documentation de conception du projet est organisée dans le dossier standard `docs/` :

```text
docs/
├── diagrams/       # Diagrammes (UML, MCD Merise, cas d'utilisation, flux SVG/PNG)
├── branding/       # Charte graphique (logo, palette de couleurs, typographies)
├── wireframes/     # Wireframes et zoning fonctionnel (basse fidélité)
└── mockups/        # Maquettes haute fidélité (exports Figma, UI finales)
```

---

## 🛠️ Stack Technique

* **Framework :** Symfony 8.1
* **Langage :** PHP 8.4
* **ORM :** Doctrine ORM
* **Moteur de template :** Twig
* **Frontend :** Symfony UX / Stimulus / Turbo (AssetMapper)
