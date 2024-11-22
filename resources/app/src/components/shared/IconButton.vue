<script>
import Vue from "vue";

export default Vue.extend({
  name: "IconButton",
  props: {
    // Icon to be used in the button.
    icon: {
      type: String,
      default: undefined,
    },
    // Color variant of the button (follows bootstrap variants)
    variant: {
      type: String,
      default: "",
    },
    // If specified, role sets a default icon and variant to be used for the button.
    // Valid roles: add, save, send, cancel, load, reset, delete, remove-item, edit
    role: {
      type: String,
      default: "",
    },
    // Whether the button should show a loading spinner and be disabled.
    loading: {
      type: Boolean,
      default: false,
    },
    // Whether the button should be disabled.
    disabled: {
      type: Boolean,
      default: false,
    },
    // Function to call on click. If the function is async, while it runs the button will behave
    // as if the loading prop was set to true. This can replace the loading prop and the @click
    // event listener pair and should be used where possible.
    onclick: {
      type: Function,
      default: undefined,
    },
    // Should the icon spin when loading rather than showing a spinner
    spinIcon: {
      type: Boolean,
      default: false,
    },
    // Should the button take the whole width and have centered text
    block: {
      type: Boolean,
      default: false,
    },
    // Whether the icon should be above the label, in a box-like layout
    square: {
      type: Boolean,
      default: false,
    },
    // The following props override the variant for the given responsible breakpoint.
    xs: {
      type: String,
      default: null,
    },
    sm: {
      type: String,
      default: null,
    },
    md: {
      type: String,
      default: null,
    },
    lg: {
      type: String,
      default: null,
    },
    xl: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      clicking: false,
    };
  },
  computed: {
    internalVariant() {
      if (this.variant) {
        return this.variant;
      }
      let variant = null;
      switch (this.role) {
        case "add":
          return "outline-success";
        case "save":
        case "accept":
        case "send": {
          variant = "success";
          break;
        }
        case "cancel":
        case "load":
        case "edit":
        case "reset": {
          variant = "ghost-secondary";
          break;
        }
        case "delete":
        case "reject":
          return "outline-danger";
        case "remove-item":
          return "ghost-danger";
      }
      return variant;
    },
    internalIcon() {
      if (this.icon) {
        return this.icon;
      }

      switch (this.role) {
        case "accept":
          return "check-circle";
        case "add":
          return "plus-circle";
        case "save":
          return "file-earmark-arrow-down";
        case "send":
          return "arrow-right-circle";
        case "cancel":
          return null;
        case "load":
          return "arrow-clockwise";
        case "reset":
          return "arrow-counterclockwise";
        case "delete":
          return "trash";
        case "reject":
          return "x-octagon";
        case "remove-item":
          return "trash";
        case "edit":
          return "pencil";
      }
      return null;
    },
    internalLoading() {
      return this.clicking || this.loading;
    },
    buttonClasses() {
      const classes = [];
      if (this.block) {
        classes.push("icon-button-block");
      }
      if (this.square) {
        classes.push("icon-button-square");
      }
      if (this.xs) {
        classes.push(`icon-button-xs-${this.xs}`);
      }
      if (this.sm) {
        classes.push(`icon-button-sm-${this.sm}`);
      }
      if (this.md) {
        classes.push(`icon-button-md-${this.md}`);
      }
      if (this.lg) {
        classes.push(`icon-button-lg-${this.lg}`);
      }
      if (this.xl) {
        classes.push(`icon-button-xl-${this.xl}`);
      }
      return classes;
    },
  },
  methods: {
    async handleClick(e) {
      if (this.onclick) {
        this.clicking = true;
        try {
          await this.onclick();
        } finally {
          this.clicking = false;
        }
      } else {
        this.$emit("click", e);
      }
    },
  },
});
</script>

<template>
  <b-button
    class="icon-button"
    :class="buttonClasses"
    v-bind="$attrs"
    :variant="internalVariant"
    :disabled="disabled || internalLoading"
    @click="handleClick"
  >
    <b-spinner v-if="internalLoading && !spinIcon" class="spinner" small />
    <b-icon
      v-else-if="internalIcon"
      class="button-icon"
      :icon="internalIcon"
      :animation="internalLoading ? 'spin' : ''"
    />
    <slot></slot>
  </b-button>
</template>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";
.btn.icon-button {
  display: inline-flex;
  align-items: center;
  // 20px * 1.2 line-height + 0.5rem padding + 2px border
  min-height: 34px;
  gap: 0.5rem;
  transition: all 0.3s;

  &.icon-button-block {
    display: flex;
    width: 100%;
    justify-content: center;
  }
  &.icon-button-square {
    flex-direction: column;
    justify-content: center;
    gap: 0.25rem;
    padding-top: 0.5rem;
  }

  .spinner {
    width: 20px;
    height: 20px;
    border-width: 2px;
    display: block;
  }
  .b-icon.bi {
    font-size: 20px;
  }
  &.btn-sm {
    // 0.875rem line height * 16px * 1.5 line height + 0.5rem padding + 2px border
    min-height: 31px;
    .spinner {
      width: 18px;
      height: 18px;
    }

    .bi.b-icon {
      font-size: 18px;
    }
  }
  &.btn-inline {
    min-height: unset;
    padding: 0 0.25rem;
    font-size: inherit;
    gap: 0.25rem;
    vertical-align: bottom;
    border: none;
    .bi.b-icon {
      font-size: inherit;
    }
    .spinner {
      width: 1em;
      height: 1em;
    }
  }
}

@mixin sized-button-style($size) {
  .btn.icon-button {
    &.icon-button-#{$size}-block {
      display: flex;
      flex-direction: row;
      width: 100%;
      gap: 0.5rem;
      justify-content: center;
      padding-top: 0.25rem;
    }
    &.icon-button-#{$size}-square {
      flex-direction: column;
      justify-content: center;
      gap: 0.25rem;
      padding-top: 0.5rem;
    }
  }
}

@include media-breakpoint-only(xs) {
  @include sized-button-style("xs");
}
@include media-breakpoint-only(sm) {
  @include sized-button-style("sm");
}
@include media-breakpoint-only(md) {
  @include sized-button-style("md");
}
@include media-breakpoint-only(lg) {
  @include sized-button-style("lg");
}
@include media-breakpoint-only(xl) {
  @include sized-button-style("xl");
}
</style>
