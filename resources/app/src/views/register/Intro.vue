<template>
  <div class="register-intro">
    <b-pagination-nav v-bind:value="currentPage"
      :number-of-pages="4" pills
      align="center" use-router
      :hide-goto-end-buttons="true"
      :disabled="true">
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <register-form v-if="currentPage == 1" />
  </div>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import UserMixin from '@/mixins/UserMixin';

import RegisterForm from '@/components/Register/RegisterForm.vue';

export default {
  name: 'RegisterIntro',
  mixins: [Authenticated, UserMixin],
  components: {
    RegisterForm,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        if (!vm.isRegistered) {
          if (vm.$route.path !== '/register/2') {
            return vm.$router.replace('/register/2');
          }

          return null;
        }

        if (vm.user.communities.length === 0) {
          if (vm.$route.path !== '/register/map') {
            return vm.$router.replace('/register/map');
          }

          return null;
        }

        return vm.$router.replace('/register/3');
      }

      if (vm.$route.path !== '/register/1') {
        return vm.$router.replace('/register/1');
      }

      return null;
    });
  },
  data() {
    return {
      currentPage: 1,
    };
  },
};
</script>

<style lang="scss">
.register-intro {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;
}
</style>
