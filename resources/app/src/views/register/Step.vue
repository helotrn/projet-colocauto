<template>
  <div class="register-step box">

    <div v-if="item && currentPage == 2">
      <h2>{{ $t("components.register.registerform.register") }}</h2>

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
        } else {
          return vm.skipToApp();
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
