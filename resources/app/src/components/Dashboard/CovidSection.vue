<template>
  <div class="dashboard-covid-section">
    <b-jumbotron bg-variant="warning"
      :header="'COVID-19 | Informations'" lead="LocoMotion est avec vous">
      <b-button v-b-modal="'covid-modal'">En savoir plus</b-button>
    </b-jumbotron>

    <b-modal size="md" class="covid-modal"
      title="COVID-19: Quelques informations importantes"
      id="covid-modal" footer-class="d-none">
      <p class="covid-modal__subtitle">VOUS ÊTES MALADE OU VOUS REVENEZ DE VOYAGE?</p>
      <p>→  N’UTILISEZ PAS LOCOMOTION</p>

      <p class="covid-modal__subtitle">POUR LES PROPRIÉTAIRES D'AUTO</p>
      <p>
        Si vous souhaitez retirer temporairement votre auto,
        pensez à mettre à jour votre calendrier.
      </p>

      <p class="covid-modal__subtitle">POUR TOU-TE-S LES PARTICIPANT-E-S</p>
      <p>
        Avant et après l’utilisation d’une voiture, LAVEZ VOS MAINS à l’eau courante
        tiède et au savon pendant au moins 20 secondes ou utilisez un désinfectant à
        base d’alcool.
      </p>

      <p class="covid-modal__subtitle">GARDEZ LES AUTOS PROPRES</p>
      <p>
        Que ce soit votre autre ou celle de votre voisin-e, voici quelques
        recommandations à prendre avant et après son utilisation:
      </p>
      <ul>
        <li>
          Nettoyez le volant et autres surfaces avec un linge et du désinfectant.
        </li>
        <li>
          Évitez plus que jamais de laisser tout déchet
          (mouchoir, tasse, emballage, etc…)
        </li>
      </ul>
    </b-modal>
  </div>
</template>

<script>
export default {
  name: 'CovidSection',
};
</script>

<style lang="scss">
.dashboard-covid-section {
  .jumbotron {
    margin-bottom: 0;
  }
}
</style>
