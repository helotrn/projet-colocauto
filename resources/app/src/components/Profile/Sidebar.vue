<template>
  <div class="profile-sidebar">
    <ul v-if="user">
      <b-nav-item to="/profile">
        {{ $t("profile.titles.account") }}
      </b-nav-item>
      <b-nav-item to="/profile/loanables" v-if="user.owner">
        {{ $t("profile.titles.loanables") }}
      </b-nav-item>
    </ul>

    <hr />
  </div>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "ProfileSidebar",
  mixins: [UserMixin],
};
</script>

<style lang="scss">
.profile-sidebar {
  hr {
    margin: 30px 0;
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    .nav-item .nav-link {
      color: $black;
      padding-left: 0;

      &.disabled {
        color: $grey;
      }

      &.router-link-exact-active,
      &:hover {
        color: $locomotion-green;
      }
    }
  }
}
</style>
