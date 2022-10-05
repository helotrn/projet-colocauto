<template>
  <div class="status-container">
    <div
      v-b-tooltip.hover
      :title="$t(`statuses.${loanStatus.status}.description`)"
      class="loan-status-pill"
      :class="loanStatus.variant"
    >
      {{ $t(`statuses.${loanStatus.status}.text`) }}
    </div>
    <div v-if="item.loanable.is_self_service">
      <span class="loan-status-pill info">{{ $t("statuses.self_service") }}</span>
    </div>
  </div>
</template>

<script>
import LoanStepsSequence from "@/mixins/LoanStepsSequence";
import UserMixin from "@/mixins/UserMixin";
import locales from "@/locales";

export default {
  name: "LoanStatus",
  mixins: [LoanStepsSequence, UserMixin],
  props: {
    // Item is a loan.
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    loanStatus() {
      if (!this.item.id || !this.item.intention) {
        return {
          status: "new",
          variant: "success",
        };
      }
      if (this.loanIsCanceled) {
        return {
          status: "canceled",
          variant: "danger",
        };
      }
      if (this.hasActiveIncidents) {
        return {
          status: "active_incident",
          variant: "danger",
        };
      }
      if (this.loanIsContested) {
        return {
          status: "contested",
          variant: "danger",
        };
      }
      if (this.loanIsCompleted) {
        return {
          status: "completed",
          variant: "success",
        };
      }
      if (this.item.intention.status === "in_process") {
        return {
          status: "waiting_for_approval",
          variant: "warning",
        };
      }
      if (this.item.pre_payment.status === "in_process") {
        return {
          status: "waiting_for_prepayment",
          variant: "warning",
        };
      }
      if (this.item.takeover.status === "in_process") {
        if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
          return {
            status: "expired_reservation",
            variant: "warning",
          };
        }

        if (this.item.loanable.is_self_service) {
          if (this.$dayjs(this.item.departure_at).isBefore(this.$dayjs(), "minute")) {
            return {
              status: "waiting_for_takeover_self_service",
              variant: "warning",
            };
          }
          return {
            status: "confirmed_reservation_self_service",
            variant: "success",
          };
        }

        if (this.$dayjs(this.item.departure_at).isBefore(this.$dayjs(), "minute")) {
          return {
            status: "waiting_for_takeover",
            variant: "warning",
          };
        }
        return {
          status: "confirmed_reservation",
          variant: "success",
        };
      }
      if (
        this.item.extensions &&
        this.item.extensions.reduce((acc, e) => acc || e.status === "in_process", false)
      ) {
        return {
          status: "waiting_for_extension",
          variant: "warning",
        };
      }
      if (this.item.handover.status === "in_process") {
        if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
          return {
            status: "waiting_for_handover",
            variant: "warning",
          };
        }

        if (this.item.loanable.is_self_service) {
          if (this.$dayjs(this.item.departure_at).isAfter(this.$dayjs(), "minute")) {
            return {
              status: "confirmed_reservation_self_service",
              variant: "success",
            };
          }
          return {
            status: "ongoing_reservation_self_service",
            variant: "success",
          };
        }

        if (this.$dayjs(this.item.departure_at).isAfter(this.$dayjs(), "minute")) {
          return {
            variant: "success",
            status: "confirmed_reservation",
          };
        }
        return {
          status: "ongoing_reservation",
          variant: "success",
        };
      }
      if (this.item.payment.status === "in_process") {
        if (this.item.loanable.is_self_service) {
          return {
            status: "waiting_for_payment_self_service",
            variant: "success",
          };
        }
        return {
          status: "waiting_for_payment",
          variant: "warning",
        };
      }
      return {};
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
      },
      fr: {
        ...locales.fr.loans,
      },
    },
  },
};
</script>

<style lang="scss">
.status-container {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.loan-status-pill {
  display: inline-block;
  font-size: 0.8em;
  padding: 0.2em 0.5em;
  border-radius: 0.25em;
  font-weight: 600;
  &.warning {
    color: $content-alert-warning;
    background-color: $background-alert-warning;
  }
  &.success {
    color: $content-alert-positive;
    background-color: $background-alert-positive;
  }
  &.danger {
    color: $content-alert-negative;
    background-color: $background-alert-negative;
  }
  &.info {
    background-color: $background-alert-informative;
    color: $content-alert-informative;
  }
}
</style>
