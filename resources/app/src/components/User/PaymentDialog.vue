<template>
  <user-add-credit-box
    v-if="user.balance < price"
    :payment-methods="user.payment_methods"
    :minimum-required="price - user.balance"
    :trip-cost="price"
    :no-cancel="true"
    :label="addCreditLabel"
    :disabled="loading"
    @bought="onBought"
  >
  </user-add-credit-box>
  <div v-else class="text-center">
    <b-button @click="$emit('complete')" variant="success" :disabled="loading">
      {{ actionName | capitalize }}
    </b-button>
  </div>
</template>
<script>
import UserAddCreditBox from "@/components/User/AddCreditBox.vue";

export default {
  name: "PaymentDialog",
  components: {
    UserAddCreditBox,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
    price: {
      type: Number,
      required: true,
    },
    actionName: {
      type: String,
    },
    loading: {
      type: Boolean,
    },
  },
  computed: {
    addCreditLabel() {
      if (this.actionName) {
        return "Ajouter au solde et " + this.actionName;
      }
      return "Ajouter au solde";
    },
  },
  methods: {
    onBought() {
      this.$store.dispatch("loadUser");
      this.$emit("complete");
    },
  },
};
</script>
