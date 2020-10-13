<template>
  <layout-page name="cgu" class="cgu__content" padded>
    <b-row tag="section" class="page__section">
      <b-col>

        <b-card>
          <p>
            Pour participer au programme LocoMotion vous devez accepter les
            conditions énoncées dans les documents suivants&nbsp;:
          </p>
          <ul>
            <li>
              <a href="/conditions-generales-participation-programme-locomotion.html">
                Conditions générales de participation au programme LocoMotion
              </a>
            </li>
            <li>
              <a href="/politique-confidentialite.html">
                Politique de confidentialité
              </a>
            </li>
            <li>
              <a href="/conditions-utilisation-desjardins-assurances.html">
                Conditions d’utilisation relatives à l’assurance offerte par Desjardins Assurances
              </a>
            </li>
          </ul>
        </b-card>

      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
export default {
  name: 'Privacy',
};
</script>
<style lang="scss">
.page.cgu {
  .page__background {
    background: #f5f8fb;
    background-image: url("/home-motif.png");
    background-repeat: repeat;
  }
}
</style>
