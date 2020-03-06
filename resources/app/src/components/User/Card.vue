<template>
  <b-card no-body class="user-card">
    <div class="user-card__tags">
      <b-badge pill variant="warning" v-if="user.owner">P</b-badge>
    </div>
    <b-row no-gutters>
      <b-col class="user-card__avatar" :style="{ backgroundImage: userAvatarStyle }" />
      <b-col class="user-card__details">
        <div>
          <i>Membre Locomotion</i>

          <h3>{{ user.full_name }}</h3>

          <p>{{ user.description }}</p>
        </div>
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
export default {
  name: 'UserCard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    userAvatarStyle() {
      if (!this.user.avatar) {
        return '';
      }

      return `url('${this.user.avatar.sizes.thumbnail}')`;
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

  &__avatar.col {
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
</style>
