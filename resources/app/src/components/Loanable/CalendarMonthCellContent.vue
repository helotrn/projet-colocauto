<template>
  <div :class="classList" @click="$emit('click')">
    <div class="loanable-calendar-month-cell-content__background">
      <cal-cell-available v-if="availability === 'available'" />
      <cal-cell-partially-available v-else-if="availability === 'partially-available'" />
      <cal-cell-unavailable v-else />
    </div>
    <div class="loanable-calendar-month-cell-content__content">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import CalCellAvailable from "@/assets/svg/loanable-calendar__cal-cell--available.svg";
import CalCellPartiallyAvailable from "@/assets/svg/loanable-calendar__cal-cell--partially-available.svg";
import CalCellUnavailable from "@/assets/svg/loanable-calendar__cal-cell--unavailable.svg";

export default {
  name: "Calendar",
  props: {
    availability: {
      type: String,
      default: "available",
    },
    current: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    "cal-cell-available": CalCellAvailable,
    "cal-cell-partially-available": CalCellPartiallyAvailable,
    "cal-cell-unavailable": CalCellUnavailable,
  },
  computed: {
    classList: function () {
      let classList = {
        "loanable-calendar-month-cell-content": true,
      };

      classList["loanable-calendar-month-cell-content--" + this.availability] = true;

      // Ça marche pas on dirait.
      if (this.current) {
        classList["loanable-calendar-month-cell-content--current"] = true;
      }

      return classList;
    },
  },
};
</script>

<style lang="scss">
// TODO Rename class. Cest trop long
.loanable-calendar-month-cell-content {
  position: relative;
  width: 100%;
  height: 100%;

  .loanable-calendar-month-cell-content__background {
    position: absolute;

    top: 0;
    left: 0;

    height: 100%;
    width: 100%;

    svg {
      height: 100%;
      width: 100%;
    }
  }

  .loanable-calendar-month-cell-content__content {
    position: absolute;
    top: 0;
    left: 0;

    height: 100%;
    width: 100%;

    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    justify-content: center;
    text-align: center;
  }

  &.loanable-calendar-month-cell-content--available {
    color: $content-alert-positive;

    svg rect {
      fill: $background-alert-positive;
    }
  }
  &.loanable-calendar-month-cell-content--partially-available {
    color: $content-alert-warning;

    svg rect {
      fill: $background-alert-warning;
    }
  }
  &.loanable-calendar-month-cell-content--unavailable {
    color: $content-alert-negative;

    svg rect {
      fill: $background-alert-negative;
    }
  }

  &.loanable-calendar-month-cell-content--current
    .loanable-calendar-month-cell-content__background
    svg {
    stroke: currentColor;
  }
  .loanable-calendar-month-cell-content--unavailable svg rect {
    fill: $beige;
  }
}
</style>
