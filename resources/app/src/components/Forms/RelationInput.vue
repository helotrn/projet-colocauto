<template>
  <v-select
    class="forms-relation-input"
    label="text"
    :options="data"
    :placeholder="placeholder"
    :disabled="disabled"
    @search="setQ"
    :loading="loading"
    :filterable="false"
    :value="convertedObjectValue"
    @input="emitInput"
  >
    <template #no-options>
      <span v-if="!q || q.length < 3">Tapez quelque chose pour commencer à chercher...</span>
      <span v-else-if="searchDebounce">Chargement...</span>
      <span v-else>Pas de résultat</span>
    </template>
  </v-select>
</template>

<script>
import Vue from "vue";
import vSelect from "vue-select";

export default {
  name: "RelationInput",
  components: { vSelect },
  props: {
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    extraParams: {
      type: Object,
      required: false,
      default() {
        return {};
      },
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
    resetAfterSelect: {
      type: Boolean,
      required: false,
      default: false,
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
    value: {
      type: [String, Number],
      required: false,
      default: null,
    },
  },
  mounted() {
    if (this.value && !this.lastSelectedItem) {
      Vue.axios
        .get(`/${this.slug}/${this.value}`, {
          params: {
            ...this.params,
          },
        })
        .then(({ data }) => {
          const value = this.dig(data, this.query.value);
          const text = this.dig(data, this.query.text);

          this.lastSelectedItem = {
            value,
            text,
          };
        });
    } else {
      // make all data available at loading
      this.$store.dispatch(`${this.slug}/search`, {
        q: this.q,
        params: this.params,
      });
    }
  },
  data() {
    return {
      lastSelectedItem: null,
      searchDebounce: null,
      q: "",
    };
  },
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    convertedObjectValue() {
      if (!this.objectValue) {
        if (!this.lastSelectedItem) {
          return null;
        }

        return this.lastSelectedItem;
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
      return {
        ...this.query.params,
        ...this.extraParams,
      };
    },
    slug() {
      return this.query.slug;
    },
  },
  methods: {
    dig(target, key) {
      const parts = key.split(".");

      return parts.reduce((acc, k) => {
        if (!acc) {
          return acc;
        }

        return acc[k];
      }, target);
    },
    emitInput(value) {
      this.lastSelectedItem = value;
      this.$emit("input", value);

      if (this.resetAfterSelect) {
        this.reset();
      }
    },
    setQ(q) {
      this.q = q;
    },
    reset() {
      this.lastSelectedItem = null;
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

  &.vs--disabled {
    .vs__dropdown-toggle,
    .vs__search,
    .vs__open-indicator,
    .vs__clear {
      background-color: #e9ecef;
    }
  }
}
</style>
