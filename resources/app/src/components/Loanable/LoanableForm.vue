<template>
  <div class="loanable-form">
    <validation-observer ref="observer" v-slot="{ passes, valid }">
      <!--
        Submit is bound to buttons directly so as to prevent vue-cal to
        trigger a submit event for an unknown reason.
      -->
      <b-form :novalidate="true" class="form loanable-form__form" @submit.stop.prevent>
        <div class="form__section">
          <b-row>
            <b-col lg="12">
              <forms-validated-input
                name="name"
                :label="$t('fields.name') | capitalize"
                :rules="form.general.name.rules"
                type="text"
                :placeholder="placeholderOrLabel('name') | capitalize"
                :description="$t('descriptions.name')"
                :disabled="loanable.owner && loanable.owner.user.id !== user.id"
                disabled-tooltip="Seul le propriétaire peut changer cela"
                v-model="loanable.name"
              />
              <forms-validated-input
                v-if="loanable.owner"
                name="owner"
                :label="$t('fields.owner.user.full_name') | capitalize"
                type="text"
                :disabled="true"
                v-model="loanable.owner.user.full_name"
              />
              <forms-validated-input
                v-if="loanable.community"
                name="community"
                :label="$t('fields.community_name') | capitalize"
                type="text"
                :disabled="true"
                v-model="loanable.community.name"
              />
            </b-col>

            <b-col lg="4">
              <forms-validated-input
                name="type"
                :label="$t('fields.type') | capitalize"
                :rules="form.general.type.rules"
                type="select"
                :options="typeOptions"
                :placeholder="placeholderOrLabel('type') | capitalize"
                :disabled="!!loanable.id"
                hidden
                disabled-tooltip="On ne peut pas changer le type d'un véhicule existant."
                v-model="loanable.type"
              />
            </b-col>
          </b-row>

          <b-row>
            <b-col lg="4">
              <forms-image-uploader
                field="image"
                :label="$t('fields.image') | capitalize"
                :preview-aspect-ratio="'16 / 10'"
                :description="$t('descriptions.image')"
                v-model="loanable.image"
              />
            </b-col>

            <b-col lg="8">
              <forms-validated-input
                name="comments"
                :description="$t('descriptions.comments')"
                :rules="form.general.comments.rules"
                :label="$t('fields.comments') | capitalize"
                type="textarea"
                :placeholder="placeholderOrLabel('comments') | capitalize"
                v-model="loanable.comments"
              />
            </b-col>
          </b-row>
        </div>

        <form-section
          v-if="loanable.type === 'bike'"
          toggleable
          class="mt-2"
          section-title="Détails du vélo"
          :inititally-visible="false"
        >
          <forms-builder :definition="form.bike" v-model="loanable" entity="bikes" />
        </form-section>
        <form-section
          v-else-if="loanable.type === 'car'"
          toggleable
          class="mt-2"
          section-title="Détails de la voiture"
          :inititally-visible="false"
        >
          <forms-builder :definition="carForm" v-model="loanable" entity="cars">
            <!-- remove unused parameters -->
            <template v-slot:year_of_circulation></template>
            <template v-slot:plate_number></template>
            <template v-slot:is_value_over_fifty_thousand></template>
            <template v-slot:report_template>
              <b-form-group>
                <a href="/fiche_etat_du_vehicule.pdf" download>
                  {{ $i18n.t("fields.report_download") }} <b-icon icon="download" />
                </a>
              </b-form-group>
            </template>
          </forms-builder>
        </form-section>
        <form-section
          v-else-if="loanable.type === 'trailer'"
          toggleable
          class="mt-2"
          section-title="Détails de la remorque"
          :inititally-visible="false"
        >
          <forms-builder :definition="form.trailer" v-model="loanable" entity="trailers" />
        </form-section>
        <div class="form__section text-center" v-else>
          <span> Sélectionnez un type de véhicule pour poursuivre la configuration. </span>
        </div>

        <form-section
          v-if="loanable.id"
          toggleable
          class="mt-2"
          section-title="Droits de gestion"
          :inititally-visible="false"
        >
          <coowners-form :loanable="loanable" />
        </form-section>

        <form-section
          v-if="loanable.id"
          toggleable
          class="mt-2"
          section-title="Partage des coûts"
          :inititally-visible="false"
        >
          <b-row>
            <b-col lg="6">
              <forms-builder :definition="form.costs" v-model="loanable" entity="cars"></forms-builder>
            </b-col>
            <b-col lg="6">
              <loanable-balance v-if="loanable.type == 'car' && loanable.balance" :loanable="loanable" />
            </b-col>
          </b-row>
        </form-section>

        <form-section
          v-if="loanable.id"
          toggleable
          class="mt-2"
          section-title="Disponibilités"
          :inititally-visible="false"
        >
          <loanable-availability-rules v-if="loanable.events" :changed="changed" :loanable="loanable" :loading="loading" />
        </form-section>


        <div class="form__buttons text-end" v-if="!hideButtons">
          <slot />
          <b-button-group v-if="showReset">
            <b-button
              variant="success"
              @click="checkInvalidThenSubmit(passes)"
              :disabled="!changed || loading"
            >
              {{ $t("enregistrer") | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed || loading" @click="$emit('reset')">
              {{ $t("réinitialiser") | capitalize }}
            </b-button>
          </b-button-group>
          <b-button
            variant="primary"
            @click="checkInvalidThenSubmit(passes)"
            v-else
            :disabled="!changed || loading"
          >
            {{ ($route.name.match('register-') ? $t("suivant") : $t("enregistrer")) | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LoanableAvailabilityRules from "@/components/Loanable/AvailabilityRules.vue";
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import LoanableBalance from "@/components/Loanable/Balance.vue";
import CoownersForm from "@/components/Loanable/CoownersForm.vue";
import FormSection from "@/components/Loanable/FormSection.vue";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import UserMixin from "@/mixins/UserMixin";

import locales from "@/locales";

export default {
  name: "LoanableForm",
  mixins: [UserMixin, FormLabelsMixin],
  components: {
    FormsBuilder,
    FormsImageUploader,
    FormsValidatedInput,
    LoanableAvailabilityRules,
    LoanableBalance,
    CoownersForm,
    FormSection,
  },
  props: {
    center: {
      type: Object,
      required: true,
    },
    form: {
      type: Object,
      required: false,
      default() {
        return null;
      },
    },
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    hideButtons: {
      type: Boolean,
      required: false,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    showReset: {
      type: Boolean,
      required: false,
      default: false,
    },
    loanable: {
      type: Object,
      required: true,
    },
  },
  methods: {
    polygonOptions(type) {
      switch (type) {
        case "borough":
          return {
            clickable: false,
            fillColor: "#16a59e",
            fillOpacity: 0.15,
            strokeColor: "#16a59e",
            strokeOpacity: 0.35,
          };
        case "neighborhood":
        default:
          return {
            clickable: false,
            fillColor: "#16a59e",
            fillOpacity: 0.25,
            strokeOpacity: 0,
          };
      }
    },
    async checkInvalidThenSubmit(passes) {
      await passes(this.submit);

      const invalidItems = document.getElementsByClassName("is-invalid");
      if (invalidItems.length > 0) {
        const collapse = invalidItems[0].closest(".collapse")
        if(collapse && !collapse.classList.contains('show')) {
          // open the collapsed section
          this.$root.$emit('bv::toggle::collapse', collapse.id)
          setTimeout(() => {
            // scroll to show the first invalid element
            invalidItems[0].scrollIntoView({
              behavior: "smooth",
            });
          }, 100)
        } else {
          // scroll to show the first invalid element
          invalidItems[0].scrollIntoView({
            behavior: "smooth",
          });
        }
      }
    },
    async submit(...params) {
      if( !this.loanable.owner ) {
        let ownerId;
        if (this.user.owner) {
          ownerId = this.user.owner.id;
        } else {
          await this.$store.dispatch("users/update", {
            id: this.user.id,
            data: {
              id: this.user.id,
              owner: {},
            },
            params: {
              fields: "owner.id,full_name,avatar",
            },
          });
        }
        ownerId = this.user.owner.id;

        this.$store.commit("loanables/mergeItem", { owner: { id: ownerId, user: this.user } });
      }
      this.$emit("submit", ...params);
    },
  },
  computed: {
    hasBoroughs() {
      return this.loanableBoroughs.length > 0;
    },
    loanableBoroughs() {
      return this.loanableCommunities.map((c) => c.parent).filter((c) => !!c);
    },
    loanableBoroughsMessage() {
      return this.loanableBoroughs
        .map((b) => b.name)
        .filter((item, i, arr) => arr.indexOf(item) === i)
        .join(", ");
    },
    loanableCommunities() {
      if (this.loanable.community) {
        return [this.loanable.community];
      }

      return this.user.communities;
    },
    loanablePolygons() {
      return this.loanableCommunities.map((c) => ({
        ...c,
        options: this.polygonOptions(c.type),
      }));
    },
    carForm() {
      const carKeys = Object.keys(this.form.car);

      const form = {};

      // we add all the car form property and our custom report_template property at the right time, see https://262.ecma-international.org/6.0/#sec-ordinary-object-internal-methods-and-internal-slots-ownpropertykeys
      for (let i = 0; i < carKeys.length; i++) {
        const key = carKeys[i];
        form[key] = this.form.car[key];

        // make car properties readonly for non-owners
        form[key].disabled = this.loanable.owner && this.loanable.owner.user.id !== this.user.id;
        form[key].disabledTooltip = "Seul le propriétaire peut changer cela";

        if (key === "report") {
          form["report_template"] = {};
          form[key].disabled = false;
        }
      }

      return form;
    },
    typeOptions() {
      return [
        {
          text: "Types",
          disabled: true,
          value: null,
        },
        ...this.form.general.type.options,
      ];
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
      },
    },
  },
};
</script>
