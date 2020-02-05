<template>
  <div :class="`page ${name}`">
    <vue-headful :title="fullTitle" />

    <layout-header class="page__header" :title="pageTitle" />

    <b-container :fluid="fluid" tag="main" class="page__content" v-if="!wide">
      <slot />
    </b-container>
    <main class="page__content" v-else>
      <slot />
    </main>

    <layout-footer class="page__footer" />
  </div>
</template>

<script>
import filters from '@/helpers/filters';

const { capitalize } = filters;

export default {
  name: 'LayoutPage',
  props: {
    name: {
      type: String,
      required: true,
    },
    fluid: {
      type: Boolean,
      required: false,
      default: false,
    },
    title: {
      type: String,
      require: false,
      default: '',
    },
    wide: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    fullTitle() {
      if (this.treeTitle) {
        return `${this.treeTitle} | LocoMotion`;
      }

      return 'LocoMotion';
    },
    treeTitle() {
      return this.$route.matched.reduce((acc, r) => {
        if (r.meta && r.meta.title) {
          acc.push(capitalize(this.$i18n.t(r.meta.title)));
        }
        return acc;
      }, []).reverse().join(' | ');
    },
    pageTitle() {
      if (this.title) {
        return capitalize(this.title);
      }

      if (this.$route.meta && this.$route.meta.title) {
        return capitalize(this.$i18n.t(this.$route.meta.title));
      }

      return '';
    },
  },
};
</script>

<style lang="scss">
</style>
