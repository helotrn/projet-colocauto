<template>
  <b-row>
    <b-col>
      <div class="wallet__balance mt-4" :class="{ loading: loading && !balanceLoaded }">
        <transition name="fade">
          <div v-if="balance && balance.users && balance.users.length > 0">
            <users-balance :users="balance.users"/>
          </div>
        </transition>
      </div>

      <section class="page__section" v-if="myRefunds.length">
        <h2>Vous devez</h2>
        <b-card v-for="refund in myRefunds"
          no-body bg="white"
          class="expense-info-box shadow my-4 pt-3"
          :class="{ disabled: loading }"
        >
            <div class="card-body">
              <b-row>
                <b-col>
                  <strong class="big">{{ refund.user_full_name }} (vous)</strong><br/>
                  <span class="small">doit à</span><br/>
                  <strong class="big">{{ refund.credited_user_full_name }}</strong>
                </b-col>
                <b-col class="text-right">
                  <strong class="huge">{{ refund.amount }} €</strong>
                </b-col>
              </b-row>
              <b-row>
                <b-col class="d-flex flex-column">
                  <b-btn :to="`/wallet/refunds/new?amount=${refund.amount}&user_id=${refund.user_id}&credited_user_id=${refund.credited_user_id}`" variant="outline-primary" class="mt-3">
                    Ajouter un remboursement
                  </b-btn>
                </b-col>
              </b-row>
            </div>
        </b-card>
      </section>

      <section class="page__section" v-if="otherRefunds.length">
        <h2>Ils doivent</h2>
        <b-card v-for="refund in otherRefunds"
          no-body bg="white"
          class="expense-info-box shadow my-4 pt-3"
          :class="{ disabled: loading }"
        >
            <div class="card-body">
              <b-row>
                <b-col>
                  <strong class="big">{{ refund.user_full_name }}</strong><br/>
                  <span class="small">doit à</span><br/>
                  <strong class="big">{{ refund.credited_user_full_name }}</strong>
                </b-col>
                <b-col class="text-right">
                  <strong class="huge">{{ refund.amount }} €</strong>
                </b-col>
              </b-row>
              <b-row>
                <b-col class="d-flex flex-column">
                  <b-btn :to="`/wallet/refunds/new?amount=${refund.amount}&user_id=${refund.user_id}&credited_user_id=${refund.credited_user_id}`" variant="outline-primary" class="mt-3">
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
    refunds() {
      return this.$store.state.dashboard.balance?.refunds ?? [];
    },
    myRefunds() {
      return this.refunds.filter(t => t.user_id == this.user.id);
    },
    otherRefunds() {
      return this.refunds.filter(t => t.user_id != this.user.id);
    }
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
