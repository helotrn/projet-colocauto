## Outils de développement

Ce répertoire contient quelques outils pour simplifier le développement.

### locomotion.bashrc

Une liste d'alias a ajouter à vôtre terminal. Pour les installer, ajoutez la ligne suivante à votre [configuration de _shell_](https://www.gnu.org/software/bash/manual/html_node/Bash-Startup-Files.html) (`.bashrc`, `.zshrc`).

```bash
source path/to/project/devtools/locomotion.bashrc
```

**Alias**

Consultez le fichier `locomotion.bashrc` pour avoir la liste complète.

Quelques examples:

-   `dcep [commande]` : exécute la [commande] dans le conteneur docker avec le serveur php.
-   `locoseed` : _seed_ la base de données avec des données par défaut.
-   `locomigr` : exécute les migrations de la base de donnée.
-   `locotest` : exécute les tests unitaires et d'intégration
-   `locopretty` : exécute prettier sur tous les fichiers
