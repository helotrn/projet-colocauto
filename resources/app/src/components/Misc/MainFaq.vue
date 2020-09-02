<template>
  <section class="page__section main-faq">
    <b-container>
      <b-row>
        <b-col>
          <div class="page__section__header">
            <h2 class="page__section__title">
              {{ $t('faq.title') }}
            </h2>
          </div>

          <div class="main-faq__questions">
            <div role="tablist">
              <faq-item v-for="id in 10" :id="`${id}`" :key="`faq-${id}`"
                :title="$t('faq.home.' + (id - 1) + '.title')">
                <div v-html="$t('faq.home.' + (id - 1) + '.content')" />
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
