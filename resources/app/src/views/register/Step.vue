<template>
  <div class="register-step">
    <b-pagination-nav
      v-bind:value="currentPage"
      :number-of-pages="4"
      pills
      align="center"
      use-router
      :hide-goto-end-buttons="true"
      disabled
    >
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <div v-if="item && currentPage == 2" class="register-step__profile">
      <h2>Profil de membre</h2>

      <p class="register-step__profile__text">
        Pour faire connaissance, dites à vos voisines et vos voisins qui vous êtes en remplissant
        les champs suivants.
      </p>

      <profile-form
        v-if="item"
        :form="form"
        :user="item"
        :loading="loading"
        @submit="submitAndReload"
      >
        <hr />

        <b-row>
          <b-col>
            <mailchimp-newsletter :item="user" @optin="item.opt_in_newsletter = $event" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="accept_conditions"
              :label="$t('users.fields.accept_conditions') | capitalize"
              :rules="form.general.accept_conditions.rules"
              type="checkbox"
              :placeholder="placeholderOrLabel('accept_conditions', 'users') | capitalize"
              v-model="item.accept_conditions"
            />
          </b-col>
        </b-row>
      </profile-form>
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 3" class="register-step__community">
      <h2>Preuve de résidence</h2>

      <p class="register-step__community__text">
        Pour rejoindre un quartier ou un voisinage LocoMotion, il faut&hellip; habiter dedans! :-)
        Merci de nous fournir une preuve de résidence.
      </p>

      <div v-if="item && item.communities">
        <community-proof-form
          v-for="community in item.communities"
          :key="community.id"
          :community="community"
          :loading="!hasAllProofs"
          @submit="submitCommunityProof"
        />
      </div>
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 4" class="register-step__intents">
      <h2>Que voulez-vous faire?</h2>

      <register-intent-form
        :user="item"
        v-if="item"
        :loading="loading"
        @submit="submitOwnerDocumentsAndTags"
      />
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 5" class="register-step__completed">
      <h2>Inscription complétée!</h2>

      <layout-loading v-if="!item || loading" />
      <div class="register-step__completed__text" v-else>
        <p>
          Votre inscription sera validée par un membre de l'équipe et vous aurez alors accès à
          toutes les fonctionnalités de LocoMotion.
        </p>

        <p v-if="!!item.owner">
          En attendant, vous pouvez commencer à entrer les informations sur vos véhicules.
        </p>

        <div class="register-step__completed__button">
          <b-button variant="primary" to="/app">Aller au tableau de bord</b-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import Notification from "@/mixins/Notification";
import UserMixin from "@/mixins/UserMixin";

import CommunityProofForm from "@/components/Community/ProofForm.vue";
import MailchimpNewsletter from "@/components/Misc/MailchimpNewsletter.vue";
import ProfileForm from "@/components/Profile/ProfileForm.vue";
import RegisterIntentForm from "@/components/Register/IntentForm.vue";

import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import FormMixin from "@/mixins/FormMixin";

import { extractErrors } from "@/helpers";

export default {
  name: "RegisterStep",
  mixins: [Authenticated, FormLabelsMixin, FormMixin, Notification, UserMixin],
  components: {
    CommunityProofForm,
    FormsValidatedInput,
    MailchimpNewsletter,
    ProfileForm,
    RegisterIntentForm,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        if (!vm.isRegistered) {
          if (vm.$route.path !== "/register/2") {
            vm.$router.replace("/register/2");
          }
        } else if (vm.user.communities.length === 0) {
          if (vm.$route.path !== "/register/map") {
            vm.$router.replace("/register/map");
          }
        } else if (!vm.user.communities.reduce((acc, c) => acc && !!c.proof, true)) {
          if (vm.$route.path !== "/register/3") {
            vm.$router.replace("/register/3");
          }
        } else if (!vm.hasCompletedRegistration) {
          vm.$router.replace("/register/4");
        } else if (vm.$route.path !== "/register/5") {
          vm.$router.replace("/register/5");
        }
      }
    });
  },
  props: {
    id: {
      required: false,
      default: "me",
    },
    step: {
      type: String,
      required: true,
    },
  },
  computed: {
    currentPage() {
      const stepIndex = parseInt(this.step, 10);

      if (Number.isNaN(stepIndex)) {
        return 1;
      }

      return stepIndex;
    },
    hasAllProofs() {
      return this.item.communities.reduce((acc, c) => acc && !!c.proof, true);
    },
  },
  methods: {
    async submitAndReload() {
      try {
        await this.submit();

        this.$store.commit("user", this.item);

        if (!this.item.communities || this.item.communities.length === 0) {
          this.$store.commit("addNotification", {
            content: "Il est temps de choisir un premier voisinage!",
            title: "Profil mis à jour",
            variant: "success",
            type: "register",
          });

          this.$router.push("/register/map");
        } else {
          this.$router.push("/register/3");
        }
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
            default:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: "Erreur d'inscription",
                variant: "danger",
                type: "register",
              });
          }
        }
      }
    },
    async submitCommunityProof() {
      if (!this.hasAllProofs) {
        this.$store.commit("addNotification", {
          content: "Fournissez toutes les preuves requises.",
          title: "Données incomplètes",
          variant: "warning",
          type: "register",
        });
      } else {
        try {
          await this.submit();

          this.$router.push("/register/4");
        } catch (e) {
          if (e.request) {
            switch (e.request.status) {
              case 422:
              default:
                this.$store.commit("addNotification", {
                  content: extractErrors(e.response.data).join(", "),
                  title: "Erreur d'inscription",
                  variant: "danger",
                  type: "register",
                });
            }
          }
        }
      }
    },
    async submitOwnerDocumentsAndTags() {
      try {
        await this.submit();
        await this.$store.dispatch("submitUser");

        this.$router.push("/register/5");
      } catch (e) {
        this.$store.commit("addNotification", {
          content: "Erreur fatale",
          title: "Erreur d'inscription",
          variant: "danger",
          type: "register",
        });
      }
    },
  },
};
</script>

<style lang="scss">
.register-step {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 50px auto;

  &__intents > h2 {
    margin-bottom: 50px;
  }

  .register-step__title {
    text-align: center;
  }

  h2 {
    text-align: center;
  }

  .community-proof-form {
    margin-bottom: 2em;
  }

  &__completed__button {
    text-align: center;
  }
}
</style>
