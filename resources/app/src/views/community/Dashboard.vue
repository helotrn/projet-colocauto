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

    <b-container
      fluid
      class="community__neighbors page__section"
    >
      <b-row no-gutters>
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
                <div class="community__users__before">
                  <svg-arrow @click="slideUsers(-415)" />
                </div>
                <div class="community__users__slider" ref="users">
                  <span class="community__users__slider__spacer" />
                  <user-card
                    v-for="user in community.users"
                    :key="user.id"
                    :user="user"
                    :is-admin="isAdminOfCommunity(community)"
                    :community-id="community.id"
                    @updated="reload"
                  />
                  <span class="community__users__slider__spacer" />
                </div>
                <div class="community__users__after">
                  <svg-arrow @click="slideUsers(415)" />
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
            La prochaine fête de voisinage c’est quand? Besoin d’aide? Où proposer mon idée pour
            améliorer la vie de quartier? Quand on ne se voit pas en personne, les réponses à ces
            questions se trouvent sur notre groupe Facebook!
          </p>

          <p>
            <a :href="community.chat_group_url" target="_blank">
              <img src="/icons/messenger.png" />
            </a>
          </p>
        </b-col>
      </b-row>

      <div v-if="borough && neighborhoods">
        <b-row class="community__area page__section">
          <b-col>
            <h2>Les voisinages du quartier</h2>
          </b-col>
        </b-row>

        <b-row v-if="borough && neighborhoods" class="page__section">
          <b-col class="community__map">
            <div class="community__map__total">
              {{ approvedUsersCount }}<br />
              voisines et voisins participent à LocoMotion dans votre quartier!
            </div>
          </b-col>
        </b-row>
      </div>
    </b-container>
  </layout-page>
</template>

<script>
import BoroughDifferenceModal from "@/components/Misc/BoroughDifferenceModal.vue";
import UserCard from "@/components/User/UserCard.vue";

import Arrow from "@/assets/svg/arrow.svg";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "CommunityDashboard",
  mixins: [Authenticated, DataRouteGuards, UserMixin],
  components: {
    BoroughDifferenceModal,
    "svg-arrow": Arrow,
    UserCard,
  },
  computed: {
    borough() {
      return this.community.type === "borough" ? this.community : this.community.parent;
    },
    community() {
      return this.$store.state.communities.item || {};
    },
    neighborhoods() {
      return this.community.type === "borough"
        ? this.community.children
        : this.community.parent.children;
    },
    approvedUsersCount() {
      return (
        this.borough.approved_users_count +
        this.neighborhoods.reduce((acc, c) => acc + c.approved_users_count, 0)
      );
    },
  },
  methods: {
    async reload() {
      this.reloading = true;
      await this.loadDataRoutesData(this, this.$route);
      this.reloading = false;
    },
    slideUsers(increment) {
      const { scrollLeft } = this.$refs.users;
      this.$refs.users.scroll({
        top: 0,
        left: scrollLeft + increment,
        behavior: "smooth",
      });
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

  .container .page__section:nth-child(1) {
    margin-top: 60px;
  }

  .page__section h2 {
    margin-bottom: 40px;
    text-align: center;
  }

  .community {
    &__organize {
      margin-bottom: 0;
    }

    &__neighbors {
      padding: 0;

      &.page__section {
        margin-bottom: 60px;
      }

      &:nth-child(2) {
        margin-top: -30px;
      }
    }

    &__users {
      max-width: 100vw;

      &__before,
      &__after {
        position: absolute;
        top: 0;
        height: 100%;
        width: 60px;
        background: $main-bg;
        z-index: 10;

        display: flex;
        flex-direction: column;
        justify-content: space-around;

        svg {
          width: 20px;
          cursor: pointer;
        }
      }

      &__before {
        left: 15px;
        background: linear-gradient(
          90deg,
          rgba(241, 241, 241, 1) 0%,
          rgba(241, 241, 241, 1) 75%,
          rgba(241, 241, 241, 0) 100%
        );

        svg {
          transform: rotate(180deg);
          margin: auto 30px auto auto;
        }
      }

      &__after {
        right: 15px;
        background: linear-gradient(
          270deg,
          rgba(241, 241, 241, 1) 0%,
          rgba(241, 241, 241, 1) 75%,
          rgba(241, 241, 241, 0) 100%
        );

        svg {
          margin: auto auto auto 30px;
        }
      }

      &__slider {
        &__spacer {
          width: 55px;
          height: 100%;
        }

        overflow-y: hidden;
        overflow-x: scroll;

        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        position: relative;

        height: 410px;

        .user-card {
          height: 184px;
          width: 450px;
          max-width: calc(100vw - 60px);
          margin: 0 30px 30px 0;

          &:nth-child(2n + 1) {
            margin-bottom: 0;
          }

          &:nth-child(1),
          &:nth-child(2) {
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
