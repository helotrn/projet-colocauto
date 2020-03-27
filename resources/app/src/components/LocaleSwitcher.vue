<template>
  <b-nav-item-dropdown class="locale-switcher d-none" :text="currentLocale" right>
    <b-dropdown-item @click="currentLocale = locale"
      v-for="locale in otherLocales" :key="locale">
      {{ $t(`locales.${locale}`) }}
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>

<script>
export default {
  name: 'LocaleSwitcher',
  data() {
    return {
      locales: ['en', 'fr'],
    };
  },
  computed: {
    currentLocale: {
      get() {
        return this.$i18n.locale;
      },
      set(val) {
        this.$i18n.locale = val;
      },
    },
    otherLocales() {
      return this.locales.filter(l => l !== this.currentLocale);
    },
  },
};
</script>

<style>
.locale-switcher .dropdown-toggle {
  text-transform: uppercase;
}
</style>
