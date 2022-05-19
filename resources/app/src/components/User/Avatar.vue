<template>
  <div class="user-avatar">
    <img class="user-avatar__image" v-if="user.avatar" :src="user.avatar.sizes.thumbnail" />
    <div class="user-avatar__initials" v-else-if="userInitials">
      {{ userInitials }}
    </div>
    <div class="user-avatar__icon" v-else>
      <svg-profile />
    </div>
  </div>
</template>

<script>
import Profile from "@/assets/svg/profile.svg";

export default {
  name: "UserAvatar",
  components: {
    "svg-profile": Profile,
  },
  props: {
    user: {
      type: Object,
    },
  },
  computed: {
    userInitials() {
      if (typeof this.user.name === "string" && this.user.name.length > 0) {
        return `${this.user.name[0]}${this.user?.last_name.slice(0, 1)}`.toUpperCase();
      } else if (typeof this.user.email === "string" && this.user.email.length > 0) {
        return this.user?.email[0].toUpperCase();
      }
    },
  },
};
</script>

<style lang="scss">
.user-avatar {
  position: relative;

  // Default size.
  width: 3rem;
  height: 3rem;

  font-weight: 700;
  color: $white;

  &__image,
  &__initials,
  &__icon {
    position: absolute;
    top: 0;
    left: 0;

    height: 100%;
    width: 100%;

    // That makes a rounded div no matter the size.
    border-radius: 99999px;
  }

  &__initials,
  &__icon {
    // Don't add to image as it will create an inelegant rim.
    background-color: $locomotion-green;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__image {
    object-fit: cover;
    object-position: top;
  }
}
</style>
