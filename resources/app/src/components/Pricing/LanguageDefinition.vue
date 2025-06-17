<template>
  <b-card no-body variant="info" class="pricing-language-definition">
    <template v-slot:header>
      <b-button size="lg" block href="#" v-b-toggle.language-definition variant="transparent">
        Définition du langage de tarification
      </b-button>
    </template>

    <b-collapse id="language-definition">
      <div class="card-body">
        <p>Une règle par ligne.</p>

        <p>
          Une règle est de la forme <code>SI condition ALORS expression</code> (forme dite
          conditionnelle) ou <code>expression</code> où<br />
        </p>

        <ul>
          <li>
            <code>condition</code> est de la forme
            <code>expression comparateur expression (opérateur_logique condition)*</code>;
          </li>

          <li>
            <code>expression</code> est un équation mathématique composée d'une ou plusieurs
            variables, <code>opérateur</code> et de constantes;
          </li>

          <li><code>opérateur</code> est un opérateur arithmétique;</li>

          <li><code>opérateur_logique</code> est un opérateur logique;</li>

          <li><code>comparateur</code> est un opérateur de comparaison;</li>
        </ul>

        <p>
          Un tableau est une liste entre crochets de valeurs séparées par des virgules, par ex.
          <code>[1,2,3]</code>.
        </p>

        <p>
          La dernière règle ne peut pas être conditionnelle (doit être une <code>expression</code>).
        </p>

        <h4>Valeurs de retour</h4>

        <p>
          Chaque ligne doit retourner une valeur numérique ou un tableau avec deux valeurs
          numériques. Dans ce cas, la première valeur est le prix et la deuxième valeur est
          l'assurance. Si une seule valeur numérique est fournie, l'assurance est considérée être
          zéro.
        </p>

        <h4>Opérateurs disponibles</h4>

        <ul>
          <li>Arithmétique</li>
          <ul>
            <li><code>+</code> (addition)</li>
            <li><code>-</code> (soustraction)</li>
            <li><code>*</code> (multiplication)</li>
            <li><code>/</code> (division)</li>
            <li><code>%</code> (modulo)</li>
            <li><code>**</code> (exponentiation e.g. <code>2**3</code> = 2<sup>3</sup> = 8)</li>
          </ul>

          <li>Comparaison</li>
          <ul>
            <li><code>==</code> (égalité)</li>
            <li><code>!=</code> (différence)</li>
            <li><code>&lt;</code> (plus petit)</li>
            <li><code>&lt;=</code> (plus petit ou égal)</li>
            <li><code>&gt;</code> (plus grand)</li>
            <li><code>&gt;=</code> (plus grand ou égal)</li>
          </ul>

          <li>Logique</li>
          <ul>
            <li><code>!</code> ou <code>NON</code> (non)</li>
            <li><code>&&</code> ou <code>ET</code> (et)</li>
            <li><code>||</code> ou <code>OU</code> (ou)</li>
          </ul>

          <li>Tableaux</li>
          <ul>
            <li><code>DANS</code> (inclus)</li>
            <li><code>PAS DANS</code> (non inclus)</li>
          </ul>

          <li>Autres</li>
          <ul>
            <li><code>..</code> (plage e.g. <code>2..4</code> veut dire <code>[2, 3, 4]</code>)</li>
          </ul>
        </ul>

        <p>
          Les règles de priorité des opérateurs s'appliquent. Utilisez des parenthèses pour forcer
          un ordre.
        </p>

        <p>
          On peut appeler une fonction avec la syntaxe suivante:
          <code>fonction(expression)</code>.
        </p>

        <p>Les fonctions suivantes sont disponibles:</p>

        <h4>Fonctions disponibles</h4>

        <ul>
          <li><code>PLANCHER(nombre)</code>: valeur entière inférieure</li>
          <li><code>PLAFOND(nombre)</code>: valeur entière supérieure</li>
          <li><code>ARRONDI(nombre)</code>: valeur entière arrondie</li>
          <li><code>DOLLARS(nombre)</code>: valeur arrondie à deux décimales</li>
        </ul>

        <h4>Variables disponibles</h4>

        <ul>
          <li><code>$KM</code>, un entier représentant le kilométrage de l'emprunt;</li>
          <li><code>$MINUTES</code>, un entier représentant la durée de l'emprunt en minutes;</li>
          <li>
            <code>$SURCOUT_ASSURANCE</code>, un booléen représentant l'application du surcoût
            d'assurance pour les véhicules de 5 ans ou moins (toujours faux pour d'autres objets que
            les voitures)
          </li>
          <li>
            <code>$OBJET</code>, une entité donnant accès à l'objet touché par la tarification (non
            accessible pour la tarification générique).
          </li>
          <li><code>$EMPRUNT</code>, un objet contenant les valeurs:</li>
          <ul>
            <li><code>days</code>: le nombre de jours de calendriers</li>
            <li><code>start</code>: un <code>objet de date</code></li>
            <li><code>end</code>: un <code>objet de date</code></li>
          </ul>
          <li>un <code>objet de date</code> contient les valeurs:</li>
          <ul>
            <li><code>year</code></li>
            <li><code>month</code></li>
            <li><code>day</code></li>
            <li><code>hour</code></li>
            <li><code>minute</code></li>
            <li><code>day_of_year</code></li>
            <li><code>year_eight_months_ago</code></li>
          </ul>
        </ul>

        <p>
          En ce qui concerne <code>$OBJET</code> et <code>$EMPRUNT</code>, on peut accéder à leurs
          propriétés avec un point. Par exemple <code>OBJET.engine</code> pour le mode de combustion
          d'une voiture ou <code>OBJET.size</code> pour la taille d'un vélo.
        </p>

        <h4>Commentaires</h4>

        <p>On peut ajouter des commentaires en les faisant commencer par <code>#</code>.</p>

        <p>
          Se référer à <a :href="unitTestUrl" target="_blank">ce fichier</a> pour des exemples
          d'utilisation.
        </p>
      </div>
    </b-collapse>
  </b-card>
</template>

<script>
export default {
  name: "LanguageDefinition",
  data() {
    return {
      unitTestUrl:
        "https://gitlab.com/mobicoop/colocauto/colocauto/" +
        "-/blob/master/tests/Unit/Models/PricingTest.php",
    };
  },
};
</script>

<style lang="scss">
.pricing-language-definition {
  margin-bottom: 20px;

  .card-header {
    padding: 0;

    > a {
      padding: 0.75rem 1.25rem;
    }
  }
}
</style>
