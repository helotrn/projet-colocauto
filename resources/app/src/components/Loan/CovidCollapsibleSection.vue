<template>
  <b-alert show variant="warning" v-b-toggle.covid-collapsible-section
    class="loans-covid-collapsible-section">
    <div class="alert-heading text-center">
      <h4>Important: informations relatives à la COVID-19</h4>
      <p class="mb-0">v Dérouler pour lire v</p>
    </div>

    <b-collapse id="covid-collapsible-section">
      <dl class="mt-3">
        <dt>Pour les propriétaires d'auto</dt>
        <dd>
          Si vous souhaitez retirer temporairement votre auto,
          pensez à mettre à jour ses disponibilités.
        </dd>

        <dt>Pour tout le monde</dt>
        <dd>
          Avant et après l’utilisation d’un vélo, d'une remorque ou d'une auto,
          <strong>lavez vos mains</strong> à l’eau courante tiède et au savon pendant
          au moins 20 secondes ou utilisez un désinfectant à base d’alcool.<br>
          Pendant un trajet à vélo, respectez les 2 mètres de distance.
        </dd>

        <dt>Gardez les véhicules propres</dt>
        <dd>
          Nettoyez le volant, le guidon ou toutes autres surfaces de contact
          avec un linge et du désinfectant. Évitez plus que jamais de laisser tout
          déchet dans les autos et les remorques  (mouchoir, tasse, emballage, etc…).
        </dd>
      </dl>
    </b-collapse>
  </b-alert>
</template>

<script>
export default {
  name: 'LoanCovidCollapsibleSection',
};
</script>

<style lang="scss">
.loans-covid-collapsible-section {
  .alert-heading {
    cursor: pointer;
  }
}
</style>
