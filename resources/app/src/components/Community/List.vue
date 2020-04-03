<template>
  <b-container class="community-list">
    <b-row v-if="data.length > 0">
      <b-col v-for="loanable in data" :key="loanable.id" lg="4">
        <loanable-card v-bind="loanable" @select="emitSelect" class="mb-3" />
      </b-col>
    </b-row>

    <b-row v-else>
      <b-col>Aucun véhicule ne correspond à ces critères</b-col>
    </b-row>
  </b-container>
</template>

<script>
import LoanableCard from '@/components/Loanable/Card.vue';

export default {
  name: 'List',
  components: {
    LoanableCard,
  },
  props: {
    data: {
      type: Array,
      required: true,
    },
  },
  methods: {
    emitSelect(loanable) {
      this.$emit('select', loanable);
    },
  },
};
</script>

<style lang="scss">
</style>
