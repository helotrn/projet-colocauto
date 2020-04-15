<template>
  <v-select class="forms-relation-input" label="text" :options="data"
    :placeholder="placeholder" @search="setQ" :loading="loading"
    :filterable="false"
    :value="convertedObjectValue" @input="emitInput">
    <template #no-options>
      <span v-if="!q || q.length < 3">Tapez quelque chose pour commencer à chercher...</span>
      <span v-else-if="searchDebounce">Chargement...</span>
      <span v-else>Pas de résultat</span>
    </template>
  </v-select>
</template>

<script>
import vSelect from 'vue-select';

export default {
  name: 'RelationInput',
  components: { vSelect },
  props: {
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    name: {
      type: String,
      required: true,
    },
    placeholder: {
      type: String,
      required: false,
      default: null,
    },
    query: {
      type: Object,
      required: true,
    },
    state: {
      type: Boolean,
      required: false,
      default: null,
    },
    objectValue: {
      type: Object,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      searchDebounce: null,
      q: '',
    };
  },
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    convertedObjectValue() {
      if (!this.objectValue) {
        return null;
      }

      const value = this.dig(this.objectValue, this.query.value);
      const text = this.dig(this.objectValue, this.query.text);

      return {
        value,
        text,
      };
    },
    data() {
      return this.context.search.map((d) => {
        const value = this.dig(d, this.query.value);
        const text = this.dig(d, this.query.text);

        return {
          ...d,
          value,
          text,
        };
      });
    },
    loading() {
      return !!this.context.searchAjax;
    },
    params() {
      return this.query.params;
    },
    slug() {
      return this.query.slug;
    },
  },
  methods: {
    dig(target, key) {
      const parts = key.split('.');

      return parts.reduce((acc, k) => {
        if (!acc) {
          return acc;
        }

        return acc[k];
      }, target);
    },
    emitInput(value) {
      this.$emit('input', value);
    },
    setQ(q) {
      this.q = q;
    },
  },
  watch: {
    q(q) {
      if (!q || q.length < 3) {
        this.$store.commit(`${this.slug}/search`, []);
      }

      if (q && q.length >= 3) {
        if (this.searchAjax) {
          this.searchAjax.abort();
        }

        if (this.searchDebounce) {
          clearTimeout(this.searchDebounce);
        }

        this.searchDebounce = setTimeout(() => {
          this.searchDebounce = null;
          this.$store.dispatch(`${this.slug}/search`, {
            q,
            params: this.params,
          });
        }, 250);
      }
    },
  },
};
</script>

<style lang="scss">
.forms-relation-input {
  .vs__dropdown-toggle {
    background-color: $white;
  }
}
</style>
