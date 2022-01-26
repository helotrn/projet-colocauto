<template>
  <div class="user-claim-credits-box">
    <b-row class="user-claim-credits-box__text">
      <b-col>
        <p>Les transferts sont gérés par l’équipe comptabilité de LocoMotion.</p>
        <p>
          Veuillez nous envoyer <strong>vos coordonnéees bancaires</strong> ou
          <strong>un spécimen chèque</strong>
          par courriel en cliquant sur le bouton ci-dessous ou à
          <a
            href="mailto:info@locomotion.app?subject=Demande de transfert vers votre compte bancaire&body=Veuillez joindre un spécimen chèque à ce courriel si besoin. 
%0D%0A——%0D%0A
Bonjour, %0D%0A%0D%0A

Pourriez-vous transférer la totalité de mon solde LocoMotion vers mon compte en banque? %0D%0A%0D%0A

Vous trouverez les coordonnées de mon compte ou un spécimen chèque en pièce jointe.  %0D%0A%0D%0A

Numéro de compte:%0D%0A
Numéro de transit: %0D%0A
Code banque: %0D%0A
%0D%0A
Cordialement, %0D%0A
"
            >info@locomotion.app</a
          >.
        </p>
        <p>Un membre de l’équipe fera un suivi avec vous.</p>
      </b-col>
    </b-row>

    <b-row class="user-claim-credits-box__buttons" tag="p">
      <b-col>
        <b-button
          class="mr-3"
          href="mailto:info@locomotion.app?subject=Demande de transfert vers votre compte bancaire&body=Veuillez joindre un spécimen chèque à ce courriel si besoin. 
%0D%0A——%0D%0A
Bonjour, %0D%0A%0D%0A

Pourriez-vous transférer la totalité de mon solde LocoMotion vers mon compte en banque? %0D%0A%0D%0A

Vous trouverez les coordonnées de mon compte ou un spécimen chèque en pièce jointe.  %0D%0A%0D%0A

Numéro de compte:%0D%0A
Numéro de transit: %0D%0A
Code banque: %0D%0A
%0D%0A
Cordialement, %0D%0A
"
          variant="primary"
          :disabled="loading"
        >
          Faire une demande de transfert
        </b-button>
        <b-button variant="outline-warning" @click="emitCancel">Annuler</b-button>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <small class="subtext"
          >Le boutton ci-dessus nécessite que vous ayez votre logiciel de courriel de configuré sur
          votre appareil. Veuillez nous écrire directement si le boutton ne fonctionne pas.</small
        >
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

<style lang="scss">
.user-claim-credits-box {
  &__text {
    margin: 15px 0;
  }
  &__buttons {
    .btn-primary {
      margin-left: 0;
    }
  }
  .subtext {
    color: grey;
  }
}
</style>
