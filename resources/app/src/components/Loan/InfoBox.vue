<template>
  <div class="loan-info-box">
    <b-card class="shadow" bg="white" no-body>
      <router-link class="card-body" :to="`/loans/${this.loan.id}`">
        <b-row>
          <b-col class="loan-info-box__image">
            <div :style="{ backgroundImage: loanImage }" />
          </b-col>

          <b-col class="loan-info-box__name">
            <span>{{ otherUser.full_name }}</span>
          </b-col>

          <b-col class="loan-info-box__details">
            <span>
              {{ loan.loanable.name }}<br>
              <span v-if="multipleDays">
                {{ loan.departure_at | date }} {{ loan.departure_at | time }}<br>
                {{ returnAt | date }} {{ returnAt | time }}
              </span>
              <span v-else>
                {{ loan.departure_at | date }}<br>
                {{ loan.departure_at | time }} à {{ returnAt | time }}
              </span>
            </span>
          </b-col>

          <b-col class="loan-info-box__actions">
            <div>
              <b-button size="sm" variant="success" v-if="hasButton('accept')"
                @click="acceptLoan">
                Accepter
              </b-button>

              <b-button size="sm" variant="outline-primary" v-if="hasButton('view')"
                :to="`/loans/${this.loan.id}`">
                Consulter
              </b-button>

              <b-button size="sm" variant="outline-danger" v-if="hasButton('deny')"
                @click="denyLoan">
                Refuser
              </b-button>

              <b-button size="sm" variant="outline-danger" v-if="hasButton('cancel')"
                @click="cancelLoan">
                Annuler
              </b-button>
            </div>
          </b-col>
        </b-row>
      </router-link>
    </b-card>
    <p class="loan-info-box__instructions muted" v-if="userIsOwner">
      Cette personne devrait entrer en contact avec vous sous peu.
    </p>
    <p class="loan-info-box__instructions muted" v-else>
      Par convention, il est de votre responsabilité de contacter cette personne.
    </p>
  </div>
</template>

<script>
export default {
  name: 'LoanInfoBox',
  props: {
    buttons: {
      type: Array,
      required: false,
      default() {
        return ['accept', 'deny'];
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
  },
  computed: {
    loanImage() {
      const { avatar } = this.otherUser;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    multipleDays() {
      return this.$dayjs(this.loan.departure_at).format('YYYY-MM-DD')
        !== this.$dayjs(this.returnAt).format('YYYY-MM-DD');
    },
    otherUser() {
      if (this.user.id === this.loan.borrower.user.id) {
        return this.loan.loanable.owner.user;
      }

      return this.loan.borrower.user;
    },
    returnAt() {
      return this.$dayjs(this.loan.departure_at)
        .add(this.loan.duration_in_minutes, 'minute')
        .format('YYYY-MM-DD HH:mm:ss');
    },
    userIsOwner() {
      return this.user.id === this.loan.loanable.owner.user.id;
    },
  },
  methods: {
    async makeloanUnavailableFor24h() {
      await this.$store.dispatch('loans/makeUnavailableFor24h');
      this.$bvModal.msgBoxOk('Ce véhicule est indisponible pour les prochaines 24h.', {
        buttonSize: 'sm',
        footerClass: 'd-none',
      });
    },
    acceptLoan() {
      this.$store.dispatch('loans/accept', this.loan.id);
    },
    cancelLoan() {
      this.$store.dispatch('loans/cancel', this.loan.id);
    },
    denyLoan() {
      this.$store.dispatch('loans/deny', this.loan.id);
    },
    hasButton(name) {
      return this.buttons.indexOf(name) > -1;
    },
  },
};
</script>

<style lang="scss">
.loan-info-box {
  a:hover, a:active, a:focus {
    text-decoration: none;
  }

  &__instructions {
    margin-top: 10px;
    margin-left: 20px;
  }

  &__image.col {
    flex: 0 1 115px;
    height: 85px;

    > div {
      height: 85px;
      width: 85px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      border-radius: 100%;
    }
  }

  &__name.col {
    flex-grow: 1;
    color: $black;
    font-size: 20px;
  }

  &__details.col {
    flex-grow: 1;
    color: $black;
    font-size: 15px;
  }

  &__name.col,
  &__details.col,
  &__actions.col {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }

  &__actions.col {
    flex: 0 1 220px;
    text-align: right;

    .btn {
      margin-left: 16px;
    }
  }
}
</style>
