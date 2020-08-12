<template>
  <section class="page__section main-faq">
    <b-container>
      <b-row>
        <b-col>
          <div class="page__section__header">
            <h2 class="page__section__title">
              {{ $t('faq.title') }}
            </h2>
            <b-button size="lg" variant="outline-secondary" to="faq">Visiter la FAQ</b-button>
          </div>
          <div class="main-faq__questions">
            <div role="tablist">
              <faq-item id="1"
                :title="$t('faq.sections.why.questions.0.title')">
                <p v-html="$t('faq.sections.why.questions.0.content')" />
              </faq-item>

              <faq-item id="2" :title="$t('faq.sections.insurance.questions.0.title')">
                <div v-html="$t('faq.sections.insurance.questions.0.content')" />
              </faq-item>

              <faq-item id="3" :title="$t('faq.sections.costs.questions.0.title')">
                <div v-html="$t('faq.sections.costs.questions.0.content')" />
              </faq-item>

              <faq-item id="4" :title="$t('faq.sections.why.questions.1.title')">
                <p v-html="$t('faq.sections.why.questions.1.content')" />
              </faq-item>

              <faq-item id="5" :title="$t('faq.sections.participate.questions.0.title')">
                <div v-html="$t('faq.sections.participate.questions.0.content')" />
              </faq-item>
            </div>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </section>
</template>

<script>
import FaqItem from '@/components/Misc/FaqItem.vue';

export default {
  name: 'MainFaq',
  components: {
    FaqItem,
  },
};
</script>

<style lang="scss">
.main-faq {
  background: #f5f8fb;
  position: relative;
  margin: 0;
  margin-bottom: 0;

  .page__section__title {
    font-size: 50px;
    color: $secondary;
  }

  .container {
    max-width: 700px;
    z-index: 2;
    position: relative;
  }

  .page__section__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 35px;
  }

  background-image: url("/home-motif.png");
  background-repeat: repeat;

  .card {
    border: 0;
    margin-bottom: 10px;

    &-title {
      color: $locomotion-green;
      font-weight: bold;
    }

    &-body {
      .form-group {
        legend {
          font-weight: bold;
        }
      }

      p {
        color: $secondary;
        font-size: 20px;
      }
    }
  }

  .card-header {
    margin: 0;
    padding: 0;
  }
}
</style>
