<template>
  <div class="loan-header">
    <b-row>
      <b-col>
        <h1>
          Emprunt {{ loanablePrettyName }}
          <small v-if="userRole === 'other'">(#{{ loan.id }})</small>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="loan-header__description">
        <p class="loan-header__description__people">
          <a href="#" v-b-modal="'borrower-modal'" v-if="userRole !== 'borrower'">
            {{ loan.borrower.user.full_name }}
          </a>

          <br v-if="userRole === 'other'" />

          <a href="#" v-b-modal="'owner-modal'" v-if="loan.loanable.owner && userRole !== 'owner'">
            {{ loan.loanable.owner.user.full_name }}
          </a>
          <span v-else-if="loan.loanable.community">
            {{ loan.loanable.community.name }}
          </span>
        </p>
        <p class="loan-header__description__loan">
          <a href="#" v-b-modal="'loanable-modal'">
            {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }}
          </a>
          <br />
          <span v-if="singleDay">
            {{ loan.departure_at | day | capitalize }} {{ loan.departure_at | date }}
            &bull;
            {{ loan.departure_at | time }} à {{ returnAt | time }}
          </span>
          <span v-else>
            {{ loan.departure_at | day | capitalize }}
            {{ loan.departure_at | date }} {{ loan.departure_at | time }}
            à
            {{ returnAt | day | capitalize }}
            {{ returnAt | date }} {{ returnAt | time }}
          </span>
        </p>
      </b-col>
    </b-row>

    <b-modal
      size="xl"
      :title="`${prettyType} ${loanableDescription} ${loanableOwnerText}`"
      id="loanable-modal"
      footer-class="d-none"
    >
      <loanable-details-box :loanable="loan.loanable" />
    </b-modal>

    <b-modal size="md" title="Coordonnées du propriétaire" id="owner-modal" footer-class="d-none">
      <p v-if="loan.loanable.owner">
        <strong>{{ loan.loanable.owner.user.full_name }}</strong>
      </p>

      <dl v-if="loan.loanable.owner">
        <dt>Téléphone</dt>
        <dd>{{ loan.loanable.owner.user.phone }}</dd>
      </dl>
    </b-modal>

    <b-modal
      size="md"
      title="Coordonnées de l'emprunteur"
      id="borrower-modal"
      footer-class="d-none"
    >
      <p>
        <strong>{{ loan.borrower.user.full_name }}</strong>
      </p>

      <dl>
        <dt>Téléphone</dt>
        <dd>{{ loan.borrower.user.phone }}</dd>
      </dl>
    </b-modal>
  </div>
</template>

<script>
import LoanableDetailsBox from "@/components/Loanable/DetailsBox.vue";

export default {
  name: "LoanHeader",
  components: {
    LoanableDetailsBox,
  },
  props: {
    loan: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    loanableDescription() {
      switch (this.loan.loanable.type) {
        case "car":
          return (
            `${this.loan.loanable.brand} ${this.loan.loanable.model} ` +
            `${this.loan.loanable.year_of_circulation}`
          );
        case "bike":
          return `${this.loan.loanable.model}`;
        case "trailer":
          return "";
        default:
          return "";
      }
    },
    loanableOwnerText() {
      if (this.userRole === "owner") {
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
    prettyType() {
      switch (this.loan.loanable.type) {
        case "car":
          return "Voiture";
        case "bike":
          return "Vélo";
        case "trailer":
          return "Remorque";
        default:
          return "Objet";
      }
    },
    returnAt() {
      const duration = this.loan.actual_duration_in_minutes || this.loan.duration_in_minutes;
      return this.$dayjs(this.loan.departure_at)
        .add(duration, "minute")
        .format("YYYY-MM-DD HH:mm:ss");
    },
    singleDay() {
      return (
        this.$dayjs(this.loan.departure_at).format("YYYY-MM-DD") ===
        this.$dayjs(this.returnAt).format("YYYY-MM-DD")
      );
    },
    userRole() {
      if (this.loan.loanable.owner && this.user.id === this.loan.loanable.owner.user.id) {
        return "owner";
      }

      if (this.user.id === this.loan.borrower.user.id) {
        return "borrower";
      }

      return "other";
    },
  },
};
</script>

<style lang="scss">
.loan-header {
  h1 {
    margin-bottom: 30px;
  }

  &__description {
    font-weight: 600;
    margin-bottom: 30px;

    p {
      margin-bottom: 0;
    }

    &__people {
      font-size: 16px;
      text-style: italic;
    }

    &__loan {
      font-size: 20px;
    }
  }
}
</style>
