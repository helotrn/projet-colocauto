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
                <img src="/partners/DefiVillIntel_fr_rgb.png">
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/Montreal_nb.png">
              </div>

              <div class="partners-section__partners__line-break" />

              <div class="partners-section__partners__partner">
                <img src="/partners/f20-logo-n1-desjardins-v.svg">
              </div>

              <div class="partners-section__partners__partner">
                <img src="/partners/FCM-logo-2018-col_fr.png">
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </section>
  </div>
</template>

<script>
import FooterDot from '@/assets/svg/home-footer-dot.svg';

export default {
  name: 'PartnersSection',
  components: {
    'svg-footer-dot': FooterDot,
  },
};
</script>

<style lang="scss">
.partners-section {
  &__top, &__bottom {
    overflow: hidden;
  }
}
</style>
