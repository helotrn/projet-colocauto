<template>
  <div class="form__section">
    <template v-if="toggleable">
      <component :is="`h${headerLevel}`" v-b-toggle:toggleable="'collapse_' + id" class="section-toggle">
        {{ sectionTitle }} <b-icon font-scale="0.75" icon="chevron-down"></b-icon>
      </component>
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
    headerLevel: {
      type: Number,
      default: 2,
    }
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
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  margin: -1.25rem;
  margin-bottom: 0;

  &.collapsed {
    margin-bottom: -1.25rem;
  }

  .b-icon {
    transition: 0.3s;
  }

  &.not-collapsed .b-icon {
    transform: rotate(180deg);
  }
}
.form__section {
  background: $white;
  padding: 1.25rem;
  border: solid 1px $locomotion-grey;
}
</style>
