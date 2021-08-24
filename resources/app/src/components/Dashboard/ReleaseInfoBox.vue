<template>
  <div class="dashboard-release-info-box">
    <b-alert variant="success" show class="dashboard-release-info-box_changeset">
      <p>Nouveautés&nbsp;:</p>
      <ul>
        <div v-if="userInAhuntsic">
          <li>Ouverture au quartier Ahuntsic (nouvelle disponibilité des véhicules collectifs)</li>
          <li>Nouvelle FAQ</li>
          <li>
            Si vous avez un véhicule, vous pouvez maintenant le partager à tout le quartier! Par
            défaut, on partage votre auto ou votre vélo juste à votre voisinage. Après, vous avez le
            choix. (Dans votre page Mes Véhicules)
          </li>
          <li>Refonte de la page Quartier/Voisinage</li>
        </div>

        <li>
          Version du 23 août 2021
          <ul>
              <li>Mise en place d'une nouvelle architecture qui nous permettra de déployer plus rapidement nos changements</li>
              <li>Le texte des 7 voisinages a été mis à jour</li>
              <li>Correction de sécurité pour effacer toutes les données après une déconnexion</li>
              <li>Les dossiers de conduite et les rapports de sinistres sont maintenant accessibles par l'utilisateur</li>
              <li>Corrections orthographiques multiples</li>
              <li>On peut maintenant annuler une réservation depuis le tableau de bord</li>
              <li>On ne peut plus réserver un véhicule avant d'être approuvé</li>
              <li>On peut maintenant modifier une image de preuve d'adresse</li>
              <li>Clarification de l'affichage des mouvements de fonds sur le compte utilisateur dans la liste des factures</li>
              <li>Supression du champ "contrat d'assurance" dans la section "emprunter un véhicule" et dans l'inscription</li>
          </ul>
        </li>
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
