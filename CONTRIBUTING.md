# Contribuer à locomotion.app

Bienvenue sur le guide de contribution de locomotion.app.

Tout d'abord, merci de considérer contribuer à Locomotion.app. Nous essayons d'incarner une plateforme "coop", plus local, axée vers la transition socio-écologique et donc avec une gouvernance la plus ouverte et démocratique possible. 

Contribuer à Locomotion, c'est renforcer ces principes. Merci pour cela. 

Avant de soumettre une contribution, assurez-vous d'avoir lu ce guide.

Nous sommes ouverts à une variété de contributions :

-   soumission de bugs;
-   correction de bugs;
-   proposition de nouvelles fonctionnalités;
-   amélioration de la couverture par des tests automatisés;
-   proposition d'améliorations à la simplicité et la clarté du code;
-   proposition de bonnes pratiques et standards à adopter;
-   amélioration de la documentation
-   amélioration de l'accessibilité universelle et élaboration de bonne pratiques;
-   etc.

Si vous débutez, nous maintenons une liste d'incidents à l'intention des
néophytes : ~18698008.

Si vous vous sentez prêt pour l'aventure, nous vous invitons à contribuer aux prochains [sprints](https://gitlab.com/solon-collectif/locomotion.app/-/milestones). 

## Code de conduite

Nous souhaitons promouvoir un espace sécuritaire pour tou-te-s.

Nous exigeons de nous-mêmes et de nos collaborateur-trice-s :

-   d'adopter un ton poli et respectueux des opinions différentes;
-   de faire preuve d'empathie et de bienveillance;
-   d'adopter une attitude d'apprentissage et d'entraide;
-   de ne pas tolérer les propos sexistes, racistes ou dénigrants, etc. ou le
    harcèlement sous quelque forme que ce soit;
-   de tenter de régler les différends avec les personnes concernées et d'avoir
    la décence d'offrir ses excuses le cas échéant.

Nous nous réservons le droit de supprimer tout commentaire jugé contraire à ces
principes, de révoquer l'accès de quiconque ou de contacter les autorités
compétentes au besoin.

## Rapporter un bug

Pour rapporter un bug, rendez vous sur notre [gestionnaire d'incidents](https://gitlab.com/Solon-collectif/locomotion.app/-/issues).

Débutez d'abord par faire une recherche pour vérifier que le même problème n'a
pas déjà été rapporté.

Si le problème a déjà été rapporté et que vous disposez d'informations
permettant de mieux cibler sa cause, n'hésitez pas à commenter l'incident
concerné.

Si le problème est nouveau, indiquez avec autant de détails que nécessaire, les
étapes qui permettent de reproduire le problème. Décrivez le comportement
observé et le comportement attendu. N'hésitez pas à fournir des captures
d'écran, ou même des extraits vidéo.

Utilisez les mots clés appropriés, qui faciliteront la recherche. Dans le même
esprit, favorisez l'utilisation des mots en entier plutôt que leurs
abréviations ou acronymes.

## Corriger un bug

À moins qu'il ne s'agisse d'une erreur évidente, dont la correction n'ait pas
besoin de mise en contexte (faute d'orthographe, etc.), les bugs doivent
d'abord être documentés dans un rapport d'incident. Voir "Rapporter un bug".

Il faut minimalement disposer des privilèges de réviseur-e (Reviewer) pour
assigner un incident à quelqu'un (incluant vous). Si ce n'est pas votre cas,
contactez-nous, nous essaierons de vous donner les permissions nécessaires et
vous assignerons l'incident.

Bonnes pratiques de correction d'un bug :

-   créer un test unitaire qui met le bug en évidence;
-   corriger le bug;
-   valider la correction du bug avec le test.

Documentez et commentez le code autant que nécessaire.

Une fois le problème corrigé, vous pourrez créer une demande de fusion (merge
request).

## Proposer une fonctionnalité

Parce qu'on a quand même une idée d'où on s'en va et qu'on ne veut pas vous
faire perdre votre temps, documentez toute proposition pour une nouvelle
fonctionnalité dans un rapport d'incident.

Nous analyserons la proposition et déciderons si cette fonctionnalité doit être
mise en oeuvre. Nous conviendrons d'un plan de mise en oeuvre avec vous, le cas
échéant, selon si vous souhaitez vous impliquer dans sa réalisation ou
simplement nous en faire la suggestion.

Dans tous les cas, commençons par en discuter.

## Demandes de fusion (Merge Requests)

Les demandes de fusion spontanées sont acceptables dans deux cas :

-   Vous corrigez un bug trivial (faute d'orthographe, erreur évidente, etc.)
-   Vous travaillez sur une contribution sur laquelle nous nous sommes mis
    d'accord, et qui a été discutée dans un incident.

Évitez de proposer une fonctionnalité à travers un merge request avant qu'on en
ait d'abord discuté.

## Sécurité

N'envoyez rien qui soit lié à la sécurité numérique sur le gestionnaire
d'incidents. Contactez-nous plutôt par courriel à
[dev+securite@solon-collectif.org](mailto:dev+securite@solon-collectif.org).
