<template>
  <b-modal v-model="showDialog"
    id="loan-modal"
    body-class="loan-modal-box"
    size="lg"
    hide-footer
    header-class="p-2 border-bottom-0"
    @hidden="closeDialog"
  >
    <b-card no-body v-if="loan && loan.loanable">
      <div class="loan-modal-box__image__wrapper">
        <div class="loan-modal-box__image">
          <div
            class="loan-modal-box__image__loanable"
            :style="{ backgroundImage: loanableImage }"
          />
        </div>

        <div class="loan-modal-box__name">
          <span>
            <span class="loan-modal-box__name__loanable">{{ loan.loanable.name }}</span>
          </span>
        </div>
        <router-link :to="`/loans/${loan.id}`">Voir la fiche complète</router-link>
      </div>

      <h2>Modifier votre réservation</h2>
      <loan-form
        :item="loan"
        :form="loanForm"
        :open="true"
        :user="user"
        @submit="updateLoan"
      />

    </b-card>
  </b-modal>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LoanForm from "@/components/Loan/Form.vue";
import Authenticated from "@/mixins/Authenticated";

export default {
  name: "ModalBox",
  components: { FormsValidatedInput, LoanForm },
   mixins: [Authenticated],
  props: {
    value: {
      type: Boolean,
      default: false,
    },
    loan: {
      type: Object,
    },
  },
  computed: {
    showDialog: {
      get () {
        return this.value
      },
      set (val) {
        this.$emit('input', val)
      }
    },
    loanForm() {
      return this.$store.state.loans.form;
    },
    loanableImage() {
      if( !this.loan.loanable ) return "";
      const { image } = this.loan.loanable;
      if (!image) {
        return "";
      }

      return `url('${image.sizes.thumbnail}')`;
    },
    loansRoute() {
      return this.$router.options.routes.find(r => r.meta && r.meta.slug == 'loans')
    },
  },
  methods: {
    async updateLoan() {
      try {
        await this.$store.dispatch('loans/updateItem', this.loansRoute.meta.params);
        this.showDialog = false;
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: "Erreur de sauvegarde",
                variant: "danger",
                type: "form",
              });
              break;
            default:
              throw e;
          }
        }

        throw e;
      }
    },
    closeDialog(){
      this.$emit('hidden')
    },
  },
}
</script>

<style lang="scss">
.loan-modal-box {
  .card {
    margin-bottom: 20px;
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

    .loan-modal-box__image {
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
}
</style>
