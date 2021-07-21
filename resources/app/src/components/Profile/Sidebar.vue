<template>
  <div class="profile-sidebar">
    <ul v-if="user">
      <b-nav-item to="/profile/locomotion">
        {{ $t("profile.titles.profile") }}
      </b-nav-item>
      <b-nav-item to="/profile/account">
        {{ $t("profile.titles.account") }}
      </b-nav-item>
      <b-nav-item to="/profile/communities">
        {{ $t("profile.titles.communities") }}
      </b-nav-item>
      <b-nav-item to="/profile/borrower">
        {{ $t("profile.titles.borrower") }}
      </b-nav-item>
      <b-nav-item to="/profile/loans" :disabled="!user.borrower">
        {{ $t("profile.titles.loans") }}
      </b-nav-item>
      <b-nav-item to="/profile/loanables">
        {{ $t("profile.titles.loanables") }}
      </b-nav-item>
      <b-nav-item to="/profile/payment_methods" :disabled="!hasCompletedRegistration">
        {{ $t("profile.titles.payment_methods") }}
      </b-nav-item>
      <b-nav-item to="/profile/invoices" :disabled="!hasCompletedRegistration">
        {{ $t("profile.titles.invoices") }}
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
