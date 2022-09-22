<template>
  <b-button @click="googleLogin" :disabled="loading" variant="primary" class="btn-google">
    <div class="btn-google__icon">
      <svg-google />
    </div>
    {{ label }}
  </b-button>
</template>

<script>
import Google from "@/assets/svg/google.svg";
export default {
  name: "GoogleAuthButton",
  components: {
    "svg-google": Google,
  },
  props: {
    label: {
      type: String,
      required: true,
    },
  },
  computed: {
    loading() {
      return this.$store.state.loading;
    },
  },
  methods: {
    googleLogin() {
      // Creating a link dynamically apparently prevents ios/facebook app from loading it in a webview
      // See #955
      const authUrl = `${process.env.VUE_APP_BACKEND_URL}/auth/google`;
      this.$store.commit("loading", true);
      const a = document.createElement("a");
      a.setAttribute("href", authUrl);
      a.dispatchEvent(new MouseEvent("click", { view: window, bubbles: true, cancelable: true }));
    },
  },
};
</script>
