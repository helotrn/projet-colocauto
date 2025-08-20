<template>
  <div class="loan-info-box">
    <b-card class="text-center" :class="{ loading, border: !!variant }" :border-variant="variant" no-body>
      <template #header v-if="!isInCurrentCommunity">
        <div class="loan-info-box__community-name">{{ loan.loanable.community.name }}</div>
      </template>
      <template #footer v-if="!isInCurrentCommunity">
        <b-button
          size="sm"
          variant="outline-primary"
          :to="`/loans/${loan.id}`"
        >
          Voir la réservation
        </b-button>
      </template>
      <router-link :class="{ 'card-body': true, disabled: loading }" :to="`/loans/${loan.id}`">
        <div class="loan-info-box__image__wrapper">
          <div class="loan-info-box__image">
            <div
              v-if="loanableImage"
              class="loan-info-box__image__loanable"
              :style="{ backgroundImage: loanableImage }"
            />
            <div v-else class="loanable-card__image--default">
              <svg-car v-if="loanableType == 'car'" />
              <svg-bike v-else-if="loanableType == 'bike'" />
            </div>
          </div>

          <div class="loan-info-box__name">
            <span>
              <span class="loan-info-box__name__loanable">{{ loan.loanable.name }}</span>
            </span>
          </div>
        </div>
        <div class="loan-info-box__details mb-2 mt-2">
          <span>
            <span v-if="multipleDays">
              {{ loan.departure_at | day }} {{ loan.departure_at | date }} {{ loan.departure_at | time }}<br />
              {{ returnAt | day }} {{ returnAt | date }} {{ returnAt | time }}
            </span>
            <span v-else>
              {{ loan.departure_at | day }} {{ loan.departure_at | date }}<br />
              {{ loan.departure_at | time }} à {{ returnAt | time }}
            </span>
          </span>
          <span class="my-2" v-if="loan.final_price">Coût: {{ loan.final_price | currency }}</span>
          <span class="my-2" v-else>Coût estimé: {{ loan.estimated_price | currency }}</span>
          <loan-status :item="loan" class="mt-2"></loan-status>
        </div>
        <div v-if="isInCurrentCommunity" class="loan-info-box__actions">
            <b-button
              size="sm"
              :disabled="loading"
              variant="success"
              v-if="hasButton('accept') && userRoles.includes('owner')"
              @click.prevent="acceptLoan"
            >
              Accepter
            </b-button>

            <b-button
              size="sm"
              :disabled="loading"
              variant="outline-primary"
              v-if="hasButton('view')"
              :to="`/loans/${this.loan.id}`"
            >
              Consulter
            </b-button>

            <b-button
              size="sm"
              :disabled="loading"
              variant="outline-primary"
              v-if="hasButton('modify')"
              @click.prevent="editLoan"
            >
              Modifier
            </b-button>

            <b-button
              size="sm"
              :disabled="loading"
              variant="outline-primary"
              v-if="hasButton('deny') && userRoles.includes('owner')"
              @click.prevent="denyLoan"
            >
              Refuser
            </b-button>

            <b-button
              size="sm"
              :disabled="loading"
              variant="outline-primary"
              v-if="hasButton('cancel')"
              @click.prevent="cancelLoan"
            >
              Annuler
            </b-button>
          </div>
      </router-link>
    </b-card>
  </div>
</template>

<script>
import LoanStatus from "@/components/Loan/Status.vue";
import UserAvatar from "@/components/User/Avatar.vue";
import CarIcon from "@/assets/svg/car.svg";
import BikeIcon from "@/assets/svg/bike.svg";

