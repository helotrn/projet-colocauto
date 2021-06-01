<template>
  <div class="dashboard-release-info-box">
    <b-alert v-if="userInAhuntsic"
      variant="success" show class="dashboard-release-info-box_changeset">
      <h2>LocoMotion évolue</h2>

      <p>Nouveautés&nbsp;:</p>
      <ul>
        <li>
          Ouverture au quartier Ahuntsic (nouvelle disponibilité des véhicule
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
  computed: {
    /*
      Check if user is approved in Ahuntsic or any of it's neighborhoods.
    */
    userInAhuntsic() {
      const ahuntsicCommunityNames = [
        'Ahuntsic',
        'Fleury-Est',
        'Fleury-Ouest',
        'Youville',
      ];

      // uc: User's community.
      // ac: Ahuntsic's community.
      for (let uc = 0, uclen = this.user.communities.length; uc < uclen; uc += 1) {
        for (let ac = 0, aclen = ahuntsicCommunityNames.length; ac < aclen; ac += 1) {
          if (this.user.communities[uc].name === ahuntsicCommunityNames[ac]
            && !!this.user.communities[uc].approved_at && !this.user.communities[uc].suspended_at) {
            return true;
          }
        }
      }

      return false;
    },
  },
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
