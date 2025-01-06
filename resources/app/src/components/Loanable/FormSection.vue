<template>
  <div class="form__section">
    <template v-if="toggleable">
      <h2 v-b-toggle:toggleable="'collapse_' + id" class="section-toggle">
        <b-icon font-scale="0.75" icon="chevron-right"></b-icon> {{ sectionTitle }}
      </h2>
      <b-collapse
        :id="'collapse_' + id"
        ref="collapse"
        :visible="visible || forceShow"
        :accordion="'collapse_' + id"
        @input="visible = $event"
      >
        <slot />
      </b-collapse>
    </template>
    <template v-else>
      <h2>{{ sectionTitle }}</h2>
      <slot />
    </template>
  </div>
</template>

<script>
export default {
  name: "FormSection",
  props: {
    sectionTitle: {
      type: String,
      required: true,
    },
    inititallyVisible: {
      type: Boolean,
      default: true,
    },
    toggleable: {
      type: Boolean,
      default: false,
    },
    forceShow: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      id: null,
      // if route has hash, we keep everything uncollapsed
      // so we can scroll to the proper element.
      visible: this.inititallyVisible || !!this.$route.hash,
    };
  },
  mounted() {
    this.id = this._uid;
  },
  methods: {
    show() {
      this.visible = true;
    },
  },
};
</script>
<style scoped lang="scss">
.section-toggle {
  transition-duration: 0.5s;

  &.collapsed {
    margin-bottom: 0;
  }

  .b-icon {
    transition: 0.3s;
  }

  &.not-collapsed .b-icon {
    transform: rotate(90deg);
  }
}
</style>
