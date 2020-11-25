<template>
  <div class="dashboard-release-info-box">
    <b-alert variant="success" show class="dashboard-release-info-box_changeset">
      <h2>LocoMotion évolue</h2>
      <h3>Dernière mise à jour, 10 septembre 2020</h3>

      <p>Nouveautés:</p>
      <ul>
        <li>
          Ouverture au quartier Petite-Patrie (nouvelle disponibilité des véhicule
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

      <b-modal id="version-2020-09-10" size="lg"
        no-close-on-backdrop no-close-on-esc
        hide-header hide-footer>
        <h2>LocoMotion évolue</h2>
        <h3>Dernière mise à jour, 10 septembre 2020</h3>

        <p>Nouveautés:</p>
        <ul>
          <li>
            Ouverture au quartier Petite-Patrie (nouvelle disponibilité des véhicule
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

        <div class="dashboard-release-info-box__question">
          <forms-validated-input type="checkbox" name="accept_conditions"
            :label="$t('users.fields.accept_conditions') | capitalize"
            :disabled="user.accept_conditions"
            :value="user.accept_conditions"
            @input="updateAcceptConditions" />
        </div>

        <div class="dashboard-release-info-box__question">
          <forms-validated-input type="checkbox" name="opt_in_newsletter"
            :label="$t('users.fields.opt_in_newsletter') | capitalize"
            :value="user.opt_in_newsletter"
            @input="updateOptInNewsletter" />
        </div>

        <div class="dashboard-release-info-box__question" v-if="hasBorough">
          <h3>Accessibilité aux quartiers</h3>

          <p>Les véhicules peuvent être mis à disposition au niveau du quartier.</p>
          <p>
            <b-button variant="light" v-b-modal="'borough-difference-modal'">
              Voisinage, quartier: quelle différence?
            </b-button>
          </p>

          <div v-for="loanable in user.loanables" :key="loanable.id">
            <p><strong>{{ loanable.name }}</strong></p>
            <forms-validated-input v-if="loanableBoroughs(loanable).length > 0"
              :description="$t('loanables.descriptions.share_with_parent_communities')"
              :label="$t(
                'loanables.fields.share_with_parent_communities_dynamic',
                {
                  shared_with: loanableBoroughsMessage(loanable),
                }
              ) | capitalize"
              name="share_with_parent_communities" type="checkbox"
              :value="loanable.share_with_parent_communities"
              @input="updateLoanableShareWithParentCommunities(loanable, $event)"/>
          </div>
        </div>

        <div class="dashboard-release-info-box__buttons">
          <b-button @click="seeVersion('1.1.0')" :disabled="!user.accept_conditions">
            J'ai compris!
          </b-button>
        </div>
      </b-modal>

      <borough-difference-modal />
    </b-alert>
  </div>
</template>

<script>
import UserMixin from '@/mixins/UserMixin';

import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import BoroughDifferenceModal from '@/components/Misc/BoroughDifferenceModal.vue';

export default {
  name: 'DashboardReleaseInfoBox',
  mixins: [UserMixin],
  components: {
    BoroughDifferenceModal,
    FormsValidatedInput,
  },
  mounted() {
    if (!this.hasSeenVersion('1.1.0', '2020-09-10')) {
      this.$bvModal.show('version-2020-09-10');
    }
  },
  computed: {
    hasBorough() {
      return this.user.communities.reduce((acc, c) => acc || !!c.parent, false);
    },
  },
  methods: {
    hasSeenVersion(version, releaseDate) {
      if (!this.$store.state.seenVersions) {
        return false;
      }

      return this.$store.state.seenVersions.indexOf(version) !== -1
        || releaseDate <= `${this.user.created_at}`;
    },
    loanableBoroughs(loanable) {
      return this.loanableCommunities(loanable).map(c => c.parent).filter(c => !!c);
    },
    loanableBoroughsMessage(loanable) {
      return this.loanableBoroughs(loanable)
        .map(b => b.name)
        .filter((item, i, arr) => arr.indexOf(item) === i)
        .join(', ');
    },
    loanableCommunities(loanable) {
      if (loanable.community) {
        return [loanable.community];
      }

      return this.user.communities;
    },
    seeVersion(version) {
      this.$store.commit('seeVersion', version);
      this.$bvModal.hide('version-2020-09-10');
    },
    async updateAcceptConditions(value) {
      await this.$store.dispatch('users/update', {
        id: this.user.id,
        data: {
          id: this.user.id,
          accept_conditions: value,
        },
        params: {
          fields: 'id,name,accept_conditions',
        },
      });

      this.$store.commit('user', {
        ...this.user,
        accept_conditions: value,
      });
    },
    async updateLoanableShareWithParentCommunities(loanable, value) {
      await this.$store.dispatch('loanables/update', {
        id: loanable.id,
        data: {
          id: loanable.id,
          type: loanable.type,
          share_with_parent_communities: value,
        },
        params: {
          fields: 'id,name,share_with_parent_communities',
        },
      });
    },
    async updateOptInNewsletter(value) {
      await this.$store.dispatch('users/update', {
        id: this.user.id,
        data: {
          id: this.user.id,
          opt_in_newsletter: value,
        },
        params: {
          fields: 'id,name,opt_in_newsletter',
        },
      });

      this.$store.commit('user', {
        ...this.user,
        opt_in_newsletter: value,
      });
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
