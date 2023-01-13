<template>
  <ul class="loanable-calendar-legend">
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="available"
        current
        >XX</loanable-calendar-month-cell-content
      >
      Disponible
    </li>
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="partially-available"
        current
        >XX</loanable-calendar-month-cell-content
      >
      Disponibilité limitée
    </li>
    <li class="loanable-calendar-legend__item">
      <loanable-calendar-month-cell-content
        class="loanable-calendar-legend__icon"
        availability="unavailable"
        current
        >XX</loanable-calendar-month-cell-content
      >
      Indisponible
    </li>
  </ul>
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

  .loanable-calendar-legend__item {
    display: flex;
    align-items: center;
  }

  .loanable-calendar-legend__icon {
    width: 3rem;
    height: 3rem;
  }
}
</style>
