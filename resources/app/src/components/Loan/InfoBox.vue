<template>
  <div class="loan-info-box">
    <b-card class="shadow" bg="white" no-body>
      <router-link class="card-body" :to="`/loans/${this.loan.id}`">
        <b-row>
          <b-col lg="6">
            <b-row>
              <b-col class="loan-info-box__image" v-if="otherUser">
                <div
                  class="loan-info-box__image__user"
                  :style="{ backgroundImage: loanPersonImage }"
                />

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
                  <span class="loan-info-box__name__user" v-else-if="loan.loanable.community">
                    {{ loan.loanable.community.name }}
                  </span>

                  <br />
                  <span class="loan-info-box__name__loanable">{{ loan.loanable.name }}</span>
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
                variant="success"
                v-if="hasButton('accept') && userRole === 'owner'"
                @click.prevent="acceptLoan"
              >
                Accepter
              </b-button>

              <b-button
                size="sm"
                variant="outline-primary"
                v-if="hasButton('view')"
                :to="`/loans/${this.loan.id}`"
              >
                Consulter
              </b-button>

              <b-button
                size="sm"
                variant="outline-danger"
                v-if="hasButton('deny') && userRole === 'owner'"
                @click.prevent="denyLoan"
              >
                Refuser
              </b-button>

              <b-button
                size="sm"
                variant="outline-danger"
                v-if="hasButton('cancel')"
                @click.prevent="cancelLoan"
              >
                Annuler
              </b-button>
            </div>
          </b-col>
        </b-row>
        <b-row v-if="withSteps">
          <b-col class="loan-info-box__steps">
            <loan-menu :item="loan" :user="user" horizontal />
          </b-col>
        </b-row>
      </router-link>
    </b-card>
    <div v-if="hasButton('accept')">
      <p class="loan-info-box__instructions muted" v-if="userRole === 'owner'">
        Cette personne devrait entrer en contact avec vous sous peu.
      </p>
      <p class="loan-info-box__instructions muted" v-else>
        La demande est envoyée! Maintenant contactez la personne propriétaire pour valider votre
        demande.
      </p>
    </div>
  </div>
</template>

<script>
import LoanMenu from "@/components/Loan/Menu.vue";

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
  },
  components: {
    LoanMenu,
  },
  computed: {
    loanPersonImage() {
      if (!this.otherUser) {
        return "";
      }

      const { avatar } = this.otherUser;
      if (!avatar) {
        return "";
      }

      return `url('${avatar.sizes.thumbnail}')`;
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
      return this.$dayjs(this.loan.departure_at)
        .add(this.loan.duration_in_minutes, "minute")
        .format("YYYY-MM-DD HH:mm:ss");
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
  methods: {
    async makeloanUnavailableFor24h() {
      await this.$store.dispatch("loans/makeUnavailableFor24h");
      this.$bvModal.msgBoxOk("Ce véhicule est indisponible pour les prochaines 24h.", {
        buttonSize: "sm",
        footerClass: "d-none",
      });
    },
    async acceptLoan() {
      const intention = this.loan.actions.find((a) => a.type === "intention");
      try {
        await this.$store.dispatch("loans/completeAction", intention);
        await this.$store.dispatch("loadUser");
      } catch (e) {
        throw e;
      }
    },
    async cancelLoan() {
      const intention = this.loan.actions.find((a) => a.type === "intention");
      try {
        await this.$store.dispatch("loans/cancel", this.loan.id);
        await this.$store.dispatch("loadUser");
      } catch (e) {
        throw e;
      }
    },
    async denyLoan() {
      await this.$store.dispatch("loans/cancel", this.loan.id);
      await this.$store.dispatch("loadUser");
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
