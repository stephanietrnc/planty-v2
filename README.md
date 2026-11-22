# Planty

Site d'une boisson énergisante 100 % naturelle : accueil, page équipe et contact, page de précommande.
Thème WordPress sur mesure et extension de contenus, d'après la maquette Figma du projet.

**Démo : [stephanietrnc.github.io/planty-v2](https://stephanietrnc.github.io/planty-v2/)**
Version statique du site : la navigation et le choix des quantités fonctionnent, les formulaires sont désactivés.

## Ce que fait le site

- **Accueil** : canette et branches animées, présentation, les goûts (cliquables vers la commande), les avis.
- **Nous rencontrer** : l'équipe et un formulaire de contact.
- **Commander** : choix des quantités par goût (boutons + / −), récapitulatif en direct, coordonnées et livraison.
- Les deux formulaires envoient un e-mail à l'adresse réglée dans *Apparence › Personnaliser › Formulaires*.

## Ce que le client gère dans l'admin

| Où | Quoi |
| --- | --- |
| **Goûts** | Nom, photo, couleur, prix (facultatif), nom affiché sur la photo |
| **Avis** | Prénom, photo, texte |
| **Équipe** | Prénom, photo, rôle |
| **Pages** | Titre et texte d'introduction de « Nous rencontrer », mentions légales |
| **Apparence › Personnaliser › Planty** | Textes des pages, image de la canette, rangée de canettes |
| **Apparence › Menus** | Menu principal (l'élément avec la classe CSS `bouton` devient le bouton rose) et pied de page |

## Structure

```
themes/planty/            Thème (affichage)
  assets/scss/            Styles Sass, architecture 7-1 (abstracts, base, components, layout, pages)
  assets/css/main.css     Généré par Sass, ne pas modifier à la main
  assets/js/main.js       Menu mobile, apparitions, commande
  inc/                    Réglages (Customizer), textes par défaut, fonctions d'affichage
  template-*.php          Modèles des pages Nous rencontrer et Commander
plugins/planty-core/      Extension (contenus) : Goûts, Avis, Équipe, formulaires
```

Les contenus sont dans l'extension pour rester en place si le thème change.

## Installation

1. Copier `themes/planty` et `plugins/planty-core` dans `wp-content`.
2. Activer le thème « Planty » puis l'extension « Planty — Contenus ».
3. Dans le tableau de bord, cliquer sur **Installer le contenu de la maquette** (pages, menus, goûts, avis, équipe).

## Modifier les styles

```bash
npm install          # une seule fois
npm run sass         # compile assets/scss en assets/css/main.css
npm run sass:watch   # recompile à chaque enregistrement
```

## Choix techniques

- Thème sans constructeur de pages : le client remplit des champs, la mise en forme suit.
- Formulaires sans extension tierce : vérification des champs, jeton de sécurité, piège à robots, envoi avec `wp_mail()`.
- Polices Syne et Inter hébergées sur le site, images WebP.
- Animations désactivées si le visiteur a demandé moins de mouvement (`prefers-reduced-motion`).
