<template>
  <div class="community-list">
    <div v-if="data.length > 0">
      <b-row>
        <b-col v-for="loanable in data" :key="loanable.id" lg="4" md="6">
          <loanable-card v-bind="loanable"
            @test="emitTest(loanable)" @select="emitSelect(loanable)" class="mb-3" />
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-pagination align="right" :value="page"
            :total-rows="total" :per-page="perPage"
            @input="emitPage" />
        </b-col>
      </b-row>
    </div>

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
    page: {
      type: Number,
      required: true,
    },
    perPage: {
      type: Number,
      required: true,
    },
    total: {
      type: Number,
      required: true,
    },
  },
  methods: {
    emitPage(page) {
      this.$emit('page', page);
    },
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
