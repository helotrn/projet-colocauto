export default {
  computed: {
    hasActiveExtensions() {
      if (!this.item.id) {
        return false;
      }

      // an active extension has the status "in_process"
      return this.item.extensions.reduce(
        (acc, i) => acc || !i.id || i.status === "in_process",
        false
      );
    },
    hasActiveIncidents() {
      if (!this.item.id) {
        return false;
      }

      return this.item.incidents.reduce((acc, i) => acc || i.status !== "completed", false);
    },
    loanIsContested() {
      const { handover, takeover } = this.item;
      return (
        (handover && handover.status === "canceled") || (takeover && takeover.status === "canceled")
      );
    },
    loanIsCompleted() {
      return this.item.status === "completed";
    },
    isOwnedLoanable() {
      return !!this.item.loanable.owner;
    },
    /*
      Use the loanable.is_self_service attribute if true.

      Otherwise keep the old definition of a loanable without owner is self
      service until all tests are done with the new definition.
    */
    loanableIsSelfService() {
      if (this.item.loanable.is_self_service) {
        return true;
      }

      // The loanable is not self service, even if beloinging to the community
      return false;
    },
    loanIsCanceled() {
      return this.item.status === "canceled";
    },
    userIsOwner() {
      if (!this.item.loanable.owner) {
        return false;
      }

      return this.user.id === this.item.loanable.owner.user.id;
    },
    userIsBorrower() {
      return this.user.id === this.item.borrower.user.id;
    },
    borrowerIsOwner() {
      // If no owner, then false.
      if (!this.item.loanable.owner) {
        return false;
      }

      // Otherwise, is the borrower the owner?
      return this.item.borrower.user.id === this.item.loanable.owner.user.id;
    },
    currentStep() {
      if (!this.item.id) {
        return "creation";
      }
      if (this.loanIsCompleted) {
        return "completed";
      }
      if (this.loanIsCanceled) {
        return "canceled";
      }
      let currentStep = this.item.actions.find((a) => a.status === "in_process").type;
      if (this.hasActiveIncidents) {
        currentStep = "incident";
      } else if (this.item.takeover && this.item.takeover.status === "canceled") {
        currentStep = "takeover";
      } else if (this.item.handover && this.item.handover.status === "canceled") {
        currentStep = "handover";
      }
      return currentStep;
    },
  },
  methods: {
    addExtension() {
      const handover = this.item.actions.find((a) => a.type === "handover");

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);

        let newDuration = this.item.actual_duration_in_minutes + 15;
        const inTenMinutes = this.$dayjs().add(10, "minute");

        // if return is (almost) in the past, add 15 minutes to current time as new return time.
        if (inTenMinutes.isAfter(this.item.actual_return_at, "minute")) {
          newDuration = this.$dayjs()
            .add(15, "minute")
            .diff(this.$dayjs(this.item.departure_at), "minute");
        }

        this.item.actions.splice(indexOfHandover, 0, {
          status: "in_process",
          new_duration: newDuration,
          comments_on_extension: "",
          type: "extension",
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById("loan-extension-new");
        this.$scrollTo(el);
      }, 10);
    },
    addIncident(type) {
      const handover = this.item.actions.find((a) => a.type === "handover");

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);
        this.item.actions.splice(indexOfHandover, 0, {
          status: "in_process",
          incident_type: type,
          type: "incident",
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById("loan-incident-new");
        this.$scrollTo(el);
      }, 10);
    },
    async cancelLoan() {
      await this.$store.dispatch("loans/cancel", this.item.id);
      await this.loadItemAndUser();
    },
    hasCanceledStep(step) {
      const { actions } = this.item;
      const intention = actions.find((a) => a.type === "intention");
      const prePayment = actions.find((a) => a.type === "pre_payment");
      const takeover = actions.find((a) => a.type === "takeover");
      const handover = actions.find((a) => a.type === "handover");
      const payment = actions.find((a) => a.type === "payment");

      // Using fallthrough because an earlier step cancels
      // all following steps if it has been canceled
      switch (step) {
        case "payment": // eslint-disable-line no-fallthrough
          if (
            handover?.status === "canceled" &&
            payment?.status !== "canceled" &&
            !this.loanIsCanceled
          ) {
            return false;
          }

          if (
            payment?.status === "canceled" ||
            (payment?.status === "in_process" && this.loanIsCanceled)
          ) {
            return true;
          }
        case "handover": // eslint-disable-line no-fallthrough
          if (
            takeover?.status === "canceled" &&
            handover?.status !== "canceled" &&
            !this.loanIsCanceled
          ) {
            return false;
          }

          if (
            handover?.status === "canceled" ||
            (handover?.status === "in_process" && this.loanIsCanceled)
          ) {
            return true;
          }
        case "takeover": // eslint-disable-line no-fallthrough
          if (
            takeover?.status === "canceled" ||
            (takeover?.status === "in_process" && this.loanIsCanceled)
          ) {
            return true;
          }
        case "pre_payment": // eslint-disable-line no-fallthrough
          if (
            prePayment?.status === "canceled" ||
            (prePayment?.status === "in_process" && this.loanIsCanceled)
          ) {
            return true;
          }
        case "intention": // eslint-disable-line no-fallthrough
          if (
            intention?.status === "canceled" ||
            (intention?.status === "in_process" && this.loanIsCanceled)
          ) {
            return true;
          }
          break;
        default:
          return false;
      }

      return false;
    },
    hasReachedStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find((a) => a.type === "intention");
      const prePayment = actions.find((a) => a.type === "pre_payment");
      const takeover = actions.find((a) => a.type === "takeover");
      const handover = actions.find((a) => a.type === "handover");
      const payment = actions.find((a) => a.type === "payment");

      switch (step) {
        case "creation":
          return !!id;
        case "intention":
          return intention && !!intention.executed_at;
        case "pre_payment":
          return prePayment && !!prePayment.executed_at;
        case "takeover":
          return takeover && !!takeover.executed_at;
        case "handover":
          return handover && !!handover.executed_at;
        case "payment":
          return payment && !!payment.executed_at;
        default:
          return false;
      }
    },
    isCurrentStep(step) {
      return this.currentStep === step;
    },
    /*
      This method determines whether a loan step should be displayed.

      Visible steps should only depend on the loanable, who's involved in the
      loan and the loan itself.
      They should not depend on who the current user is.
    */
    displayStep(step) {
      switch (step) {
        case "intention":
          // Intention is required if loanable is not self-service
          // As of now, it is required even if the borrower is the owner.
          // This is likely to be reviewed.
          return !this.loanableIsSelfService;

        case "pre_payment":
          // Pre-payment should be displayed whenever the loan has an estimated
          // cost over 0. This includes free loans for which the platform tip
          // is greater than 0.
          return (
            parseFloat(this.item.estimated_price) > 0 ||
            parseFloat(this.item.estimated_insurance) > 0 ||
            parseFloat(this.item.platform_tip) > 0
          );

        case "takeover":
          return !this.item.loanable.is_self_service;
        case "handover":
          return true;
        case "payment":
          return true;

        default:
          return false;
      }
    },
  },
};
