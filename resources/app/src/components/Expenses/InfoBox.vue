<template>
  <b-card no-body class="expense-info-box shadow my-2" bg="white" no-body :class="{ disabled: loading }">
    <router-link :to="`/wallet/expenses/${id}`">
      <div class="card-header">
          <b-row><span class="badge badge-primary">Emprunt</span></b-row>
          <b-row><small v-if="executed_at">{{ new Date(executed_at).toLocaleDateString() }}</small></b-row>
      </div>
      <div class="card-body">
        <b-row>
          <b-col>
            {{ name }}<br/>
            <small>par {{ user.full_name }}</small>
          </b-col>
          <strong>{{ amount }} €</strong>
        </b-row>
        <b-row><b-col>{{ loanable.name }}</b-col></b-row>
      </div>
    </router-link>
    <div class="card-footer">
      <span class="badge badge-warning">2 modifications effectuées</span>
    </div>
  </b-card>
</template>

<script>
import { extractErrors } from "@/helpers";

export default {
  name: "ExpenseInfoBox",
  props: {
    id: {
      type: Number,
      required: true,
    },
    name: {
      type: String,
      required: true,
    },
    amount: {
      type: Number,
      required: true,
    },
    executed_at: {
      type: String,
      required: false,
    },
    user: {
      type: Object,
      required: true,
    },
    loanable: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
}
</script>

<style lang="scss">
.expense-info-box {
  a,
  a:hover,
  a:active,
  a:focus {
    text-decoration: none;
    color: black;
  }
}
</style>
