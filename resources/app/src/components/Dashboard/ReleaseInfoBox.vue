<template>
  <div class="dashboard-release-info-box">
    <b-alert variant="success" show class="dashboard-release-info-box_changeset">
      <h2>LocoMotion évolue</h2>
      <h3>Dernière mise à jour, 10 septembre 2020</h3>

      <p>Nouveautés:</p>
      <ul>
        <li>
          Ouverture au quartier La Petite-Patrie (nouvelle disponibilité des véhicule
          collectifs)
        </li>
        <li>
          Nouvelle FAQ
        </li>
        <li>
          Si vous avez un véhicule, vous pouvez maintenant le partager à tout le quartier!
          Par défaut, on partage votre auto ou votre vélo juste à votre voisinage. Après,
          vous avez le choix. (Dans votre page Mes Véhicules)
        </li>
        <li>
          Refonte de la page Quartier/Voisinage
        </li>
      </ul>
    </b-alert>
  </div>
</template>

<script>
import UserMixin from '@/mixins/UserMixin';

export default {
  name: 'DashboardReleaseInfoBox',
  mixins: [UserMixin],
};
</script>

<style lang="scss">
.dashboard-release-info-box {
  &__question {
    border: 1px solid $light-grey;
    padding: 1rem;
    background: $success;
    color: $white;

    .form-group {
      margin-bottom: 0;
    }
  }

  &__buttons {
    margin-top: 1rem;
    text-align: center;
  }
}
</style>
