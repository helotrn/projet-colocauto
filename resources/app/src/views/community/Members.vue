<template>
    <b-container
      v-if="routeDataLoaded && !loading && loaded"
      fluid
    >
      <b-row no-gutters class="px-3 header" :style="{justifyContent: approvedUsersCount > 1 ? 'space-between' : 'end'}">
        <h2 v-if="approvedUsersCount > 1">{{ approvedUsersCount }} membres</h2>
        <b-btn
          v-if="canInviteMemberInCurrentCommunity"
          variant="outline-primary"
          :to="`/community/${currentCommunity}#email`"
        >Inviter un membre</b-btn>
      </b-row>
      <b-row no-gutters>
        <b-col>
          <div v-if="community.users">
            <b-container>
              <b-row class="community__users mt-4">
                  <b-col md="12" lg="6"
                    v-for="item in membersAndInvitations"
                    :key="`${item.type}-${item.id}`"
                  >
                    <user-card
                      v-if="item.type == 'user'"
                      :user="item"
                      detailed-view
                      :community-id="community.id"
                      @updated="loadCurrentCommunity"
                      @set-responsible="setResponsibleHandler"
                    />
                    <invitation-card
                      v-else
                      :invitation="item"
                      @updated="loadCurrentCommunity"
                    />
                  </b-col>
              </b-row>
            </b-container>
          </div>
        </b-col>
      </b-row>
      <b-modal
        v-model="showResponsibleConfirm"
        hide-header centered
        footer-class="justify-content-center mb-4"
        cancel-title="Annuler"
        cancel-variant="outline-primary"
        ok-title="Nommer référent"
        @ok="submitSetResponsible"
        @cancel="showResponsibleConfirm = false">
        <round-warning width="50px" class="mx-auto d-block my-4" />
        <template v-if="userToPromote">
          <p style="font-size:20px; text-align:center; font-weight: bold;">
            Êtes-vous sûr de vouloir nommer {{ userToPromote.full_name }} référent·e pour {{ community.name }} ?
          </p>
          <p style="text-align:center">
            Une fois référent, il aura les mêmes droits que vous sur cette communauté, et il ne sera plus possible de les lui enlever.
          </p>
        </template>
      </b-modal>
    </b-container>
    <layout-loading v-else />
</template>

<script>
import UserCard from "@/components/User/UserCard.vue";
import InvitationCard from "@/components/Invitation/InvitationCard.vue";

import Arrow from "@/assets/svg/arrow.svg";
import RoundWarning from "@/assets/icons/round-warning.svg";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "CommunityMembers",
  mixins: [Authenticated, DataRouteGuards, UserMixin],
  data: () => ({
    showResponsibleConfirm: false,
    userToPromote: null,
  }),
  components: {
    "svg-arrow": Arrow,
    UserCard,
    InvitationCard,
    RoundWarning,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if( vm.hasCommunity ){
        vm.loadCurrentCommunity();
      }
    });
  },
  computed: {
    // item is loaded manually in beforeRouteEnter
    skipLoadItem() {
      return true;
    },
    community() {
      return this.$store.state.communities.item || {};
    },
    approvedUsersCount() {
      return this.community.approved_users_count;
    },
    membersAndInvitations(){
      return [
        ...this.community.users.map(u => ({...u, type: 'user'})),
        ...this.community.invitations.filter(i => !i.consumed_at).map(i => ({...i, type: 'invitation'})),
      ]
    },
    loaded() {
      return this.$store.state.communities.loaded;
    },
    loading() {
      return !!this.$store.state.communities.cancelToken;
    },
  },
  methods: {
    slideUsers(increment) {
      const { scrollLeft } = this.$refs.users;
      this.$refs.users.scroll({
        top: 0,
        left: scrollLeft + increment,
        behavior: "smooth",
      });
    },
    async loadCurrentCommunity(){
      this.$store.dispatch(`communities/retrieveOne`, {
        id: this.currentCommunity,
        params: this.$route.meta.params,
      });
      this.$store.dispatch('wallet/loadBalance', {
        community: {id: this.currentCommunity}
      })
    },
    setResponsibleHandler(user){
      this.userToPromote = user
      this.showResponsibleConfirm = true
    },
    async submitSetResponsible() {
      console.log('do it : submitSetResponsible')
      await this.$store.dispatch('communities/promoteUser', {
        id: this.currentCommunity,
        userId: this.userToPromote.id,
      })
      this.showResponsibleConfirm = false
      this.userToPromote = null
    }
  },
  watch: {
    currentCommunity() {
      this.loadCurrentCommunity()
    }
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
      .header {
        position: relative;
        min-height: 3.7em;
        padding-bottom: 1em;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        .btn {
          position: absolute;
          right: 0;
        }
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
      background-image: url("/img-tetes.svg");
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
