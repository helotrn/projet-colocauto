<template>
  <layout-page name="community" wide :loading="!routeDataLoaded">
    <div class="community__header">
      <h1>{{ community.name }}</h1>
    </div>

    <b-container class="community__content">
      <b-row>
        <b-col lg="3">
          <nav>
            <a href="#mes-voisins" v-if="community.users">Mes voisins</a><br>
          </nav>
        </b-col>

        <b-col lg="9">
          <div class="page__section" v-if="community.users">
            <h2 id="mes-voisins">Mes voisins</h2>

            <div class="community__users-legend">
              <span>
                <b-badge pill variant="warning">P</b-badge>
                <span>Propriétaire de véhicule</span>
              </span>
              <span>
                <b-badge pill variant="success">A</b-badge>
                <span>Ambassadeur.rice de la communauté</span>
              </span>
            </div>

            <b-row class="page__section community__users">
              <b-col v-for="user in community.users" :key="user.id" lg="6" md="12">
                <user-card :user="user" :is-admin="isAdminOfCommunity(community)"
                  :community-id="community.id" @updated="reload" />
              </b-col>
            </b-row>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </layout-page>
</template>

<script>
import UserCard from '@/components/User/UserCard.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';

export default {
  name: 'CommunityDashboard',
  mixins: [Authenticated, DataRouteGuards],
  components: {
    UserCard,
  },
  computed: {
    community() {
      return this.$store.state.communities.item || {};
    },
  },
  methods: {
    async reload() {
      this.reloading = true;
      await this.loadDataRoutesData(this, this.$route);
      this.reloading = false;
    },
  },
};
</script>

<style lang="scss">
.page.community {
  .page__section h2 {
    margin-bottom: 40px;
  }

  .page__section:nth-child(2) {
    margin-top: -30px;
  }

  .community__users-legend {
    margin-bottom: 40px;

    > span {
      margin-right: 10px;
    }

    .badge {
      line-height: 15px;
      height: 21.45px;
      margin-right: 5px;
    }
  }

  .community__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }

  .community__header {
    height: 450px;
    background-image: url("/img-tetes.png");
    background-color: $locomotion-dark-green;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: bottom -50px right -50px;

    h1 {
      padding-top: 110px;
      padding-left: 90px;
      font-size: 60px;
      font-weight: normal;
      color: $white;
    }
  }
}
</style>
