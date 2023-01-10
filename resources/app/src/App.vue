<template>
  <div id="locomotion">
    <aside v-if="environment" class="p-4" :class="environment">
      <p class="mb-0 text-center">👋 Vous consultez actuellement une page de {{ environment }}, pour vous rendre sur l'application Coloc'Auto c'est par ici : <a href="https://app.colocauto.org">app.colocauto.org</a></p>
    </aside>
    <router-view />
    <b-toast
      toast-class="terms-toast"
      variant="warning"
      visible
      v-if="showTermsChanged"
      no-auto-hide
      no-close-button
      solid
      :title="$t('terms_changed_title')"
    >
      <p>{{ $t("terms_changed_text") }}</p>

      <dl>
        <template v-for="terms in termsChanges">
          <dt>{{ terms | date }}</dt>
          <dd>{{ $t(`updates.${terms}`) }}</dd>
        </template>
      </dl>

      <i18n path="read_all_conditions" tag="p">
        <router-link to="/conditions">{{ $t("terms") }}</router-link>
      </i18n>

      <div class="d-flex justify-content-between">
        <div>
          <b-button @click="approveTerms" :disabled="loading" variant="success" size="sm">{{
            $t("approve_changes_button")
          }}</b-button>
        </div>
        <a v-b-toggle.unregister class="text-muted align-self-end" href="#">{{
          $t("unregister_link")
        }}</a>
      </div>
      <b-collapse id="unregister">
        <i18n path="unregister_instructions" tag="div" class="mt-2">
          <a href="mailto:info@locomotion.app">info@locomotion.app</a>
        </i18n>
      </b-collapse>
    </b-toast>
  </div>
</template>

<script>
import Notification from "@/mixins/Notification";
import terms from "@/locales/views/Terms";
import { date } from "./helpers/filters";

export default {
  name: "ColocAuto",
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    date,
    async approveTerms() {
      this.loading = true;
      await this.axios.post("/approveTerms");
      await this.$store.dispatch("loadUser");
      this.loading = false;
    },
  },
  mixins: [Notification],
  mounted() {
    if (this.$store.state.token && !this.$store.state.loading && !this.$store.state.loaded) {
      this.$store.dispatch("loadUser");
    }
  },
  computed: {
    environment(){
      const subdomain = location.host.split('.').shift();
      if( subdomain == 'test' || subdomain == 'dev' ) return subdomain;
      else if ( subdomain.match(/localhost/) ) return 'dev';
      return '';
    },
    approvalDate() {
      return this.$dayjs(this.$store.state.user.terms_approved_at);
    },
    showTermsChanged() {
      // Avoid showing the
      if (this.$route.name === "conditions") {
        return false;
      }

      if (!this.$store.state.loaded || !this.$store.state.user || !this.mostRecentUpdate) {
        return false;
      }

      return this.approvalDate.isBefore(this.mostRecentUpdate);
    },
    mostRecentUpdate() {
      return Object.keys(terms.fr.updates).sort().at(-1);
    },
    termsChanges() {
      return Object.keys(terms.fr.updates)
        .sort()
        .filter((d) => this.approvalDate.isBefore(d));
    },
  },

  i18n: {
    messages: {
      fr: {
        ...terms.fr,
      },
      en: {
        ...terms.en,
      },
    },
  },
};
</script>

<style lang="scss" scoped>
aside.dev {
  background: #ffccaa;
}
aside.test {
  background: #87aade;
}
aside a {
  color: #212529;
}
.terms-toast {
  max-height: calc(100vh - 16px);
  overflow: auto !important;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
