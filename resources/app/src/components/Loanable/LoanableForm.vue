<template>
  <div class="loanable-form">
    <validation-observer ref="observer" v-slot="{ passes, valid }">
      <b-form
        :novalidate="true"
        class="form loanable-form__form"
        @submit.stop.prevent="checkInvalidThenSubmit(passes, valid)"
      >
        <div class="form__section">
          <b-row>
            <b-col lg="8">
              <forms-validated-input
                name="name"
                :label="$t('fields.name') | capitalize"
                :rules="form.general.name.rules"
                type="text"
                :placeholder="placeholderOrLabel('name') | capitalize"
                :description="$t('descriptions.name')"
                v-model="loanable.name"
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
                disabled-tooltip="On ne peut pas changer le type d'un véhicule existant."
                v-model="loanable.type"
              />
            </b-col>
          </b-row>

          <b-row>
            <b-col lg="8">
              <forms-validated-input
                name="position"
                :description="form.general.position.description"
                :rules="form.general.position.rules"
                :center="center"
                :label="$t('fields.position') | capitalize"
                type="point"
                :placeholder="placeholderOrLabel('position') | capitalize"
                :polygons="loanablePolygons"
                v-model="loanable.position"
              />
            </b-col>

            <b-col>
              <forms-validated-input
                name="location_description"
                :description="$t('descriptions.location_description')"
                :rules="form.general.location_description.rules"
                :rows="12"
                :label="$t('fields.location_description') | capitalize"
                type="textarea"
                :placeholder="placeholderOrLabel('location_description') | capitalize"
                v-model="loanable.location_description"
              />
            </b-col>
          </b-row>

          <b-row>
            <b-col lg="4">
              <forms-image-uploader
                field="image"
                :label="$t('fields.image') | capitalize"
                v-model="loanable.image"
              />
            </b-col>

            <b-col lg="8">
              <forms-validated-input
                name="comments"
                :rules="form.general.comments.rules"
                :label="$t('fields.comments') | capitalize"
                type="textarea"
                :placeholder="placeholderOrLabel('comments') | capitalize"
                v-model="loanable.comments"
              />

              <forms-validated-input
                name="instructions"
                :description="$t('descriptions.instructions')"
                :rules="form.general.instructions.rules"
                :label="$t('fields.instructions') | capitalize"
                type="textarea"
                :placeholder="placeholderOrLabel('instructions') | capitalize"
                v-model="loanable.instructions"
              />
            </b-col>
          </b-row>
        </div>

        <div
          class="form__section"
          v-if="!!loanable.type && (user.communities.length > 1 || loanableBoroughs.length > 0)"
        >
          <h2>Accessibilité</h2>

          <forms-validated-input
            v-if="user.communities.length > 1"
            :description="$t('descriptions.community_id')"
            :label="$t('fields.community_id') | capitalize"
            name="community_id"
            type="relation"
            v-bind="form.general.community_id"
            :extra-params="{ 'users.id': 'me' }"
            :placeholder="placeholderOrLabel('community_id') | capitalize"
            @relation="loanable.community = $event"
            v-model="loanable.community_id"
          />

          <forms-validated-input
            v-if="loanableBoroughs.length > 0"
            :description="$t('descriptions.share_with_parent_communities')"
            :label="
              $t('fields.share_with_parent_communities_dynamic', {
                shared_with: loanableBoroughsMessage,
              }) | capitalize
            "
            name="share_with_parent_communities"
            type="checkbox"
            v-model="loanable.share_with_parent_communities"
          />
        </div>

        <div class="form__section" v-if="loanable.type === 'bike'">
          <h2>Détails du vélo</h2>

          <forms-builder :definition="form.bike" v-model="loanable" entity="bikes" />
        </div>
        <div class="form__section" v-else-if="loanable.type === 'car'">
          <h2>Détails de la voiture</h2>

          <forms-builder :definition="form.car" v-model="loanable" entity="cars" />
        </div>
        <div class="form__section" v-else-if="loanable.type === 'trailer'">
          <h2>Détails de la remorque</h2>

          <forms-builder :definition="form.trailer" v-model="loanable" entity="trailers" />
        </div>
        <div class="form__section text-center" v-else>
          <span> Sélectionnez un type de véhicule pour poursuivre la configuration. </span>
        </div>

        <div class="form__section" v-if="loanable.type && loanable.id">
          <a id="availability" />
          <loanable-availability-calendar
            :changed="changed"
            :loanable="loanable"
            :loading="loading"
          />
        </div>

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="success" type="submit" :disabled="!changed || loading">
              {{ $t("enregistrer") | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed || loading" @click="$emit('reset')">
              {{ $t("réinitialiser") | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" v-else :disabled="!changed || loading">
            {{ $t("enregistrer") | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LoanableAvailabilityCalendar from "@/components/Loanable/AvailabilityCalendar.vue";
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";

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
    LoanableAvailabilityCalendar,
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
    checkInvalidThenSubmit(passes, isValid) {
      passes().then(() => {
        if (isValid) {
          this.submit();
        } else {
          const invalidItems = document.getElementsByClassName("is-invalid");
          if (invalidItems.length > 0) {
            invalidItems[0].scrollIntoView({
              behavior: "smooth",
            });
          }
        }
      });
    },
    submit(...params) {
      const ownerId = this.$store.state.user.owner.id;
      this.$store.commit("loanables/mergeItem", { owner: { id: ownerId } });
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

<style lang="scss"></style>
