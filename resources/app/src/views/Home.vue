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

    <b-row tag="section" class="page__section">
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
  </layout-page>
</template>

<script>
export default {
  name: 'Home',
};
</script>

<style lang="scss">
</style>
