<template>
  <div class="loanable-calendar-legend__wrap">
  <strong class="loanable-calendar-legend__title">
    Légende:
  </strong>
  <ul class="loanable-calendar-legend">
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="available"
        ></loanable-calendar-month-cell-content
      >
      Disponible
    </li>
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="partially-available"
        ></loanable-calendar-month-cell-content
      >
      Partiellement disponible
    </li>
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="unavailable"
        ></loanable-calendar-month-cell-content
      >
      Indisponible
    </li>
  </ul>
  </div>
</template>

<script>
import LoanableCalendarMonthCellContent from "@/components/Loanable/CalendarMonthCellContent.vue";

export default {
  name: "LoanableCalendarLegend",
  components: {
    LoanableCalendarMonthCellContent,
  },
};
</script>

<style lang="scss">

.loanable-calendar-legend {
  padding: 0;
  list-style: none;
  display: flex;
  justify-content: flex-start;

  &__title {
    margin: 0 0.5rem;
  }
  &__wrap {
    background: white;
    padding: 10px 16px 1px;
  }
  &__item {
    display: flex;
    align-items: center;
    margin-right: 1rem;
  }

  &__icon {
    width: 3rem;
    height: 3rem;
  }
}
</style>
