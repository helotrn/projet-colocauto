<template>
  <div class="user-claim-credits-box">
    <b-row>
      <b-col>
        <p>
          Par cette action, un courriel sera envoyé à l'équipe LocoMotion pour qu'elle vous envoie
          le solde de votre compte.
        </p>
        <p>
          Assurez-vous d'avoir configuré un autre mode de paiement qu'une carte de crédit ou
          communiquez avec l'équipe pour déterminer le mode de transfert.
        </p>
      </b-col>
    </b-row>

    <b-row class="user-claim-credits-box__buttons" tag="p">
      <b-col class="text-center">
        <b-button
          class="mr-3"
          type="submit"
          variant="primary"
          @click="claimCredit"
          :disabled="loading"
        >
          Confirmer
        </b-button>

        <b-button variant="outline-warning" @click="emitCancel">Annuler</b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  name: "UserClaimCreditsBox",
  data() {
    return {
      loading: false,
    };
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  methods: {
    emitCancel() {
      this.$emit("cancel");
    },
    async claimCredit() {
      this.loading = true;

      try {
        await this.$store.dispatch("account/claimCredit");

        this.$emit("claimed");

        this.$store.commit("addNotification", {
          content: "Votre demande a bien été envoyée. Vous recevrez une réponse sous peu.",
          title: "Demande envoyée",
          variant: "success",
          type: "balance_claim",
        });
      } catch (e) {
        switch (e.request.status) {
          case 429:
            this.$store.commit("addNotification", {
              content: "Votre demande a déjà été envoyée. Vous recevrez une réponse sous peu.",
              title: "Demande déjà envoyée",
              variant: "warning",
              type: "balance_claim",
            });

            this.$emit("cancel");

            break;
          default:
            throw e;
        }
      }

      this.loading = false;
    },
  },
};
</script>

<style lang="scss"></style>
