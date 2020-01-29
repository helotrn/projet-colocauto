<template>
  <div class="register-box">
    <b-pagination-nav
      v-model="currentPage"
      :number-of-pages="4"
      pills
      align="center"
      use-router
      :hide-goto-end-buttons="true"
      :link-gen="paginationLinks"
      :disabled="true"
    >
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <div class="pagination__text">
      Inscription
    </div>

    <div class="register-box__google">
      <b-button :disabled="loading" variant="primary" class="btn-google">
        <div class="btn-google__icon">
          <svg-google />
        </div>
        Inscription Google
      </b-button>
    </div>

    <div class="register-box__separator">
      <span class="register-box__separator__text">OU</span>
    </div>

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

      <b-form-group label="Mot de passe (confirmation)">
        <b-form-input
          type="password"
          required
          placeholder="Mot de passe (confirmation)"
          v-model="passwordRepeat"
        />
      </b-form-group>

      <b-button type="submit" :disabled="loading" variant="primary" block>S'inscrire</b-button>
    </b-form>
  </div>
</template>

<script>
import helpers from '@/helpers';
import Google from '@/assets/svg/google.svg';

const { extractErrors } = helpers;

export default {
  name: 'registerBox',
  components: {
    'svg-google': Google,
  },
  data() {
    return {
      password: '',
      passwordRepeat: '',
      currentPage: this.$route.params.step,
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
    paginationLinks(pageNum) {
      return pageNum > 1 ? `/register/${pageNum}` : '/register';
    },
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
        this.$router.replace('/register/2');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
            default:
              this.$store.commit('addNotification', {
                content: extractErrors(e.response.data).join(', '),
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
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .register-box__title {
    text-align: center;
  }

  .register-box__google {
    text-align: center;
  }

  .register-box__separator {
    text-align: center;
    margin: 24px 0;
    border-bottom: 1px solid $black;
  }

  .register-box__separator__text {
    position: relative;
    top: 11px;
    padding: 0 20px;
    background: $white;
  }

}
</style>
