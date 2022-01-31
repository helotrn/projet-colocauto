<template>
  <div class="user-claim-credits-box">
    <b-row class="user-claim-credits-box__text">
      <b-col>
        <p>Les transferts sont gérés par l’équipe comptabilité de LocoMotion.</p>
        <p>
          Envoyez-nous <strong>vos coordonnées bancaires</strong> ou
          <strong>un spécimen chèque</strong>, en cliquant sur le bouton ci-dessous ou en écrivant
          un courriel à
          <a
            href="mailto:info@locomotion.app?subject=Demande de transfert du solde du compte LocoMotion vers un compte bancaire&body=Veuillez joindre un spécimen chèque à ce courriel si besoin. %0D%0A
---- %0D%0A
Bonjour,%0D%0A%0D%0A

Je souhaite transférer la totalité de mon solde LocoMotion vers mon compte bancaire. %0D%0A%0D%0A

Voici les coordonnées de mon compte: %0D%0A%0D%0A

Numéro de compte:%0D%0A
Numéro de transit: %0D%0A
Code banque: %0D%0A
%0D%0A
OU %0D%0A  %0D%0A 
Voici un spécimen chèque en pièce jointe. %0D%0A %0D%0A
Cordialement, %0D%0A"
            >info@locomotion.app</a
          >.
        </p>
        <p>Un membre de l’équipe vous répondra sous peu.</p>
      </b-col>
    </b-row>

    <b-row class="user-claim-credits-box__buttons" tag="p">
      <b-col>
        <b-button
          class="mr-3"
          href="mailto:info@locomotion.app?subject=Demande de transfert du solde du compte LocoMotion vers un compte bancaire&body=Veuillez joindre un spécimen chèque à ce courriel si besoin. %0D%0A
---- %0D%0A
Bonjour,%0D%0A%0D%0A

Je souhaite transférer la totalité de mon solde LocoMotion vers mon compte bancaire. %0D%0A%0D%0A

Voici les coordonnées de mon compte: %0D%0A%0D%0A

Numéro de compte:%0D%0A
Numéro de transit: %0D%0A
Code banque: %0D%0A
%0D%0A
OU %0D%0A  %0D%0A 
Voici un spécimen chèque en pièce jointe. %0D%0A %0D%0A
Cordialement, %0D%0A"
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
          >Pour que le bouton fonctionne, il faut que le logiciel de courriel de votre appareil soit
          configuré. Veuillez nous écrire directement si le bouton ne fonctionne pas.</small
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
