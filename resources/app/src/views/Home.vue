<template>
  <layout-page name="home">
    <b-row tag="section" class="page__section">
      <b-col>
        <h2 class="page__section__title">
          La page d'accueil
        </h2>
        <div class="page__section__content">
          <p>Un texte personnalisé. Ici, la page d'accueil.</p>
        </div>
      </b-col>
    </b-row>

    <b-row tag="section" class="page__section" v-if="!loggedIn">
      <b-col>
        <h2 class="page__section__title">
          Se connecter
        </h2>
        <div class="page__section__content">
          <p>Je suis convaincu, je veux essayer.</p>

          <b-btn variant="primary" to="login">Se connecter</b-btn>
          <b-btn variant="primary" to="register">S'inscrire</b-btn>
        </div>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'Home',
  mixins: [Authenticated, Notification],
};
</script>

<style lang="scss">
.page__section__content a {
  margin-right: 10px;
}
</style>
