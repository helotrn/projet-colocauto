<template>
  <b-card no-body class="user-card">
    <b-row no-gutters class="user-card__content">
      <b-col class="user-card__content__avatar" :style="{ backgroundImage: userAvatarStyle }" />
      <b-col class="user-card__content__details">
        <div>
          <i>Membre Coloc'Auto</i>

          <h3>{{ user.full_name }}</h3>

          <p v-if="user.description" :title="user.description">{{ user.description }}</p>
          <a v-if="user.phone" :href="`tel:${user.phone}`">{{ user.phone }}</a>

          <div v-if="false && isAdmin && communityId" class="user-card__admin-actions">
            <a href="#" @click.prevent="unsetCommittee" v-if="isCommittee">
              Désaffecter du comité du voisinage
            </a>
            <a href="#" @click.prevent="setCommittee" v-else> Affecter au comité du voisinage </a>
          </div>
        </div>
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
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
  computed: {
    committeeTag() {
      return this.$store.state.global.tags.find((t) => t.slug === "committee");
    },
    isCommittee() {
      return this.user.tags.find((t) => t.slug === "committee");
    },
    userAvatarStyle() {
      if (!this.user.avatar) {
        return "";
      }

      return `url('${this.user.avatar.sizes.thumbnail}')`;
    },
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

  &__tags {
    position: absolute;
    top: -8px;
    left: -8px;

    .badge {
      margin-right: 5px;
    }
  }

  &__admin-actions {
    margin-top: 1rem;
  }

  &__content {
    &__avatar.col {
      border-radius: 15px 0 0 15px;
      flex: 0 1 103px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      width: 103px;
      height: 103px;
    }

    &__details {
      > div {
        padding: 5px 20px;
      }

      p {
        margin-bottom: 0;

        display: -webkit-box; /* stylelint-disable-line value-no-vendor-prefix */
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-line-clamp: 3; /* stylelint-disable-line property-no-vendor-prefix */
        -webkit-box-orient: vertical; /* stylelint-disable-line property-no-vendor-prefix */
      }

      i {
        text-transform: uppercase;
        margin-bottom: 20px;
        font-size: 12px;
        font-weight: 600;
      }

      h3 {
        font-size: 18px;
        font-weight: normal;
        margin-bottom: 20px;
      }

      a[href^="tel"] {
        font-size: 14px;
        text-decoration: underline;
        color: $black;
      }
    }
  }
}
</style>
