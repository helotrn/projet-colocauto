<template>
  <div class="partners-section">
    <section class="page__section partners-section__top d-none d-lg-block">
      <svg-footer-dot />
    </section>

    <section class="page__section partners-section__bottom">
      <b-container>
        <b-row>
          <b-col>
            <h2 class="page__section__title text-center">Merci à nos partenaires</h2>

            <svg-footer-dot class="partners-section__dot d-lg-none" />

            <div class="partners-section__partners">
              <div class="partners-section__partners__partner">
                <img src="/partners/MTL_en_commun.png" />
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/DefiVillIntel_fr_rgb.png" />
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/Montreal_nb.png" />
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/Quebec-Logo-1.png" />
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/logo-canada.png" />
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </section>
  </div>
</template>

<script>
import FooterDot from "@/assets/svg/home-footer-dot.svg";

export default {
  name: "PartnersSection",
  components: {
    "svg-footer-dot": FooterDot,
  },
};
</script>

<style lang="scss">
// This component's style can be found in assets/scss/components/_partners-section.scss
</style>
