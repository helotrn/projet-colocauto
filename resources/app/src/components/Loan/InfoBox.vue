<template>
  <div class="loan-info-box">
    <b-card :class="{ shadow: true, loading, border: !!variant }" :border-variant="variant" no-body>
      <router-link :class="{ 'card-body': true, disabled: loading }" :to="`/loans/${this.loan.id}`">
        <b-row>
          <b-col lg="6">
            <b-row>
              <b-col class="loan-info-box__image" v-if="otherUser">
                <user-avatar :user="otherUser" class="loan-info-box__image__user" />

                <div class="loan-info-box__image__loanable">
                  <div :style="{ backgroundImage: loanableImage }" />
                </div>
              </b-col>
              <b-col class="loan-info-box__image" v-else>
                <div
                  class="loan-info-box__image__user"
                  :style="{ backgroundImage: loanableImage }"
                />

                <div class="loan-info-box__image__loanable" v-if="otherUser">
                  <div :style="{ backgroundImage: loanableImage }" />
                </div>
              </b-col>

              <b-col class="loan-info-box__name">
                <span>
                  <span class="loan-info-box__name__user" v-if="otherUser">
                    {{ otherUser.full_name }}
                  </span>
                  <br />
                  <span class="loan-info-box__name__loanable">{{ loan.loanable.name }}</span>

                  <loan-status :item="loan" class="mt-2"></loan-status>
                </span>
              </b-col>
            </b-row>
          </b-col>

          <b-col class="loan-info-box__details mb-2 mt-2" lg>
            <span>
              <span v-if="multipleDays">
                {{ loan.departure_at | date }} {{ loan.departure_at | time }}<br />
                {{ returnAt | date }} {{ returnAt | time }}
              </span>
              <span v-else>
                {{ loan.departure_at | date }}<br />
                {{ loan.departure_at | time }} à {{ returnAt | time }}
              </span>
            </span>
          </b-col>

          <b-col class="loan-info-box__actions" lg>
            <div>
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
                variant="outline-danger"
                v-if="hasButton('deny') && userRoles.includes('owner')"
                @click.prevent="denyLoan"
              >
                Refuser
              </b-button>

              <b-button
                size="sm"
                :disabled="loading"
                variant="outline-danger"
                v-if="hasButton('cancel')"
                @click.prevent="cancelLoan"
              >
                Annuler
              </b-button>
            </div>
          </b-col>
        </b-row>
      </router-link>
    </b-card>
  </div>
</template>

<script>
import LoanStatus from "@/components/Loan/Status.vue";
import UserAvatar from "@/components/User/Avatar.vue";

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
    hasButton(name) {
      return this.buttons.indexOf(name) > -1;
    },
  },
};
</script>

<style lang="scss">
.loan-info-box {
  .card {
    margin-bottom: 20px;
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

  &__image.col {
    height: 85px;
    position: relative;
    width: 85px;
    flex: 0 1 115px;

    .loan-info-box__image {
      &__user,
      &__loanable > div {
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        border-radius: 100%;
      }

      &__user {
        height: 100%;
        width: 85px;
        margin: 0 auto;
      }

      &__loanable {
        position: absolute;
        bottom: 0;
        left: calc(50% + 15px);
        height: 50%;
        width: 85px;

        > div {
          width: calc(85px / 2);
          height: calc(85px / 2);
        }
      }
    }
  }

  &__name.col {
    flex-grow: 1;
    color: $black;
  }

  &__name__user {
    font-size: 16px;
    text-style: italic;
  }

  &__name__loanable {
    font-size: 20px;
  }

  &__details.col-lg {
    flex-grow: 1;
    color: $black;
    font-size: 15px;
    text-align: center;
  }

  &__name.col,
  &__details.col-lg,
  &__actions.col-lg {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }

  &__actions.col-lg {
    text-align: right;

    .btn {
      margin-left: 16px;
      margin-top: 8px;
      margin-bottom: 8px;
    }
  }
}
</style>
