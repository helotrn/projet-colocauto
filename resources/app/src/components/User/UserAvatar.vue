<template>
  <div :style="style" class="user-avatar" >
    <span class="initials-wrapper" v-if="userAvatarImage==null">
      {{ userInitials }}
    </span>
  </div>
</template>

<script>
export default {
  name: "UserAvatar",
  props: {
    user: {
      type: Object,
      required: true,
    },
    size: {
      type: Number,
      default: 40,
    },
  },
  computed: {
    userAvatarImage() {
      if (this.user.avatar) {
        return {
          "background-image": `url('${this.user.avatar.sizes.thumbnail}')`,
          "background-color": null,
          "background-size": "cover",
        };
      } else {
        return null
      }
    },
    userInitials() {
      if (typeof this.user.name === "string" && this.user.name.length > 0) {
        return `${this.user.name[0] + this.user.last_name[0]}`.toUpperCase();
      } else {
        return null
      }
    },
    style() {
      const styles = {
        width: this.size + "px",
        height: this.size + "px",
        "font-size": this.size / 2 + "px",
      }
      return Object.assign(styles, this.userAvatarImage)
    },
  },
};
</script>

<style lang="scss">
.user-avatar {
  background-color: $locomotion-green;
  background-size: cover;
  border-radius: 50%;

  .initials-wrapper {
    width: 100%;
    height: 100%;
    font-weight: 700;
    color: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

</style>
