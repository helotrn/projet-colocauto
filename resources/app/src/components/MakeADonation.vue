<template>
  <b-card no-body>
    <b-card-header style="background-color: transparent">
      <a href="https://www.zeste.coop/fr/associations/Colocauto" class="d-flex" target="_blank">
        <salut-coeur width="100px" class="p-2" />
        <div class="pl-2">
          <h2 class="text-dark">Faire un don</h2>
          <span class="text-dark">Partagez des voitures sans frais, sans commission.
          Coloc'Auto est possible grace à vos dons.</span>
        </div>
      </a>
    </b-card-header>
  </b-card>
</template>
<script>
import SalutCoeur from "@/assets/svg/salut-coeur.svg";

export default {
  name: 'MakeADonation',
  components: {SalutCoeur},
};
</script>
