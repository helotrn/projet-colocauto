<template>
  <div id="locomotion">
    <aside v-if="environment" class="p-4" :class="environment">
      <p class="mb-0 text-center">👋 Vous consultez actuellement une page de {{ environment }}, pour vous rendre sur l'application Coloc'Auto c'est par ici : <a href="https://app.colocauto.org">app.colocauto.org</a></p>
    </aside>
    <router-view />
  </div>
</template>

<script>
import Notification from "@/mixins/Notification";

export default {
  name: "ColocAuto",
  mixins: [Notification],
  mounted() {
    if (this.$store.state.token && !this.$store.state.loading && !this.$store.state.loaded) {
      this.$store.dispatch("loadUser");
    }
  },
  computed: {
    environment(){
      const subdomain = location.host.split('.').shift();
      if( subdomain == 'test' || subdomain == 'dev' ) return subdomain;
      else if ( subdomain.match(/localhost/) ) return 'dev';
      return '';
    },
  },
};
</script>

<style lang="scss" scoped>
aside.dev {
  background: #ffccaa;
}
-aside.test {
  background: #87aade;
}
aside a {
  color: #212529;
}

.terms-toast {
  max-height: calc(100vh - 16px);
  overflow: auto !important;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
