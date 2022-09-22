<template>
  <div class="loanable-details-box">
    <h1>{{ loanable.name }}</h1>

    <b-row>
      <b-col lg="6">
        <div class="loanable-details-box__section" v-if="loanable.comments">
          <h3>{{ $t("fields.comments") | capitalize }}</h3>

          <p>{{ loanable.comments }}</p>
        </div>

        <div v-if="showInstructions && loanable.instructions" class="loanable-details-box__section">
          <h3>{{ $t("fields.instructions") | capitalize }}</h3>

          <p>{{ loanable.instructions }}</p>
        </div>

        <div v-if="loanable.type === 'bike'">
          <h3>Détails du vélo</h3>

          <p>
            <strong>{{ $t("fields.model") | capitalize }}</strong
            >:
            {{ loanable.model }}
          </p>

          <p>
            <strong>{{ $t("fields.bike_type") | capitalize }}</strong
            >:
            {{ $t(`bike_types.${loanable.bike_type}`) | capitalize }}
          </p>

          <p>
            <strong>{{ $t("fields.size") | capitalize }}</strong
            >:
            {{ $t(`sizes.${loanable.size}`) | capitalize }}
          </p>
        </div>
        <div v-else-if="loanable.type === 'trailer'">
          <h3>Détails de la remorque</h3>

          <p>
            <strong>{{ $t("fields.maximum_charge") | capitalize }}</strong
            >:
            {{ loanable.maximum_charge }}
          </p>
        </div>
        <div v-else-if="loanable.type === 'car'">
          <h3>Détails de la voiture</h3>

          <p>
            <strong>{{ $t("fields.brand") | capitalize }}</strong
            >:
            {{ loanable.brand }}
          </p>

          <p>
            <strong>{{ $t("fields.model") | capitalize }}</strong
            >:
            {{ loanable.model }}
          </p>

          <p>
            <strong>{{ $t("fields.year_of_circulation") | capitalize }}</strong
            >:
            {{ loanable.year_of_circulation }}
          </p>

          <p>
            <strong>{{ $t("fields.transmission_mode") | capitalize }}</strong
            >:
            {{ $t(`transmission_modes.${loanable.transmission_mode}`) | capitalize }}
          </p>

          <p>
            <strong>{{ $t("fields.engine") | capitalize }}</strong
            >:
            {{ $t(`engines.${loanable.engine}`) | capitalize }}
          </p>

          <p>
            <strong>{{ $t("fields.papers_location") | capitalize }}</strong
            >:
            {{ $t(`papers_locations.${loanable.papers_location}`) | capitalize }}
          </p>
        </div>
      </b-col>

      <b-col lg="6">
        <forms-map-input :value="loanable.position" disabled bounded />

        <div class="mt-3 loanable-details-box__section" v-if="loanable.location_description">
          <h3>{{ $t("fields.location_description") | capitalize }}</h3>

          <p>{{ loanable.location_description }}</p>
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import FormsMapInput from "@/components/Forms/MapInput.vue";

import locales from "@/locales";

export default {
  name: "LoanableDetailsBox",
  components: { FormsMapInput },
  props: {
    loanable: {
      type: Object,
      required: true,
    },
    showInstructions: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
      },
      fr: {
        ...locales.fr.loanables,
      },
    },
  },
};
</script>

<style lang="scss">
.loanable-details-box {
  // Show line feeds in comments, instructions and location_description
  white-space: pre;
}
</style>
