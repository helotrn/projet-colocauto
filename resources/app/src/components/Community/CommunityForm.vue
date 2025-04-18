<template>
  <b-form v-if="community" class="form text-end" @submit.prevent="submit">
    <forms-builder :definition="formName" v-model="community" entity="communities" class="text-start" />
    <slot />
    <b-button
      variant="primary"
      type="submit"
      :disabled="!changed || loading"
    >
      {{ $route.name.match('register-') ? 'Suivant' : 'Enregistrer' }}
    </b-button>
  </b-form>
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";

export default {
  name: "CommunityForm",
  components: {FormsBuilder},

  props: {
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    form: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    community: {
      type: Object,
      required: true,
    },
  },
  computed: {
    formName() {
      return { name: this.form.name }
    },
  },
  methods: {
    submit(...params) {
      this.$emit("submit", ...params);
    },
  },
}
</script>
