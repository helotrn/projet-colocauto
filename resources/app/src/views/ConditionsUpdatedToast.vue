<template>
  <b-toast
    toast-class="conditions-toast"
    variant="warning"
    visible
    v-if="showToast"
    no-auto-hide
    no-close-button
    solid
    :title="$t('conditions_updated_title')"
  >
    <p>{{ $t("conditions_updated_text") }}</p>

    <dl>
      <template v-for="conditions in unacceptedConditionsUpdates">
        <dt>{{ conditions | date }}</dt>
        <dd>{{ $t(`updates.${conditions}`) }}</dd>
      </template>
    </dl>

    <i18n path="read_all_conditions" tag="p">
      <a href="https://www.colocauto.org/conditions" target="_blank">{{ $t("conditions") }}</a>
    </i18n>

    <div class="d-flex justify-content-between">
      <div>
        <b-button @click="acceptConditions" :disabled="loading" variant="success" size="sm">{{
          $t("accept_updates_button")
        }}</b-button>
      </div>
      <a v-b-toggle.unregister class="text-muted align-self-end" href="#">{{
        $t("unregister_link")
      }}</a>
    </div>
    <b-collapse id="unregister">
      <i18n path="unregister_instructions" tag="div" class="mt-2">
        <a href="mailto:soutien@colocauto.org">soutien@colocauto.org</a>
      </i18n>
    </b-collapse>
  </b-toast>
</template>

<script>
import conditions from "@/locales/views/Conditions";
export default {
  name: "ConditionsUpdatedToast",
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    currentAcceptationDate() {
      return this.$dayjs(this.$store.state.user.conditions_accepted_at);
    },
    showToast() {
      // Avoid showing the toast on the conditions page, which would make it unreadable on mobile.
      if (this.$route.name === "conditions") {
        return false;
      }

      if (!this.$store.state.loaded || !this.$store.state.user || !this.mostRecentUpdate) {
        return false;
      }

      return this.currentAcceptationDate.isBefore(this.mostRecentUpdate);
    },
    mostRecentUpdate() {
      return this.pastConditionsUpdates.at(-1);
    },
    unacceptedConditionsUpdates() {
      return this.pastConditionsUpdates.filter((d) => this.currentAcceptationDate.isBefore(d));
    },
    pastConditionsUpdates() {
      const today = this.$dayjs();
      return Object.keys(conditions.fr.updates)
        .sort()
        .filter((d) => today.isAfter(d));
    },
  },

  methods: {
    async acceptConditions() {
      this.loading = true;
      try {
        await this.axios.post("/acceptConditions");
        await this.$store.dispatch("loadUser");
        this.$store.commit("addNotification", {
          title: "Succès",
          content: "Conditions d'utilisations acceptées!",
          variant: "success",
        });
      } catch (e) {
        this.$store.commit("addNotification", {
          title: "Erreur lors de l'approbation des conditions d'utilisation!",
          content: "Veuillez contacter info@locomotion.app pour corriger cette erreur.",
          variant: "danger",
        });
      } finally {
        this.loading = false;
      }
    },
  },

  i18n: {
    messages: {
      fr: {
        ...conditions.fr,
      },
      en: {
        ...conditions.en,
      },
    },
  },
};
</script>

<style scoped lang="scss">
.conditions-toast {
  max-height: calc(100vh - 1rem);
  overflow: auto !important;
}
</style>
