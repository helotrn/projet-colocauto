<template>
  <div class="register-box">
    <b-pagination-nav
      v-model="currentPage"
      :number-of-pages="4"
      pills
      align="center"
      use-router
      :hide-goto-end-buttons="true"
      :disabled="true"
    >
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <register-form v-if="currentPage == 1"/>
    <profile-form v-if="currentPage == 2"/>
  </div>
</template>

<script>

import RegisterForm from '@/components/Register/Form.vue';
import ProfileForm from '@/components/Account/ProfileForm.vue';

export default {
  name: 'registerBox',
  components: {
    RegisterForm,
    ProfileForm,
  },
  props: {
    currentPage: {
      type: Number,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.register-box {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .register-box__title {
    text-align: center;
  }

  h2 {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 16px;
  }
}
</style>
