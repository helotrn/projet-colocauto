<template>
  <div class="login-box">
    <h1 class="login-box__title">Connexion</h1>

    <b-form class="login-box__form" @submit="login">
      <b-form-group label="Courriel">
        <b-form-input type="email" required placeholder="Courriel" v-model="email" />
      </b-form-group>

      <b-form-group label="Mot de passe">
        <b-form-input type="password" required
          placeholder="Mot de passe" v-model="password" />
      </b-form-group>

      <b-form-group>
        <b-form-checkbox inline v-model="rememberMe">
          Se souvenir de moi
        </b-form-checkbox>
      </b-form-group>

      <b-button type="submit">Se connecter</b-button>
    </b-form>
  </div>
</template>

<script>
export default {
  name: 'LoginBox',
  data() {
    return {
      password: '',
    };
  },
  computed: {
    loading() {
      return this.$store.state.login.loading;
    },
    email: {
      get() {
        return this.$store.state.login.email;
      },
      set(value) {
        return this.$store.commit('login/email', value);
      },
    },
    rememberMe: {
      get() {
        return this.$store.state.login.rememberMe;
      },
      set(value) {
        return this.$store.commit('login/rememberMe', value);
      },
    },
  },
  methods: {
    login(event) {
      this.$store.dispatch('login', {
        email: this.email,
        password: this.password,
      });

      return false;
    },
  },
};
</script>

<style lang="scss">
.login-box {
  background-color: $white;
  padding: 73px 102px;
  width: 590px;
  margin: 0 auto;

  &__title {
    text-align: center;
  }
}
</style>
