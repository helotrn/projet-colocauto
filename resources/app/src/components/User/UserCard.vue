<template>
  <b-card no-body class="user-card">
    <b-row no-gutters class="user-card__content">
      <b-col class="user-card__content__avatar" :style="{ backgroundImage: userAvatarStyle }" />
      <b-col class="user-card__content__details">
        <div>
          <i>Membre LocoMotion</i>

          <h3>{{ user.full_name }}</h3>

          <p :title="user.description">{{ user.description }}</p>

          <div v-if="false && isAdmin && communityId" class="user-card__admin-actions">
            <a href="#" @click.prevent="unsetCommittee" v-if="isCommittee">
              Désaffecter du comité du voisinage
            </a>
            <a href="#" @click.prevent="setCommittee" v-else> Affecter au comité du voisinage </a>
          </div>
        </div>
      </b-col>
    </b-row>
    <div class="user-card__tags">
      <b-badge pill variant="warning" v-if="user.owner">P</b-badge>
      <b-badge pill variant="success" v-if="isCommittee">C</b-badge>
    </div>
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
  margin-bottom: 30px;
  height: calc(100% - 30px);
  position: relative;

  &__tags {
    position: absolute;
    top: -8px;
    left: -8px;

    .badge {
      margin-right: 5px;
    }
  }

  > div {
    height: 100%;
  }

  &__admin-actions {
    margin-top: 1rem;
  }

  &__content {
    &__avatar.col {
      border-radius: 15px 0 0 15px;
      flex: 0 1 140px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
    }

    &__details {
      > div {
        padding: 30px;
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
        font-size: 22px;
        margin-bottom: 20px;
      }
    }
  }
}
</style>
