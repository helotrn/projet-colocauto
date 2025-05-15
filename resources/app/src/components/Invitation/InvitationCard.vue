<template>
  <b-card no-body class="invitation-card">
    <b-row no-gutters class="invitation-card__content">
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
        </div>
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
  },
  components: {
    DefaultAvatar: Avatar,
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
      > div {
        padding: 5px 20px;
      }
      h3 {
        line-height: 1.2;
      }
    }
  }
}
</style>
