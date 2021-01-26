<template>
  <layout-page name="insurance" class="insurance__content" padded>
    <b-row tag="section" class="page__section">
      <b-col>

        <b-card>
          <p>
            Pour faciliter la lecture, nous avons simplifié le texte&nbsp;:
          </p>
          <ul>
            <li>
              <a href="/conditions-utilisation-desjardins-assurances.html">
                Conditions d’utilisation relatives à l’assurance offerte par Desjardins Assurances
              </a>
            </li>
          </ul>

          <p>
            Cependant, le texte de la loi, du règlement ou du contrat
            d’assurance sont prioritaires s’il y a une différence.  Vous pouvez
            accéder aux documents de références en suivant ces liens&nbsp;:
          </p>
          <ul>
            <li>
              <a href="/assurances/condition_desjardins_2_59189530_conditions_particulieres.pdf">
                Police d’assurance
              </a>
            </li>
            <li>
              <a href="/assurances/lettre_assureur_courtier.pdf">
                Lettre courtier
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
.page.insurance {
  .page__background {
    background: #f5f8fb;
    background-image: url("/home-motif.png");
    background-repeat: repeat;
  }
}
</style>
