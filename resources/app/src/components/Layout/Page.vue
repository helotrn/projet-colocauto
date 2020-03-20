<template>
  <div :class="`page ${name}`">
    <vue-headful :title="fullTitle" />

    <layout-header class="page__header" :title="pageTitle" />

    <div class="page__background">
      <b-container :fluid="fluid" tag="main" :class="mainClass" v-if="!wide">
        <layout-loading v-if="loading" />
        <slot v-else />
      </b-container>
      <main :class="mainClass" v-else>
        <layout-loading v-if="loading" />
        <slot v-else />
      </main>
    </div>

    <layout-footer class="page__footer" />
  </div>
</template>

<script>
import { capitalize } from '@/helpers/filters';

export default {
  name: 'LayoutPage',
  props: {
    bgColor: {
      type: String,
      required: false,
      default: '',
    },
    bgImage: {
      type: Boolean,
      default: false,
      required: false,
    },
    centered: {
      type: Boolean,
      default: false,
      required: false,
    },
    fluid: {
      type: Boolean,
      required: false,
      default: false,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
    name: {
      type: String,
      required: true,
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
    mainClass() {
      return [
        'page__content',
        this.bgColor,
        this.bgImage ? 'with-tiled-bg' : '',
        this.centered ? 'centered' : '',
      ].filter(c => !!c).join(' ');
    },
    treeTitle() {
      const firstMatchedParts = this.$route.matched.slice(0, this.$route.matched.length - 1);
      const parts = firstMatchedParts.reduce((acc, r) => {
        if (r.meta && r.meta.title) {
          acc.push(capitalize(this.$i18n.t(r.meta.title)));
        }
        return acc;
      }, []);

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
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
.page {
  &__content {
    &.with-tiled-bg {
      background-image: url("/bg-tile.png");
    }

    &.green {
      background-color: $locomotion-green;
    }

    &.centered {
      display: flex;
      flex-direction: column;
      justify-content: space-around;
    }
  }

  &__background {
    background-color: $main-bg;
  }
}
</style>
