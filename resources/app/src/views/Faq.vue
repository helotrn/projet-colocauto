<template>
  <layout-page name="faq" class="faq__content" padded>
    <vue-headful :title="fullTitle" />

    <b-row tag="section" class="page__section">
      <b-col>
        <p class="text-center">
          <b-button class="faq__button" variant="success"
            href="http://bit.ly/locomotion-bienvenue" target="_blank">
            <img src="/icons/faq.png">
            Guide de départ
          </b-button>
        </p>

        <b-row>
          <b-col lg="6" class="page__section__content" v-for="s in 11" :key="`section-${s}`">
            <h2>{{ $t('faq.sections.' + (s - 1) + '.title') }}</h2>

            <div role="tablist">
              <faq-item  v-for="q in parseInt($t('faq.sections.' + (s - 1) + '.count'), 11)"
                :key="`section-${s}-question-${q}`"
                :id="`section-${s}-question-${q}`"
                :title="$t(`faq.sections.${s - 1}.questions.${q - 1}.title`)">
                <div v-html="$t(`faq.sections.${s - 1}.questions.${q - 1}.content`)" />
              </faq-item>
            </div>
          </b-col>
        </b-row>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import FaqItem from '@/components/Misc/FaqItem.vue';

import Authenticated from '@/mixins/Authenticated';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Home',
  mixins: [Authenticated],
  components: {
    FaqItem,
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.faq')),
      ];

      return parts.reverse().join(' | ');
    },
  },
};
</script>

<style lang="scss">
.page.faq {
  .page__background {
    background: #f5f8fb;
    background-image: url("/home-motif.png");
    background-repeat: repeat;
  }

  .faq__button {
    font-size: 18px;
    height: 37px;
    line-height: 37px;
    padding: 0 100px;
    position: relative;

    img {
      position: absolute;
      left: -20px;
      top: -20px;
    }
  }

  .page__section__content {
    margin-top: 60px;
    margin-bottom: 60px;

    h2 {
      color: $secondary;
    }
  }

  .card {
    border: 0;
    margin-bottom: 10px;

    img {
      width: 100%;
      height: auto;
    }
  }

  .card-header {
    margin: 0;
    padding: 0;
  }
}
</style>
