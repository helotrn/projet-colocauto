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
      <h2>Ravi de vous rencontrer</h2>

      <h3>Remplissez votre profil de membre pour faciliter la rencontre avec vos voisin-e-s.</h3>
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
              :label="($t('users.fields.accept_conditions') + '*') | capitalize"
              :rules="form.general.accept_conditions.rules"
              type="checkbox"
              :placeholder="placeholderOrLabel('accept_conditions', 'users') | capitalize"
              v-model="item.accept_conditions"
            />
            <forms-validated-input
              name="gdpr"
              :label="($t('users.fields.gdpr') + '*') | capitalize"
              :rules="form.general.gdpr.rules"
              type="checkbox"
              :placeholder="placeholderOrLabel('gdpr', 'users') | capitalize"
              v-model="item.gdpr"
            />
            <forms-validated-input
              name="newsletter"
              :label="$t('users.fields.newsletter') | capitalize"
              :rules="form.general.newsletter.rules"
              type="checkbox"
              :placeholder="placeholderOrLabel('newsletter', 'users') | capitalize"
              v-model="item.newsletter"
            />
          </b-col>
        </b-row>
      </profile-form>
      <layout-loading v-else />
    </div>

    <layout-loading v-if="!item && currentPage == 2" />

    <div v-if="currentPage == 3" class="register-step__community">
      <div class="headers text-center">
        <h4>Afin que Coloc'Auto reste un service entre voisin.e.s sécuritaire.</h4>
        <h2>Veuillez téléverser une preuve de résidence pour {{ mainCommunity.name }}</h2>
      </div>
      <div v-if="item && item.communities">
        <community-proof-form
          v-for="community in item.communities"
          :key="community.id"
          :community="community"
          :loading="proofLoading"
          skip="/register/4"
          @submit="submitCommunityProof"
        />
      </div>
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 4" class="register-step__reasons-why">
      <h2>Bienvenue à bord</h2>

      <div class="swiping-card" v-show="currentSlide == 1">
        <svg-driving class="img" />
        <div class="text">
          En route vers le partage de voiture entre voisin.e.s
        </div>
      </div>

      <b-btn variant="primary" to="/app" v-on:click="forcePageRefresh()"> J'embarque !</b-btn>
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
    return { currentSlide: 1, currrentPage: 2, proofLoading: false };
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        // Has not finalized his account creation
        if (!vm.isRegistered) {
          if (vm.$route.path !== "/register/2") {
            vm.$router.replace("/register/2");
          }
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
  },
  methods: {
    nextSlide() {
      this.currentSlide += 1;
    },
    forcePageRefresh() {
      // Hack to get the dashboard to refresh with the latest UserMixin
      window.location.reload();
    },
    async submitAndReload() {
      try {
        await this.submit(false);
        this.$store.commit("user", this.item);

        // Skip "Submit proof of residency" third step
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
    },
    async submitCommunityProof() {
      try {
        this.proofLoading = true;
        // File attached
        await this.submit(false);
        // Go to the on-boarding slides
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
      } finally {
        this.proofLoading = false;
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

  &__title {
    text-align: center;
  }

  h2,
  h3 {
    text-align: center;
  }

  h2 {
    margin-top: 30px;
    font-size: 26px;
    font-weight: 600;
  }

  h3 {
    color: grey;
    font-size: 20px;
    margin: 20px 0;
    font-weight: 400;
  }

  .community-proof-form {
    margin-bottom: 2em;
  }

  &__completed__button {
    text-align: center;
  }

  &__reasons-why {
    text-align: center;
    height: 460px;
    margin-top: 40px;
    h2 {
      margin-bottom: 30px;
    }
    .swiping-card {
      margin: 20px 0;
      text-align: center;
      .img {
        max-height: 280px;
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

  &__community {
    .headers {
      margin: 20px 0;
      h4 {
        color: grey;
        font-size: 16px;
      }
    }
  }
}
</style>
