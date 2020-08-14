<template>
  <layout-page name="community" wide :loading="!routeDataLoaded">
    <div class="community__header">
      <h1>{{ community.name }}</h1>
    </div>

    <b-container>
      <b-row class="community__description page__section" v-if="community.long_description">
        <b-col>
          <b-alert variant="info" show>
            <div v-html="community.long_description" />
          </b-alert>
        </b-col>
      </b-row>

      <b-row class="page__section text-center" v-if="community.type === 'neighborhood'">
        <b-col>
          <h2>Mon voisinage</h2>
        </b-col>
      </b-row>
    </b-container>

    <b-container fluid v-if="community.type === 'neighborhood'">
      <b-row class="community__neighbors page__section">
        <b-col>
          <div v-if="community.users">
            <b-container class="community__users-legend">
              <b-row>
                <b-col>
                  <span>
                    <b-badge pill variant="warning">P</b-badge>
                    <span>Propriétaire de véhicule</span>
                  </span>
                  <span>
                    <b-badge pill variant="success">C</b-badge>
                    <span>Comité du voisinage</span>
                  </span>
                </b-col>
              </b-row>
            </b-container>

            <b-row class="community__users">
              <b-col>
                <div class="community__users__slider">
                  <user-card v-for="user in community.users" :key="user.id"
                    :user="user" :is-admin="isAdminOfCommunity(community)"
                    :community-id="community.id" @updated="reload" />
                </div>
              </b-col>
            </b-row>
          </div>
        </b-col>
      </b-row>
    </b-container>

    <b-container>
      <b-row class="community__organize page__section" v-if="community.chat_group_url">
        <b-col>
          <h2>Un espace pour s'organiser</h2>
        </b-col>
      </b-row>

      <b-row class="page__section text-center" v-if="community.chat_group_url">
        <b-col>
          <p>
            La prochaine fête de voisinage c’est quand? Besoin d’aide? Où proposer mon idée
            pour améliorer la vie de quartier? Quand on ne se voit pas en personne, les
            réponses à ces questions se trouvent sur notre groupe Facebook!
          </p>

          <p>
            <a :href="community.chat_group_url" target="_blank">
              <img src="/icons/messenger.png">
            </a>
          </p>
        </b-col>
      </b-row>

      <b-row class="community__area page__section">
        <b-col>
          <h2>Les voisinages du quartier</h2>
        </b-col>
      </b-row>

      <b-row v-if="borough && neighborhoods" class="page__section">
        <b-col class="community__map">
          <schematized-community-map
            :borough="borough" :neighborhoods="neighborhoods" />

            <div class="community__map__total">
              {{ totalUsersCount }}<br>
              voisines et voisins participent à
              LocoMotion dans votre quartier!
            </div>
        </b-col>
      </b-row>

      <b-row class="text-center" v-if="community.type === 'borough'">
        <b-col>
          <p>
            <b-button variant="success" size="lg">Créer un voisinage</b-button>
            <br>
            <a href="#" v-b-modal="'borough-difference-modal'">
              <small>Voisinage, quartier: quelle différence?</small>
            </a>
          </p>

          <borough-difference-modal />
        </b-col>
      </b-row>
    </b-container>
  </layout-page>
</template>

<script>
import BoroughDifferenceModal from '@/components/Misc/BoroughDifferenceModal.vue';
import SchematizedCommunityMap from '@/components/Misc/SchematizedCommunityMap.vue';
import UserCard from '@/components/User/UserCard.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';

export default {
  name: 'CommunityDashboard',
  mixins: [Authenticated, DataRouteGuards],
  components: {
    BoroughDifferenceModal,
    SchematizedCommunityMap,
    UserCard,
  },
  computed: {
    borough() {
      return this.community.type === 'borough'
        ? this.community
        : this.community.parent;
    },
    community() {
      return this.$store.state.communities.item || {};
    },
    neighborhoods() {
      return this.community.type === 'borough'
        ? this.community.children
        : this.community.parent.children;
    },
    totalUsersCount() {
      return this.borough.users_count
        + this.neighborhoods.reduce((acc, c) => acc + c.users_count, 0);
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
@import "~bootstrap/scss/mixins/breakpoints";

.page.community {
  .page__content {
    padding-bottom: 60px;
  }

  .page__section h2 {
    margin-bottom: 40px;
    text-align: center;
  }

  .community {
    &__description {
      margin-top: 60px;
    }

    &__organize {
      margin-bottom: 0;
    }

    &__neighbors {
      &.page__section {
        margin-bottom: 60px;
      }

      &:nth-child(2) {
        margin-top: -30px;
      }
    }

    &__users {
      &__slider {
        overflow-y: hidden;
        overflow-x: scroll;

        display: flex;
        flex-wrap: wrap;
        flex-direction: column;

        height: 410px;

        .user-card {
          height: 184px;
          width: 450px;
          margin: 0 30px 30px 0;

          &:nth-child(2n) {
            margin-bottom: 0;
          }

          &:nth-child(1), &:nth-child(2) {
            margin-left: 0;
          }
        }
      }
    }

    &__map {
      max-width: 600px;
      margin: 0 auto;

      .schematized-community-map {
        margin-bottom: 40px;
      }

      &__total {
        color: $locomotion-light-green;
        font-size: 48px;
        text-align: center;
      }
    }

    &__users-legend {
      margin-bottom: 40px;

      .col > span {
        margin-right: 10px;
      }

      .badge {
        line-height: 15px;
        height: 21.45px;
        margin-right: 5px;
      }
    }

    &__area {
      margin-top: 60px;
    }

    &__header {
      height: 300px;
      background-image: url("/img-tetes.png");
      background-color: $locomotion-dark-green;
      background-size: auto 300px;
      background-repeat: no-repeat;
      background-position: bottom -50px right -50px;

      h1 {
        font-weight: normal;
        color: $white;
        padding-top: 55px;
        padding-left: 45px;
        font-size: 40px;

        @include media-breakpoint-up(lg) {
          padding-top: 110px;
          padding-left: 90px;
          font-size: 60px;
        }
      }

      @include media-breakpoint-up(lg) {
        height: 450px;
        background-size: auto 450px;
      }
    }
  }
}
</style>
