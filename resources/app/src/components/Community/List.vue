<template>
  <div class="community-list">
    <b-row v-if="data.length > 0">
      <b-col v-for="loanable in data" :key="loanable.id" lg="4" md="6">
        <loanable-card v-bind="loanable"
          @test="emitTest(loanable)" @select="emitSelect(loanable)" class="mb-3" />
      </b-col>
    </b-row>

    <b-row v-else>
      <b-col>Aucun véhicule ne correspond à ces critères</b-col>
    </b-row>
  </div>
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
    emitTest(loanable) {
      this.$emit('test', loanable);
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.community-list {
  @include media-breakpoint-down(md) {
    margin-top: 45px;
  }
}
</style>
