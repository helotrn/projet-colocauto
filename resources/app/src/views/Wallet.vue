<template>
  <layout-page name="wallet">
    <ul class="nav justify-content-around">
      <li class="nav-item">
        <router-link :class="classes('/wallet/expenses')" to="/wallet/expenses">
          <icons-receipt /> Dépenses
        </router-link>
      </li>
      <li class="nav-item">
        <router-link :class="classes('/wallet/refunds')" to="/wallet/refunds">
          <icons-euro /> Remboursements
        </router-link>
      </li>
      <li class="nav-item">
        <router-link :class="classes('/wallet/balance')" to="/wallet/balance">
          <icons-balance /> Équilibre
        </router-link>
      </li>
    </ul>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";
import IconsReceipt from "@/assets/icons/receipt.svg";
import IconsEuro from "@/assets/icons/euro.svg";
import IconsBalance from "@/assets/icons/balance.svg";

const routeGuard = (to, from, next) => {
  if (to.name === "wallet") {
    next("/wallet/expenses");
  } else {
    next();
  }
};

export default {
  name: "Wallet",
  mixins: [Authenticated, UserMixin],
  components: {
    IconsReceipt,
    IconsEuro,
    IconsBalance,
  },
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
  beforeRouteLeave(to, from, next) {
    // Set the root store as not loaded to force a reload of the user
    this.$store.commit("loaded", false);
    next();
  },
  methods: {
    classes(path){
      return ['nav-link', path === this.$route.path ? 'active' : '']
    }
  },
};
</script>

<style lang="scss">
.wallet.page {
  .page__content {
    padding: 45px 30px;
  }
  .nav-link {
    padding: 0 0 1em;
    border-bottom: solid 4px transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
    color: black;
    svg {
      margin-bottom: .4em;
    }

    &.active {
      color: $primary;
      border-color: $primary;
      svg {
        fill: $primary;
      }
    }
  }
  
}
</style>
