<template>
  <div class="loan-next-date">
    <div v-if="loaded">
      <div v-if="nextLoan">
        <div v-if="nextDate">
          <b-alert
            variant="warning"
            show
            v-b-modal="`next-borrower-modal-${extensionId}`"
            @click.native.stop.prevent
          >
            La prochaine réservation pour ce véhicule est
            <span class="no-break">à {{ nextDate | time }} le {{ nextDate | date }}</span
            >. Cliquez ici pour voir les coordonnées de la personne sur cette réservation.
          </b-alert>
        </div>

        <b-modal
          size="sm"
          title="Prochain emprunteur"
          :id="`next-borrower-modal-${extensionId}`"
          footer-class="d-none"
        >
          <p>
            <strong>{{ nextLoan.borrower.user.full_name }}</strong>
          </p>

          <dl>
            <dt>Téléphone</dt>
            <dd>{{ nextLoan.borrower.user.phone | phone }}</dd>
          </dl>
        </b-modal>
      </div>
    </div>
    <layout-loading v-else class="loan-next-date__loading" />
  </div>
</template>

<script>
import Vue from "vue";

export default {
  name: "LoanNextDate",
  props: {
    loanId: {
      type: Number,
      required: true,
    },
    loanableId: {
      type: Number,
      required: true,
    },
    extensionId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      loaded: false,
      nextLoan: null,
    };
  },
  computed: {
    nextDate() {
      if (!this.nextLoan) {
        return null;
      }

      return this.nextLoan.departure_at;
    },
  },
  mounted() {
    if (!this.loaded) {
      this.loadLoanNextDate();
    }
  },
  methods: {
    async loadLoanNextDate() {
      try {
        const { data } = await Vue.axios.get(
          `/loanables/${this.loanableId}/loans/${this.loanId}/next`,
          {
            params: {
              fields:
                "id,departure_at,duration_in_minutes,borrower.user.full_name,borrower.user.phone",
            },
          }
        );

        this.loaded = true;
        this.nextLoan = data;
      } catch (e) {
        const { response } = e;

        if (!response || response.status !== 404) {
          throw e;
        }

        this.loaded = true;
      }
    },
  },
  watch: {
    loanableId() {
      this.loaded = false;
      this.nextDate = null;
      this.loadLoanNextDate();
    },
  },
};
</script>

<style lang="scss">
.loan-next-date {
  .alert {
    margin-bottom: 0;
    line-height: 1.2;
    font-size: 14px;

    > span {
      font-size: 14px;
    }
  }

  &__loading {
    max-height: 76px;
  }
}
</style>
