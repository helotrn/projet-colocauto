<template>
  <b-card class="invitation-card">
    <template v-if="forMe" no-gutters>
      <h3 class="text-center mb-4"><strong>{{invitation.community.name}}</strong></h3>
      <b-button
        variant="outline-primary"
        class="w-full mb-2"
        @click="acceptInvitation"
      >Rejoindre la communauté</b-button>
      <b-button
        variant="outline-primary"
        class="w-full"
        @click="deactivateInvitation"
      >Refuser l'invitation</b-button>
    </template>
    <b-row v-else no-gutters class="invitation-card__content">
      <b-col class="user-card__content__avatar">
        <default-avatar class="default-avatar" />
      </b-col>
      <b-col class="invitation-card__content__details">
        <div class="p-3">
          <small class="d-block mb-2">
            <b-badge v-if="!invitation.consumed_at" variant="warning">
              invitation en attente
            </b-badge>
          </small>
          <a :href="`mailto:${invitation.email}`" class="d-block">{{ invitation.email }}</a>
          <b-btn variant="outline-primary" size="sm" class="mt-4" @click="resendInvitation">Renvoyer l'invitation</b-btn>
        </div>
        <b-icon
          icon="trash"
          class="trash"
          @click="deactivateInvitation"
          title="Désactiver l'invitation"
        ></b-icon>
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
import Avatar  from "@/assets/svg/avatar.svg";
export default {
  name: "InvitationCard",
  props: {
    invitation: {
      type: Object,
      required: true,
    },
    forMe: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    DefaultAvatar: Avatar,
  },
  methods: {
    async resendInvitation(){
       await this.$store.dispatch("invitations/resend", this.invitation.id)
       this.$store.commit("addNotification", {
          content: `L'invitation pour ${this.invitation.email} a été envoyée de nouveau.`,
          title: "Invitation renvoyée !",
          variant: "success",
          type: "community",
        })
    },
    async deactivateInvitation(){
       await this.$store.dispatch("invitations/destroy", this.invitation.id)
       this.$store.commit("addNotification", {
          content: `L'invitation pour ${this.invitation.email} a été désactivée.`,
          title: "Invitation désactivée !",
          variant: "success",
          type: "community",
        })
        this.$emit("updated");
    },
    async acceptInvitation(){
      await this.$store.dispatch("invitations/accept", this.invitation.token)
      this.$store.commit("addNotification", {
        content: `L'invitation pour ${this.invitation.community.name} a été acceptée.`,
        title: "Invitation acceptée !",
        variant: "success",
        type: "community",
      })
      this.$emit("updated");
    },
  },
};
</script>

<style lang="scss">
.invitation-card {
  margin-bottom: 10px;
  position: relative;

  &__content {
    &__avatar.col {
      border-radius: 15px 0 15px;
      flex: 0 1 103px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      width: 103px;
      height: 103px;
      .default-avatar {
        fill: $danger;
        background: #ffe6e5; // FIXME use a variable here
        border-radius: 15px 0 15px;
        width: 103px;
        height: 103px;
      }
    }

    &__details {
      position: relative;
      > div {
        padding: 5px 20px;
      }
      h3 {
        line-height: 1.2;
      }
      .trash.b-icon{
        position: absolute;
        top: 1em;
        right: 1em;
        cursor: pointer;
        opacity: 0.75;
        &:hover {
          opacity: 1;
        }
      }
    }
  }

  
}
</style>
