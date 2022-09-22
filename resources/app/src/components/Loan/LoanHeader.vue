<template>
  <div class="loan-header">
    <b-row>
      <b-col>
        <h1>
          Emprunt {{ loanablePrettyName }}
          <!-- Show loan id for admins -->
          <small v-if="userRoles.includes('admin')">(#{{ loan.id }})</small>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="loan-header__description">
        <p class="loan-header__description__people">
          <!-- Display borrower's name for owner or admins -->
          <span v-if="!userRoles.includes('borrower')">
            Emprunteur-se&nbsp;:
            <a v-if="borrowerUrl" :href="borrowerUrl">
              {{ loan.borrower.user.full_name }}
            </a>
            <span v-else>
              {{ loan.borrower.user.full_name }}
            </span>
            <span class="badge badge-light badge-pill" v-b-modal="'borrower-modal'"> ? </span>
          </span>

          <!-- Skip line if borrower's and owner's names are to be displayed (for admins). -->
          <br v-if="userRoles.includes('admin')" />

          <!-- Display owner's name for borrower or admins -->
          <span v-if="loan.loanable.owner && !userRoles.includes('owner')">
            Propriétaire&nbsp;:
            <a v-if="ownerUrl" :href="ownerUrl">
              {{ loan.loanable.owner.user.full_name }}
            </a>
            <span v-else v-b-modal="'owner-modal'">
              {{ loan.loanable.owner.user.full_name }}
            </span>
            <span class="badge badge-light badge-pill" v-b-modal="'owner-modal'"> ? </span>
          </span>
          <!-- Display community's name if present -->
          <span v-else-if="loan.loanable.community">
            Communauté&nbsp;:
            {{ loan.loanable.community.name }}
          </span>
        </p>
        <p class="loan-header__description__loan">
          Véhicule&nbsp;:
          <a v-if="loanableUrl" :href="loanableUrl">
            {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }}
          </a>
          <span v-else> {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }} </span>
          <span class="badge badge-light badge-pill" v-b-modal="'loanable-modal'"> ? </span>
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
      <loanable-details-box :loanable="loan.loanable" showInstructions />
    </b-modal>

    <b-modal
      v-if="loan.loanable.owner"
      size="md"
      title="Coordonnées du propriétaire"
      id="owner-modal"
      footer-class="d-none"
    >
      <p>
        <strong>{{ loan.loanable.owner.user.full_name }}</strong>
      </p>

      <dl>
        <dt>Téléphone</dt>
        <dd v-if="loan.loanable.owner.user.phone">{{ loan.loanable.owner.user.phone }}</dd>
        <dd v-else>Sera affiché quand la demande d'emprunt sera créée.</dd>
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
    showInstructions: {
      type: Boolean,
      required: false,
      default: false,
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
      if (this.userRoles.includes("owner")) {
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
    /*
      Returns an array containing all user roles in the current loan.
    */
    userRoles() {
      const roles = [];

      // User is global admin
      if (this?.user?.role === "admin") {
        roles.push("admin");
      }

      if (this.loan.loanable.owner && this.user.id === this.loan.loanable.owner.user.id) {
        roles.push("owner");
      }

      if (this.user.id === this.loan.borrower.user.id) {
        roles.push("borrower");
      }

      return roles;
    },
    userIsAdmin() {
      // User is global admin
      if (this?.user?.role === "admin") return true;

      // User is admin of the community to which the loanable belongs.
      const community = this?.user?.communities?.find(
        (c) => c.id && c.id === this?.loan?.loanable?.community_id
      );

      return community?.role === "admin";
    },
    borrowerUrl() {
      const borrowerId = this?.loan?.borrower?.user?.id;

      if (this.userIsAdmin && borrowerId) return "/admin/users/" + borrowerId;

      return "";
    },
    ownerUrl() {
      const ownerId = this?.loan?.loanable?.owner?.user?.id;

      if (this.userIsAdmin && ownerId) return "/admin/users/" + ownerId;

      return "";
    },
    loanableUrl() {
      const loanableId = this?.loan?.loanable?.id;

      if (this.userIsAdmin && loanableId) return "/admin/loanables/" + loanableId;

      return "";
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
