<template>
  <div class="register-box">
    <h1 class="register-box__title">Inscription</h1>

    <b-form class="register-box__form" @submit.prevent="register">
      <b-form-group label="Courriel">
        <b-form-input
          type="email"
          required
          placeholder="Courriel"
          v-model="email"
        />
      </b-form-group>

      <b-form-group label="Mot de passe">
        <b-form-input
          type="password"
          required
          placeholder="Mot de passe"
          v-model="password"
        />
      </b-form-group>

      <b-form-group label="Répéter mot de passe">
        <b-form-input
          type="password"
          required
          placeholder="Répéter mot de passe"
          v-model="passwordRepeat"
        />
      </b-form-group>

      <b-button type="submit" :disabled="loading">S'inscrire</b-button>
    </b-form>
  </div>
</template>

<script>
export default {
  name: 'registerBox',
  data() {
    return {
      password: '',
      passwordRepeat: '',
    };
  },
  computed: {
    loading() {
      return this.$store.state.register.loading;
    },
    email: {
      get() {
        return this.$store.state.register.email;
      },
      set(value) {
        return this.$store.commit('register/email', value);
      },
    },
  },
  methods: {
    async register() {
      this.$store.commit('register/loading', true);

      try {
        await this.$store.dispatch('register', {
          email: this.email,
          password: this.password,
        });

        await this.$store.dispatch('login', {
          email: this.email,
          password: this.password,
        });

        this.$store.commit('register/loading', false);

        this.$router.replace('/app');

        // this.$router.replace('/app');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 401:
            default:
              console.log(e.request);
              this.$store.commit('addNotification', {
                content: 'Courriel déjà utilisé',
                title: "Erreur d'inscription",
                variant: 'danger',
                type: 'register',
              });
          }
        }
      }

      this.$store.commit('register/loading', false);
    },
  },
};
</script>

<style lang="scss">
.register-box {
  background-color: $white;
  padding: 73px 102px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  &__title {
    text-align: center;
  }
}
</style>
