<template>
  <layout-page name="community" wide>
    <div  v-if="routeDataLoaded">
      <div class="community__header">
        <h1>{{ community.name }}</h1>
      </div>

      <b-container class="community__content">
        <b-row>
          <b-col lg="3">
            <nav>
              <a href="#mes-voisins">Mes voisins</a><br>
              <a href="#nouvelles-de-la-communaute">Nouvelles de la communauté</a><br>
              <a href="#activites-locomotion">Activités Locomotion</a>
            </nav>
          </b-col>

          <b-col lg="9">
            <div class="page__section">
              <h2 id="mes-voisins">Mes voisins</h2>

              <div class="page__section__users">
                <p v-for="user in community.users" :key="user.id">
                  {{ user.full_name }}
                </p>
              </div>
            </div>

            <div class="page__section">
              <h2 id="nouvelles-de-la-communaute">Nouvelles de ma communauté</h2>
            </div>

            <div class="page__section">
              <h2 id="activites-locomotion">Activités Locomotion</h2>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </div>
    <layout-loading v-else />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import Notification from '@/mixins/Notification';

export default {
  name: 'CommunityDashboard',
  mixins: [Authenticated, DataRouteGuards, Notification],
  computed: {
    community() {
      return this.$store.state.communities.item;
    },
  },
};
</script>

<style lang="scss">
.page.community {
  .community__content {
    margin-top: 85px;
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
