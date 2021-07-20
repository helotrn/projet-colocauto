<template>
  <b-card class="pricing-form">
    <template v-slot:header class="pricing-form__header">
      <strong>
        {{ $t(`pricings.types.${pricing.object_type || "génerique"}`) | capitalize }}
      </strong>
      <b-button variant="outline-danger" @click="$emit('remove', pricing)">X</b-button>
    </template>
    <b-form-group label="Nom" label-for="name">
      <b-form-input :value="pricing.name" @input="emitChange('name', $event)" />
    </b-form-group>

    <b-form-group label="Règle" label-for="rule">
      <b-form-textarea
        name="rule"
        id="rule"
        :value="pricing.rule"
        rows="6"
        @input="emitChange('rule', $event)"
      />
    </b-form-group>
  </b-card>
</template>

<script>
export default {
  name: "PricingForm",
  props: {
    pricing: {
      type: Object,
      required: true,
    },
  },
  methods: {
    emitChange(key, value) {
      this.$emit("change", {
        ...this.pricing,
        [key]: value,
      });
    },
  },
};
</script>

<style lang="scss">
.pricing-form {
  margin-bottom: 20px;
}
</style>
