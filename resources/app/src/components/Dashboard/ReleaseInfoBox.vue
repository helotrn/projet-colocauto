<template>
  <div class="dashboard-release-info-box"  v-if="userInAhuntsic">
    <b-alert variant="success" show class="dashboard-release-info-box_changeset">
      <p>Nouveautés</p>
      <ul>
        <div>
          <li>Ouverture au quartier Ahuntsic (nouvelle disponibilité des véhicules collectifs)</li>
          <li>
            Si vous avez un véhicule, vous pouvez maintenant le partager à tout le quartier! Par
            défaut, on partage votre auto ou votre vélo juste à votre voisinage. Après, vous avez le
            choix.
          </li>
        </div>
      </ul>
    </b-alert>
  </div>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "DashboardReleaseInfoBox",
  mixins: [UserMixin],
  components: {},
  computed: {
    /*
      Check if user is approved in Ahuntsic or any of it's neighborhoods.
    */
    userInAhuntsic() {
      const ahuntsicCommunityNames = ["Ahuntsic", "Fleury-Est", "Fleury-Ouest", "Youville"];
  
      // uc: User's community.
      // ac: Ahuntsic's community.
      for (let uc = 0, uclen = this?.user?.communities?.length; uc < uclen; uc += 1) {
        for (let ac = 0, aclen = ahuntsicCommunityNames.length; ac < aclen; ac += 1) {
          if (
            this?.user?.communities[uc]?.name === ahuntsicCommunityNames[ac] &&
            !!this.user.communities[uc].approved_at &&
            !this.user.communities[uc].suspended_at
          ) {
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
