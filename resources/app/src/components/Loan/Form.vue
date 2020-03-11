<template>
  <b-card class="loan-form">
    <h2>
      <svg-check v-if="loan.id" />
      <svg-waiting v-else />
      Demande d'emprunt
    </h2>
  </b-card>
</template>

<script>
import Check from '@/assets/svg/check.svg';
import Waiting from '@/assets/svg/waiting.svg';

export default {
  name: 'LoanForm',
  props: {
    loan: {
      required: true,
      type: Object,
    },
  },
  components: {
    'svg-check': Check,
    'svg-waiting': Waiting,
  },
};
</script>

<style lang="scss">
</style>
