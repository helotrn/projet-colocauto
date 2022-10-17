<template>
  <div class="loan-header">
    <div class="title">
      <h1>
        Emprunt {{ loanablePrettyName }}
        <!-- Show loan id for admins -->
        <small v-if="isLoanAdmin">(#{{ loan.id }})</small>
      </h1>
      <loan-status :item="loan"></loan-status>
    </div>
  </div>
</template>

<script>
import LoanStatus from "@/components/Loan/Status.vue";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "LoanHeader",
  mixins: [UserMixin],
  components: {
    LoanStatus,
  },
  props: {
    loan: {
      type: Object,
      required: true,
    },
    showInstructions: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    loanableOwnerText() {
      if (this.isOwner) {
        return "";
      }

      let ownerName;
      if (this.loan.loanable.owner) {
        ownerName = this.loan.loanable.owner.user.name;
      } else {
        ownerName = `${this.loan.loanable.community.name} (${this.loan.loanable.name})`;
      }

      const particle =
        ["a", "e", "i", "o", "u", "é", "è"].indexOf(ownerName[0].toLowerCase()) > -1 ? "d'" : "de ";
      return `${particle}${ownerName}`;
    },
    loanablePrettyName() {
      let particle;
      let type;

      switch (this.loan.loanable.type) {
        case "car":
          particle = "de la ";
          type = "voiture";
          break;
        case "bike":
          particle = "du ";
          type = "vélo";
          break;
        case "trailer":
          particle = "de la ";
          type = "remorque";
          break;
        default:
          particle = "de l'";
          type = "objet";
          break;
      }

      if (this.loan.loanable.owner && this.user.id === this.loan.loanable.owner.user.id) {
        particle = "de votre ";
      }

      const description = `${particle}${type}`;
      return `${description} ${this.loanableOwnerText}`;
    },
    isOwner() {
      return this.loan.loanable.owner && this.user.id === this.loan.loanable.owner.user.id;
    },
    isLoanAdmin() {
      return this.isAdminOfLoanable(this.loan.loanable);
    },
  },
};
</script>

<style lang="scss">
.loan-header {
  .title {
    margin-bottom: 1rem;
  }
}
</style>
