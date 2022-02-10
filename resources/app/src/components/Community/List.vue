<template>
  <div class="community-list">
    <div v-if="data.length > 0">
      <b-row no-gutters>
        <b-col v-for="loanable in data" class="community-list--mobile" :key="loanable.id" lg="3">
          <loanable-card
            v-bind="loanable"
            @test="emitTest(loanable)"
            @select="emitSelect(loanable)"
            class="mb-3"
          />
        </b-col>
      </b-row>
      <b-row no-gutters>
        <b-col class="community-list--margin">
          <b-pagination
            align="right"
            :value="page"
            :total-rows="total"
            :per-page="perPage"
            @input="emitPage"
          />
        </b-col>
      </b-row>
    </div>

    <b-row v-else>
      <b-col class="community-list__no-results">
        <b-card>
          <b-card-body>
            <h3>Désolé, aucun véhicule ne correspond à ces critères.</h3>
            <p class="community-list--dark">
              Essayez d'autres critères ou invitez vos voisins à rejoindre LocoMotion ;)
            </p>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import LoanableCard from "@/components/Loanable/Card.vue";

export default {
  name: "List",
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
      this.$emit("page", page);
    },
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
  &__no-results h3 {
    font-weight: 700;
  }
}

.community-list--mobile {
  @include media-breakpoint-down(md) {
    margin: 0 15px;
  }
}

.community-list--margin {
  margin: 15px;
}

.community-list--dark {
  color: $dark;
}
</style>
