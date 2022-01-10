<template>
  <div class="register-step">
    <b-pagination-nav
      v-bind:value="currentPage"
      :number-of-pages="3"
      pills
      align="center"
      use-router
      :hide-goto-end-buttons="true"
      disabled
      v-show="currrentPage < 4"
    >
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <div v-if="item && currentPage == 2">
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

    <div v-if="currentPage == 4" class="register-step__reasons-why">
      <h2>Vous avez fait le bon choix</h2>

      <div class="swiping-card" v-show="currentSlide == 1">
        <svg-driving class="img" />
        <div class="text">
          Soyez protégé-e avec <strong>une assurance complète</strong>, pas compliquée.
        </div>
      </div>

      <div class="swiping-card" v-show="currentSlide == 2">
        <svg-lend class="img" />
        <div class="text">
          C’est un projet <strong>par et pour votre voisinage</strong>, qui évolue avec vous.
        </div>
      </div>

      <div class="swiping-card" v-show="currentSlide == 3">
        <svg-smiling-heart class="img" />
        <div class="text">
          Optez pour <strong>la seule solution locale</strong> d’autopartage entre voisin-e-s.
        </div>
      </div>

      <div class="swiping-card" v-show="currentSlide == 4">
        <svg-biking class="img" />
        <div class="text">
          <strong>Économisez</strong> grâce à ce projet citoyen à but non-lucratif.
        </div>
      </div>

      <b-icon
        class="nextSlide-btn"
        v-on:click="nextSlide()"
        v-if="currentSlide < 4"
        icon="arrow-right-circle-fill"
      ></b-icon>

      <b-btn v-else variant="primary" href="/app">
        J'embarque!
      </b-btn>
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
import ProfileForm from "@/components/Profile/ProfileForm.vue";

import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import FormMixin from "@/mixins/FormMixin";

import { extractErrors } from "@/helpers";

import SmilingHeart from "@/assets/svg/smiling-heart.svg";
import Driving from "@/assets/svg/driving.svg";
import Lend from "@/assets/svg/home-lend.svg";
import Biking from "@/assets/svg/biking.svg";

export default {
  name: "RegisterStep",
  mixins: [Authenticated, FormLabelsMixin, FormMixin, Notification, UserMixin],
  components: {
    CommunityProofForm,
    FormsValidatedInput,
    ProfileForm,
    "svg-driving": Driving,
    "svg-lend": Lend,
    "svg-smiling-heart": SmilingHeart,
    "svg-biking": Biking,
  },
  data() {
    return { currentSlide: 1 };
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
    nextSlide() {
      this.currentSlide += 1;
    },
    async submitAndReload() {
      try {
        await this.submit();

        this.$store.commit("user", this.item);

        if (!this.item.communities || this.item.communities.length === 0) {
          this.$store.commit("addNotification", {
            content: "Il est temps de rejoindre un quartier!",
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

  &__reasons-why {
    text-align: center;
    h2 {
      margin-bottom: 30px;
    }
    .swiping-card {
      margin: 20px 0;
      text-align: center;
      .img {
        width: 330px;
      }
      .text {
        font-size: 22px;
        margin: 30px 20px 0 20px;
        text-align: center;
      }
    }
    .nextSlide-btn {
      cursor: pointer;
      font-size: 30px;
    }
  }
}
</style>
