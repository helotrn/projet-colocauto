<template>
  <layout-page name="register" wide bg-color="green" bg-image padded>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

const routeGuard = (to, from, next) => {

  return next((vm) => {
    const goToStep = step => {
      if (vm.$route.path !== `/register/${step}`) {
        return vm.$router.replace(`/register/${step}`);
      } else {
        return null;
      }
    }

    // go to the appropriate registration step
    if (!vm.isLoggedIn) {
      return goToStep(1)
    }

    if (!vm.isRegistered) {
      return goToStep(2)
    }

    if( !vm.hasCommunity ) {
      return goToStep(4)
    }

    if( !vm.canLoanVehicle ){
      return goToStep(5)
    }
    vm.skipToApp();

    return null;
  });
};

export default {
  name: "Register",
  mixins: [Authenticated, UserMixin],
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
};
</script>

<style lang="scss"></style>
