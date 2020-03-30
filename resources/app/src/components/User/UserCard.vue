<template>
  <b-card no-body class="user-card">
    <b-row no-gutters class="user-card__content">
      <b-col class="user-card__content__avatar" :style="{ backgroundImage: userAvatarStyle }" />
      <b-col class="user-card__content__details">
        <div>
          <i>Membre Locomotion</i>

          <h3>{{ user.full_name }}</h3>

          <p>{{ user.description }}</p>

          <div v-if="isAdmin && communityId" class="user-card__admin-actions">
            <a href="#" @click.prevent="unsetAmbassador" v-if="isAmbassador">
              Désaffecter comme ambassadeur.rice
            </a>
            <a href="#" @click.prevent="setAmbassador" v-else>
              Affecter comme ambassadeur.rice
            </a>
          </div>
        </div>
      </b-col>
    </b-row>
    <div class="user-card__tags">
      <b-badge pill variant="warning" v-if="user.owner">P</b-badge>
      <b-badge pill variant="success" v-if="isAmbassador">A</b-badge>
    </div>
  </b-card>
</template>

<script>
export default {
  name: 'UserCard',
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
    ambassadorTag() {
      return this.$store.state.global.tags.find(t => t.slug === 'ambassador');
    },
    isAmbassador() {
      return this.user.tags.find(t => t.slug === 'ambassador')
    },
    userAvatarStyle() {
      if (!this.user.avatar) {
        return '';
      }

      return `url('${this.user.avatar.sizes.thumbnail}')`;
    },
  },
  methods: {
    async setAmbassador() {
      await this.$store.dispatch('communities/setAmbassador', {
        communityId: this.communityId,
        tagId: this.ambassadorTag.id,
        userId: this.user.id,
      });
      this.$emit('updated');
    },
    async unsetAmbassador() {
      await this.$store.dispatch('communities/unsetAmbassador', {
        communityId: this.communityId,
        tagId: this.ambassadorTag.id,
        userId: this.user.id,
      });
      this.$emit('updated');
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
  }

  > div {
    height: 100%;
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
