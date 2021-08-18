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
          Version du 2 août 2021
          <ul>
            <li>Utilisateurs</li>
            <ul>
              <li>Ajout d'un icone sur l'onglet de l'application</li>
              <li>
                Retirer le bloc "réserver un véhicule" de la page d'accueil quand on est en attente
                d'approbation
              </li>
              <li>Nouveau message d'information pour les emprunts</li>
              <li>Modification de la mise en page de tous les courriels</li>
              <li>On peut maintenant changer l'image de preuve d'adresse</li>
              <li>
                Au rechargement d'une page, il n'y a plus de redirection automatique vers la page de
                connexion
              </li>
              <li>Nouveau look du menu de profil</li>
              <li>
                Dans la barre de navigation, la photo de profil de l'utilisateur ou ses initiales
                sont affichées
              </li>
            </ul>
            <li>Administrateurs</li>
            <ul>
              <li>
                Dans les listes, pour les véhicules, les anciens boutons "supprimer" indiquent
                maintenant "archiver"
              </li>
              <li>Les liens de la section emprunts de l'administration fonctionnent</li>
              <li>On peut chercher des membres avec seulement une partie de l'adresse courriel</li>
              <li>
                Dans les filtres, il est maintenant possible de cliquer n'importe où dans l'écran
                pour sortir du filtre
              </li>
              <li>
                Le filtre de date sur une période d'un mois affiche maintenant tous les emprunts du
                mois
              </li>
            </ul>
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
