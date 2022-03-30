<template>
  <layout-page name="insurance" class="insurance__content" padded>
    <b-row tag="section" class="page__section">
      <b-col>
        <b-card>
          <p>
            <strong>À lire pour comprendre l'assurance LocoMotion par Desjardins Assurances</strong>
          </p>
          <ol>
            <li>
              <a href="https://mailchi.mp/solon-collectif/loco-assurances" target="_blank">
                L'assurance LocoMotion en résumé
              </a>
            </li>
            <li>
              <a href="/conditions-utilisation-desjardins-assurances.html">
                Conditions d'utilisation l'assurance LocoMotion
              </a>
            </li>
            <li>
              <a href="/assurances/condition_desjardins_2_59189530_conditions_particulieres.pdf">
                Police d’assurance
              </a>
            </li>
          </ol>
          <p><strong>Vous prêtez votre auto?</strong></p>
          <p>
            👉 Si ce n'est pas déjà fait, voici la
            <a href="/assurances/lettre_assureur_courtier.pdf">lettre à envoyer à votre assureur</a
            >.
          </p>
        </b-card>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
export default {
  name: "Privacy",
};
</script>

<style lang="scss">
.page.insurance {
  .page__background {
    background: #f5f8fb;
    background-image: url("/home-motif.png");
    background-repeat: repeat;
  }
  p,
  ul,
  ol {
    color: $secondary;
  }
}
</style>
