<template>
  <b-card no-body class="faq-item">
    <b-card-header header-tag="header" role="tab">
      <b-button size="lg" block href="#"
        v-b-toggle="`faq-${id}`" variant="white-secondary">
        {{ title }}
      </b-button>
    </b-card-header>
    <b-collapse :id="`faq-${id}`" role="tabpanel" accordion="faq">
      <b-card-body>
        <b-card-text>
          <slot />
        </b-card-text>
      </b-card-body>
    </b-collapse>
  </b-card>

</template>

<script>
export default {
  name: 'FaqItem',
  props: {
    id: {
      required: true,
      type: String,
    },
    title: {
      required: true,
      type: String,
    },
  },
};
</script>

<style lang="scss">
</style>
