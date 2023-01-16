<template>
  <b-row>
    <b-col>
      <div class="wallet__balance mt-4" :class="{ loading: loading && !balanceLoaded }">
        <transition name="fade">
          <div v-if="balance && balance.length > 0">
            <users-balance :users="balance"/>
          </div>
        </transition>
      </div>

      <section class="page__section">
        <h2>Vous devez</h2>
        <b-card no-body class="expense-info-box shadow my-2 pt-3" bg="white" no-body :class="{ disabled: loading }">
            <div class="card-body">
              <b-row>
                <b-col>
                  <strong class="big">Romy Rivière (vous)</strong><br/>
                  <span class="small">doit à</span><br/>
                  <strong class="big">Jean-Lalong</strong>
                </b-col>
                <b-col class="text-right">
                  <strong class="huge">158 €</strong>
                </b-col>
              </b-row>
              <b-row>
                <b-col class="d-flex flex-column">
                  <b-btn to="/wallet/refunds/new?amount=158" variant="outline-primary" class="mt-3">
                    Ajouter un remboursement
                  </b-btn>
                </b-col>
              </b-row>
            </div>
        </b-card>
      </section>
    </b-col>
  </b-row>
</template>

<script>
import locales from "@/locales";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";
import UsersBalance from "@/components/Balance/UsersBalance.vue";

export default {
  name: "Balance",
  mixins: [Authenticated, UserMixin],
  components: {
    UsersBalance,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin();
    }

    if (this.isGlobalAdmin) {
      this.$router.replace("/admin");
    }

    if (!this.hasCompletedRegistration) {
      // Skip to 2 here since we already have an email (logged in)
      this.$router.replace("/register/2");
    }
  },
  mounted() {
    this.$store.dispatch("dashboard/reload", this.user);
  },
  computed: {
    balance() {
      return this.$store.state.dashboard.balance ?? [];
    },
    balanceLoaded() {
      return this.$store.state.wallet.balanceLoaded;
    },
    loading() {
      return this.$store.state.wallet.loadRequests > 0;
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.views.wallet,
      },
      en: {
        ...locales.en.views.wallet,
      },
    },
  },
};
</script>
