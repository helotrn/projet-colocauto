<template>
  <div class="home page">
    <header>
      <h1>Locomotion</h1>
    </header>
    <b-container tag="main" class="page__content">
      <b-row tag="section">
        <b-col>
          <h2 class="page__section__title">
            La page d'accueil
          </h2>
          <div class="page__section__content">
            <p>Un texte personnalisé. Ici, la page d'accueil.</p>
          </div>
        </b-col>
      </b-row>
      <b-row tag="section">
        <b-col>
          <h2 class="page__section__title">
            Se connecter
          </h2>
          <div class="page__section__content">
            <p>Je suis convaincu, je veux essayer.</p>

            <b-btn variant="primary" to="login">Se connecter</b-btn>
          </div>
        </b-col>
      </b-row>
    </b-container>
    <footer class="page__footer">
      <molotov-footer />
    </footer>
  </div>
</template>

<script>
import MolotovFooter from '@/components/Molotov/Footer.vue';

export default {
  name: 'Home',
  components: { MolotovFooter },
};
</script>

<style lang="scss">
</style>