export default {
  name: "LoanInfoBox",
  props: {
    buttons: {
      type: Array,
      required: false,
      default() {
        return ["accept", "deny"];
      },
    },
    loan: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
    withSteps: {
      type: Boolean,
      required: false,
      default: false,
    },
    variant: {
      type: String,
      required: false,
      default: "",
    },
  },
  data: () => ({
    loading: false,
  }),
  components: {
    LoanStatus,
    UserAvatar,
    "svg-car": CarIcon,
    "svg-bike": BikeIcon,
  },
  computed: {
    isAvailable() {
      return this.$store.state.loans.item.isAvailable;
    },
    loanableImage() {
      const { image } = this.loan.loanable;
      if (!image) {
        return "";
      }

      return `url('${image.sizes.thumbnail}')`;
    },
    loanableType() {
      return this.loan.loanable.type;
    },
    multipleDays() {
      return (
        this.$dayjs(this.loan.departure_at).format("YYYY-MM-DD") !==
        this.$dayjs(this.returnAt).format("YYYY-MM-DD")
      );
    },
    otherUser() {
      if (this.user.id === this.loan.borrower.user.id) {
        if (!this.loan.loanable.owner) {
          return null;
        }

        return this.loan.loanable.owner.user;
      }

      return this.loan.borrower.user;
    },
    returnAt() {
      return this.$dayjs(this.loan.actual_return_at).format("YYYY-MM-DD HH:mm:ss");
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
    currentCommunity() {
      return this.$store.state.communities.current
        ? this.$store.state.communities.current
        : this.user.main_community.id;
    },
    isInCurrentCommunity() {
      return !this.loan.loanable.community || (this.loan.loanable.community.id == this.currentCommunity)
    },
  },
  methods: {
    async makeloanUnavailableFor24h() {
      await this.$store.dispatch("loans/makeUnavailableFor24h");
      this.$bvModal.msgBoxOk("Ce véhicule est indisponible pour les prochaines 24h.", {
        buttonSize: "sm",
        footerClass: "d-none",
      });
    },
    async acceptLoan() {
      const intention = this.loan.intention;
      try {
        this.loading = true;
        await this.$store.dispatch("loans/isAvailable", this.loan.id);

        if (!this.isAvailable) throw "unavailable";
        else {
          await this.$store.dispatch("loans/completeAction", {
            action: {
              ...intention,
              loan_id: this.loan.id,
            },
            type: "intention",
          });
          await this.$store.dispatch("dashboard/loadLoans");
        }
      } catch (e) {
        if (e === "unavailable") {
          this.$store.commit("addNotification", {
            content: "Ce véhicule n'est pas disponible pour cette réservation.",
            title: "Véhicule non disponible",
            variant: "danger",
            type: "loans",
          });
        } else throw e;
      } finally {
        this.loading = false;
      }
    },
    async cancelLoan() {
      try {
        this.loading = true;
        await this.$store.dispatch("loans/cancel", this.loan.id);
        await this.$store.dispatch("dashboard/loadLoans");
      } catch (e) {
        throw e;
      } finally {
        this.loading = false;
      }
    },
    async denyLoan() {
      this.loading = true;
      await this.$store.dispatch("loans/cancel", this.loan.id);
      await this.$store.dispatch("dashboard/loadLoans");
      this.loading = false;
    },
    editLoan() {
      this.$emit('edit', this.loan);
    },
    hasButton(name) {
      return this.buttons.indexOf(name) > -1;
    },
  },
};
</script>

<style lang="scss">
.loan-info-box {
  display: flex;
  flex-grow: 1;
  .card {
    margin-bottom: 20px;
    flex-grow: 1;
    min-height: 387px;
  }
  .card-header {
    position: absolute;
    width: 100%;
    display: flex;
    justify-content: center;
    background: transparent;
    padding: 0;
    .loan-info-box__community-name {
      background: $locomotion-light-green;
      color: white;
      font-weight: bold;
      padding: 6px 10px;
      border-radius: 0 0 3px 3px;
    }
  }

  .card-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.1) 0%, #FFFFFF 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    .btn {
      margin-bottom: 2rem;
    }
  }

  .loading {
    opacity: 0.5;
  }

  a.disabled {
    cursor: default;
    pointer-events: none;
  }

  a:hover,
  a:active,
  a:focus {
    text-decoration: none;
  }

  &__instructions {
    margin-top: 10px;
    margin-left: 20px;
  }

  &__image {
    height: 85px;
    position: relative;
    width: 85px;
    &__wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .loan-info-box__image {
      &__loanable {
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        border-radius: 100%;
        height: 100%;
        width: 85px;
        margin: 0 auto;
      }
    }
  }

  &__name {
    flex-grow: 1;
    color: $black;
  }

  &__name__user {
    font-size: 16px;
    text-style: italic;
  }

  &__name__loanable {
    font-size: 18px;
    font-weight: bold;
  }

  &__details {
    flex-grow: 1;
    color: $black;
    font-size: 15px;
    line-height: 20px;
    text-align: center;
  }

  &__name,
  &__details,
  &__actions {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }

  &__actions {
    div {
      display: flex;
      flex-direction: column;
      margin-top: 8px;
    }

    .btn {
      width: 100%;
      margin-bottom: 8px;
    }
  }
  
  .loan-status-pill {
    width: 100%;
  }
}
</style>
