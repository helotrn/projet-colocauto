<template>
  <b-container class="profile-loanable" fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $t("nouveau") | capitalize }} {{ $tc("véhicule", 1) }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <loanable-form
          :loanable="item"
          :form="form"
          :loading="loading"
          @submit="submitLoanable"
          :show-reset="!!item.id"
          :changed="changed"
          @reset="reset"
          :center="center"
        />
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import LoanableForm from "@/components/Loanable/LoanableForm.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";
import UserMixin from "@/mixins/UserMixin";

import locales from "@/locales";

import { capitalize } from "@/helpers/filters";

export default {
  name: "ProfileLoanable",
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: {
    LoanableForm,
  },
  computed: {
    communityCenter() {
      if (!this.item.community) {
        return null;
      }

      return this.item.community.center;
    },
    center() {
      return this.communityCenter || this.defaultCenter;
    },
    fullTitle() {
      const parts = [
        "LocoMotion",
        capitalize(this.$i18n.t("titles.profile")),
        capitalize(this.$i18n.tc("véhicule", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    defaultCenter() {
      return { lat: 45.53748, lng: -73.60145 };
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("véhicule", 1));
    },
  },
  methods: {
    async submitLoanable() {
      const isNew = !this.item.id;

      await this.submit();

      if (isNew) {
        this.$store.commit("addNotification", {
          content:
            "Votre véhicule a bien été créé. Veuillez maintenant ajouter des disponibilités dans la section du bas.",
          title: "Véhicule créé",
          variant: "success",
          type: "loanable",
        });
      } else {
        this.$store.commit("addNotification", {
          content: "Le véhicule a été enregistré avec succès.",
          title: "Véhicule enregistré",
          variant: "success",
          type: "loanable",
        });
      }
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
        profile: { ...locales.en.profile },
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
        profile: { ...locales.fr.profile },
        titles: { ...locales.fr.titles },
      },
    },
  },
};
</script>

<style lang="scss"></style>
