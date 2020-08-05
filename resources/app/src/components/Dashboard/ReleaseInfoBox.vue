<template>
  <div class="dashboard-release-info-box">
    <b-alert variant="success" :show="!hasSeenVersion('1.1.0', '2020-08-24')"
      class="dashboard-release-info-box_changeset">
      <h2>LocoMotion évolue &mdash; 24 août 2020</h2>

      <p>
        En plus de la refonte de la page Voisinage,vous pouvez maintenant partager votre auto ou
        votre vélo à tout le quartier! Par défaut, on met à disposition votre véhicule à la plus
        petite échelle, à votre voisinage. Après, vous avez le choix.
      </p>

      <p>
        Répondez aux questions suivantes relativement aux nouvelles fonctionnalités.
      </p>

      <div class="dashboard-release-info-box__question">
        <h3>Conditions d'utilisation</h3>

        <p>Acceptez-vous les <a href="/conditions">conditions d'utilisation</a>?</p>

        <forms-validated-input type="checkbox" name="accept_conditions"
          :label="$t('users.fields.accept_conditions') | capitalize"
          :value="user.accept_conditions"
          @input="updateAcceptConditions" />
      </div>

      <div class="dashboard-release-info-box__question">
        <h3>Infolettre</h3>

        <p>Présentation de l'infolettre</p>

        <forms-validated-input type="checkbox" name="opt_in_newsletter"
          :label="$t('users.fields.opt_in_newsletter') | capitalize"
          :value="user.opt_in_newsletter"
          @input="updateOptInNewsletter" />
      </div>

      <div class="dashboard-release-info-box__question" v-if="hasBorough">
        <h3>Accessibilité aux quartiers</h3>

        <p>Les véhicules peuvent être rendus accessibles aux quartiers.</p>

        <p>Vous faites partie de {{ boroughNames }}.</p>

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
        <b-button @click="seeVersion('1.1.0')">C'est compris!</b-button>
      </div>
    </b-alert>
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

export default {
  name: 'DashboardReleaseInfoBox',
  components: { FormsValidatedInput },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    boroughNames() {
      return this.user.communities.reduce((acc, c) => {
        if (c.parent) {
          acc.push(c.parent.name);
        }

        return acc;
      }, []).join(', ');
    },
    hasBorough() {
      return this.user.communities.reduce((acc, c) => acc || !!c.parent, false);
    },
  },
  methods: {
    hasSeenVersion(version, releaseDate) {
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
    },
  },
};
</script>

<style lang="scss">
.dashboard-release-info-box {
  &__question {
    border: 1px solid $light-grey;
    padding: 1rem;
    margin-bottom: 1rem;

    .form-group {
      margin-bottom: 0;
    }
  }
}
</style>
