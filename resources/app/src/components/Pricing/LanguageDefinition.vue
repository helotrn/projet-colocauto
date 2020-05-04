<template>
  <b-card no-body variant="info" class="pricing-language-definition">
    <template v-slot:header>
      <b-button size="lg" block href="#"
        v-b-toggle.language-definition variant="transparent">
        Définition du langage de tarification
      </b-button>
    </template>

    <b-collapse id="language-definition">
      <div class="card-body">
        <p>
          Une règle par ligne.
        </p>

        <p>
          Une règle est de la forme <code>SI condition ALORS calcul</code> (forme dite
          conditionnelle) ou <code>calcul</code> où<br>
        </p>

        <ul>
          <li>
            <code>condition</code> est de la forme <code>expression comparateur expression
              (opérateur_logique condition)*</code>;
          </li>

          <li>
            <code>expression</code> est un équation mathématique composée d'un ou
            plusieurs variables, <code>opérateur</code> et de constantes;
          </li>

          <li>
            <code>opérateur</code> est un opérateur mathématique;
          </li>

          <li>
            <code>opérateur_logique</code> est un opérateur logique parmi
            <code>ET OU</code>.
          </li>
        </ul>

        <p>
          La dernière règle ne peut pas être conditionnelle
          (doit être une <code>expression</code>).
        </p>

        <h4>Opérateurs disponibles</h4>

        <ul>
          <li>Arithmétiques</li>
          <ul>
            <li><code>+</code> (addition)</li>
            <li><code>-</code> (soustraction)</li>
            <li><code>*</code> (multiplication)</li>
            <li><code>/</code> (division)</li>
            <li><code>%</code> (modulo)</li>
            <li>
              <code>**</code> (exponentiation e.g. <code>2**3</code> = 2<sup>3</sup> = 8)
            </li>
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
            <li><code>!</code> ou <code>not</code> (non)</li>
            <li><code>&&</code> (et)</li>
            <li><code>||</code> (ou)</li>
          </ul>

          <li>Tableaux</li>
          <ul>
            <li><code>in</code> (inclus)</li>
            <li><code>not in</code> (non inclus)</li>
          </ul>

          <li>Autres</li>
          <ul>
            <li>
              <code>..</code> (plage e.g. <code>2..4</code> veut dire <code>[2, 3, 4]</code>)
            </li>
          </ul>
        </ul>

        <p>
          Les règles de priorité des opérateurs s'appliquent. Utilisez des parenthèses
        pour forcer un ordre.
        </p>

        <h4>Fonctions disponibles</h4>

        <ul>
          <li><code>PLANCHER(nombre)</code>: valeur entière inférieure</li>
          <li><code>PLAFOND(nombre)</code>: valeur entière supérieure</li>
          <li><code>ARRONDI(nombre)</code>: valeur arrondie</li>
        </ul>

        <p>
          On peut appeler une fonction avec la syntaxe suivante:
          <code>fonction(expression)</code>.
        </p>

        <p>Les fonctions suivantes sont disponibles:</p>

        <h4>Variables disponibles</h4>
        <ul>
          <li><code>$KM</code>, un entier représentant le kilométrage de l'emprunt;</li>
          <li><code>$MINUTES</code>, un entier représentant la durée de l'emprunt en
            minutes;</li>
          <li><code>$OBJET</code>, une entité donnant accès à l'objet touché par la
            tarification (non accessible pour la tarification générique).</li>
        </ul>

        <p>
          En ce qui concerne <code>OBJET</code>, on peut accéder à ses propriétés avec
          un point. Par exemple <code>OBJET.engine</code> pour le mode de combustion d'une
          voiture ou <code>OBJET.size</code> pour la taille d'un vélo.
        </p>
      </div>
    </b-collapse>
  </b-card>
</template>

<script>
export default {
  name: 'LanguageDefinition',
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
