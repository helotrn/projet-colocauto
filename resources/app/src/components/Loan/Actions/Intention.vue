<template>
  <b-card clas="loan-actions-intention loan-actions">
    <h2>Faire une demande d'emprunt</h2>
  </b-card>
</template>

<script>
export default {
  name: 'LoanActionsIntention',
  props: {
    action: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style lang="scss">
</style>
