<template>
  <layout-loading v-if="loading" />
  <div v-else class="community-list">
    <!-- loanable cards -->
    <div v-if="visibleLoanables.length > 0">
      <b-row>
        <b-col
          v-for="loanable in visibleLoanables"
          class="community-list--mobile"
          :key="loanable.id"
          xl="3"
          md="4"
          sm="6"
        >
          <loanable-card
            v-bind="loanable"
            @test="emitTest(loanable)"
            @select="emitSelect(loanable)"
            class="mb-3"
          />
        </b-col>
      </b-row>
      <b-row>
        <b-col class="community-list--margin">
          <b-pagination align="right" v-model="page" :total-rows="total" :per-page="perPage" />
        </b-col>
      </b-row>
    </div>
    <!---->
    <!-- container if no loanables -->
    <b-row v-else>
      <b-col class="community-list__no-results">
        <b-card>
          <b-card-body>
            <h3>Désolé, aucun véhicule ne correspond à ces critères.</h3>
            <p class="community-list--dark">
              Essayez d'autres critères ou invitez vos voisins à rejoindre votre communauté ;)
            </p>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
    <!---->
  </div>
</template>

<script>
import LoanableCard from "@/components/Loanable/Card.vue";

export default {
  name: "List",
  components: {
    LoanableCard,
  },
  data() {
    return {
      page: 1,
      perPage: 12,
    };
  },
  props: {
    data: {
      type: Array,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    total() {
      return this.data.length;
    },
    visibleLoanables() {
      return this.data.slice((this.page - 1) * this.perPage, this.page * this.perPage);
    },
  },
  methods: {
    emitSelect(loanable) {
      this.$emit("select", loanable);
    },
    emitTest(loanable) {
      this.$emit("test", loanable);
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.community-list {
  width: 100%;

  &__no-results h3 {
    font-weight: 700;
  }
}

.community-list--margin {
  margin: 15px;
}

.community-list--dark {
  color: $dark;
}
</style>
