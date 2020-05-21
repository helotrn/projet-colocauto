<template>
  <layout-page name="faq" class="faq__content" padded>
    <b-row tag="section" class="page__section">
      <b-col>
        <h1 class="page__section__title">
          {{ $t('faq.title') }}
        </h1>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.why.title') }}</h2>

          <div>
            <h3>{{ $t('faq.sections.why.questions.0.title') }}</h3>
            <div v-html="$t('faq.sections.why.questions.0.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.why.questions.1.title') }}</h3>
            <div v-html="$t('faq.sections.why.questions.1.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.why.questions.2.title') }}</h3>
            <div v-html="$t('faq.sections.why.questions.2.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.why.questions.3.title') }}</h3>
            <div v-html="$t('faq.sections.why.questions.3.content')" />
          </div>
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.costs.title') }}</h2>

          <div>
            <h3>{{ $t('faq.sections.costs.questions.0.title') }}</h3>
            <div v-html="$t('faq.sections.costs.questions.0.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.costs.questions.1.title') }}</h3>
            <div v-html="$t('faq.sections.costs.questions.1.content')" />
          </div>
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.insurance.title') }}</h2>

          <div>
            <h3>{{ $t('faq.sections.insurance.questions.0.title') }}</h3>
            <div v-html="$t('faq.sections.insurance.questions.0.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.insurance.questions.1.title') }}</h3>
            <div v-html="$t('faq.sections.insurance.questions.1.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.insurance.questions.2.title') }}</h3>
            <div v-html="$t('faq.sections.insurance.questions.2.content')" />
          </div>
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.extension.title') }}</h2>

          <div v-html="$t('faq.sections.extension.content')" />
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.make_reservation.title') }}</h2>

          <div v-html="$t('faq.sections.make_reservation.content')" />
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.cancel_reservation.title') }}</h2>

          <div v-html="$t('faq.sections.cancel_reservation.content')" />
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.takeover.title') }}</h2>

          <div v-html="$t('faq.sections.takeover.content')" />
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.borough.title') }}</h2>

          <div v-html="$t('faq.sections.borough.content')" />
        </div>

        <div class="page__section__content">
          <h2>{{ $t('faq.sections.participate.title') }}</h2>

          <div>
            <h3>{{ $t('faq.sections.participate.questions.0.title') }}</h3>
            <div v-html="$t('faq.sections.participate.questions.0.content')" />
          </div>

          <div>
            <h3>{{ $t('faq.sections.participate.questions.1.title') }}</h3>
            <div v-html="$t('faq.sections.participate.questions.1.content')" />
          </div>
        </div>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

export default {
  name: 'Home',
  mixins: [Authenticated],
};
</script>

<style lang="scss">
.faq__content {
  h2 {
    margin-bottom: 1.2rem;
    margin-top: 2.4rem;
  }

  h3 {
    margin-bottom: 1rem;
    margin-top: 2rem;
  }
}
</style>
