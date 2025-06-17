<template>
  <b-card no-body class="user-card">
    <b-row no-gutters class="user-card__content">
      <b-col
        v-if="user.avatar"
        class="user-card__content__avatar"
        :style="`background-image: url('${user.avatar.sizes.thumbnail}')`"
      />
      <b-col v-else class="user-card__content__avatar">
        <default-avatar class="default-avatar" />
      </b-col>
      <b-col class="user-card__content__details">
        <b-badge v-if="user.role == 'responsible'" class="role">Référent</b-badge>
        <div class="p-3">
          <small class="uppercase">{{ user.owner && user.owner.id ? 'Propriétaire' : 'Membre' }}</small>

          <h3><strong>{{ user.full_name }}</strong></h3>

          <a v-if="user.phone" :href="`tel:${user.phone}`" class="d-block">{{ user.phone }}</a>
          <a v-if="user.email" :href="`mailto:${user.email}`" class="d-block">{{ user.email }}</a>
        </div>
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
import Avatar  from "@/assets/svg/avatar.svg";
export default {
  name: "UserCard",
  props: {
    communityId: {
      type: Number,
      required: false,
      default: null,
    },
    isAdmin: {
      type: Boolean,
      required: false,
      default: false,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  components: {
    DefaultAvatar: Avatar,
  },
  methods: {
    async setCommittee() {
      await this.$store.dispatch("communities/setCommittee", {
        communityId: this.communityId,
        tagId: this.committeeTag.id,
        userId: this.user.id,
      });
      this.$emit("updated");
    },
    async unsetCommittee() {
      await this.$store.dispatch("communities/unsetCommittee", {
        communityId: this.communityId,
        tagId: this.committeeTag.id,
        userId: this.user.id,
      });
      this.$emit("updated");
    },
  },
};
</script>

<style lang="scss">
.user-card {
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
      .role {
        position: absolute;
        top: .5em;
        right: 1em;
      }
    }
  }
}
</style>
