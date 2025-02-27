<template>
  <b-container fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $tc("véhicule", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form class="form" @submit.prevent="checkInvalidThenSubmit(passes)">
            <div class="form__section main_section">
              <h2>Informations</h2>

              <forms-builder :definition="form.general" v-model="item" entity="loanables">
                <!-- remove unused parameters -->
                <template v-slot:share_with_parent_communities></template>
                <template v-slot:is_self_service></template>
                <template v-slot:location_description></template>
                <template v-slot:instructions></template>
                <template v-slot:padlock_id></template>
                <template v-slot:community_id="{ def, item, property }">
                  <validation-provider
                    class="forms-validated-input"
                    mode="eager"
                    name="community_id"
                    :rules="def.rules"
                    v-slot="validationContext"
                  >
                    <b-form-group
                      :label="$t('fields.community_id') | capitalize"
                      label-for="community_id"
                      :description="$t('descriptions.community_id')"
                      :invalid-feedback="getInvalidFeedback(validationContext)"
                      :state="getValidationState(validationContext)"
                      v-b-tooltip.hover
                      class="input-and-button long-description"
                    >
                      <forms-relation-input
                        id="community_id"
                        name="community_id"
                        :query="form.general.community_id.query"
                        :placeholder="$t('fields.community_id') | capitalize"
                        :disabled="form.general.community_id.disabled"
                        :state="getValidationState(validationContext)"
                        :object-value="item.community"
                        :value="item.community_id"
                        @input="setLoanableCommunity"
                      />
                      <b-button
                        size="sm"
                        variant="success"
                        @click="viewCommunity(item.community)"
                        :disabled="!item.community"
                      >
                        Voir la communauté
                      </b-button>
                    </b-form-group>
                  </validation-provider>
                </template>

                <template v-slot:owner_id="{ def, item, property }">
                  <validation-provider
                    class="forms-validated-input"
                    mode="eager"
                    name="owner_id"
                    v-slot="validationContext"
                  >
                    <b-form-group label="Propriétaire" label-for="owner_id" class="input-and-button">
                      <forms-relation-input
                        id="owner_id"
                        name="owner_id"
                        :query="ownerQuery"
                        placeholder="Propriétaire"
                        :object-value="itemOwner"
                        :value="item.owner_id"
                        @input="setLoanableOwner"
                      />
                      <b-button
                        size="sm"
                        variant="success"
                        @click="viewUser(itemOwner)"
                        :disabled="!itemOwner"
                      >
                        Voir le profil
                      </b-button>
                      <b-form-invalid-feedback :state="getValidationState(validationContext)">
                        {{ validationContext.errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </template>

              </forms-builder>
              <b-alert variant="warning" v-if="item.owner && item.community && item.community.users && !ownerIsPartOfCommunity" show>
                Attention {{ item.owner.user.full_name }} n'est pas dans la communauté {{ item.community.name }}. Le véhicule ne sera pas visible.
              </b-alert>
            </div>

            <form-section
              v-if="item.type === 'car'"
              toggleable
              class="mt-2"
              section-title="Détails de la voiture"
              :inititally-visible="!item.id"
            >
              <forms-builder :definition="carForm" v-model="item" entity="cars">
                <!-- remove unused parameters -->
                <template v-slot:year_of_circulation></template>
                <template v-slot:plate_number></template>
                <template v-slot:is_value_over_fifty_thousand></template>
                <template v-slot:report_template>
                  <b-form-group>
                    <a href="/fiche_etat_du_vehicule.pdf" download>
                      {{ $i18n.t("cars.fields.report_download") }} <b-icon icon="download" />
                    </a>
                  </b-form-group>
                </template>
              </forms-builder>
            </form-section>

            <div class="form__section">
              <h2 id="reports">État du véhicule</h2>
              <div v-if="lastReportDate" class="mb-4">
                <small>Date de dernière modification : {{lastReportDate | datetime}}</small>
              </div>
              <form-section
                v-for="location in reportLocations"
                :key="location.slug"
                toggleable
                class="mt-2"
                :section-title="location.label"
                :header-level="3"
                :inititally-visible="false"
              >
                <report-form
                  :report="lastReportOfLocation(location.slug)"
                  @update="updateReport"
                />
              </form-section>
            </div>

            <form-section
              v-if="item.owner && item.owner.user"
              toggleable
              class="mt-2"
              section-title="Droits de gestion"
              :inititally-visible="false"
            >
              <coowners-form :loanable="item" />
            </form-section>

            <form-section
              toggleable
              class="mt-2"
              section-title="Partage des coûts"
              :inititally-visible="false"
            >
              <b-row>
                <b-col lg="6">
                  <forms-builder :definition="form.costs" v-model="item" entity="cars"></forms-builder>
                </b-col>
                <b-col lg="6">
                  <loanable-balance v-if="item.balance" :loanable="item" />
                </b-col>
              </b-row>
            </form-section>

            <form-section
              v-if="item.type && item.id"
              toggleable
              class="mt-2"
              section-title="Disponibilités"
              :inititally-visible="false"
            >
              <loanable-availability-rules :changed="changed" :loanable="item" :loading="loading" />
            </form-section>

            <div class="form__buttons">
              <b-button-group>
                <b-button variant="success" type="submit" :disabled="!changed || loading">
                  Sauvegarder
                </b-button>
                <b-button type="reset" :disabled="!changed || loading" @click="reset">
                  Réinitialiser
                </b-button>
              </b-button-group>
            </div>
          </b-form>
        </validation-observer>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import FormsBuilder from "@/components/Forms/Builder.vue";
import FormsRelationInput from "@/components/Forms/RelationInput.vue";
import LoanableAvailabilityRules from "@/components/Loanable/AvailabilityRules.vue";
import LoanableBalance from "@/components/Loanable/Balance.vue";
import CoownersForm from "@/components/Loanable/CoownersForm.vue";
import FormSection from "@/components/Loanable/FormSection.vue";
import ReportForm from "@/components/Loanable/ReportForm.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import locales from "@/locales";

import { capitalize } from "@/helpers/filters";

export default {
  name: "AdminLoanable",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
    FormsRelationInput,
    FormsValidatedInput,
    LoanableAvailabilityRules,
    LoanableBalance,
    CoownersForm,
    FormSection,
    ReportForm,
  },
  data() {
    return {
      ownerQuery: {
        slug: "users",
        value: "owner.id",
        text: "full_name",
        params: {
          fields: "full_name, avatar, owner.id",
        },
      },
    };
  },
  computed: {
    itemOwner() {
      if (this.item.owner) {
        return {
          ...this.item.owner.user,
          owner: {
            id: this.item.owner.id,
          },
        };
      }

      return null;
    },
    ownerIsPartOfCommunity() {
      if( this.item.owner && this.item.community ){
        return this.item.community.users.map(u => u.id).includes(this.item.owner.user.id);
      } else {
        return false;
      }
    },
    fullTitle() {
      const parts = [
        "Coloc'Auto",
        capitalize(this.$i18n.t("titles.admin")),
        capitalize(this.$i18n.tc("véhicule", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    hasBoroughs() {
      return this.loanableBoroughs.length > 0;
    },
    loanableBoroughs() {
      if (this.item.community) {
        if (this.item.community.parent) {
          return [this.item.community.parent];
        }

        return [];
      }

      if (this.item.owner?.user?.communities) {
        return this.item.owner.user.communities.filter((c) => !!c.parent).map((c) => c.parent);
      }

      return [];
    },
    loanableBoroughsMessage() {
      return this.loanableBoroughs
        .map((b) => b.name)
        .filter((item, i, arr) => arr.indexOf(item) === i)
        .join(", ");
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("véhicule", 1));
    },
    carForm() {
      const carKeys = Object.keys(this.form.car);

      const form = {};

      // we add all the car form property and our custom report_template property at the right time, see https://262.ecma-international.org/6.0/#sec-ordinary-object-internal-methods-and-internal-slots-ownpropertykeys
      for (let i = 0; i < carKeys.length; i++) {
        const key = carKeys[i];
        form[key] = this.form.car[key];

        if (key === "report") {
          form["report_template"] = {};
          form[key].disabled = false;
        }
      }

      return form;
    },
    reportLocations() {
      return Object.entries(locales.fr.reports.location.label).map(l => ({
        slug: l[0],
        label: l[1]
      }))
    },
    lastReportDate() {
      if( !this.item.reports ) return null;
      let reports = this.item.reports
        .toSorted((a,b) => a.updated_at < b.updated_at ? -1 : 1)
      if( reports.length ) return reports[0].updated_at
      else return null
    },
  },
  methods: {
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
    async setLoanableOwner(user) {
      if(!user) {
        this.$store.commit("loanables/patchItem", {
          owner: null,
          owner_id: null,
        });
        return;
      } else if (!user.owner) {
        await this.$store.dispatch("users/update", {
          id: user.id,
          data: {
            id: user.id,
            owner: {},
          },
          params: {
            fields: "owner.id,full_name,avatar",
          },
        });

        const updatedUser = this.$store.state.users.item;

        this.$store.commit("loanables/patchItem", {
          owner: {
            id: updatedUser.owner.id,
            user: {
              id: updatedUser.id,
              full_name: updatedUser.full_name,
              avatar: updatedUser.avatar,
            },
          },
          owner_id: updatedUser.owner_id,
        });

        return;
      }

      this.$store.commit("loanables/patchItem", {
        owner: {
          id: user.owner.id,
          user: {
            id: user.id,
            full_name: user.full_name,
            avatar: user.avatar,
          },
        },
        owner_id: user.owner_id,
      });
    },
    viewUser(owner){
      if( owner ) this.$router.push(`/admin/users/${owner.id}`);
    },
    viewCommunity(community){
      if( community ) this.$router.push(`/admin/communities/${community.id}`);
    },
    setLoanableCommunity(selection) {
      this.item.community = selection
      if (!selection) {
        this.item.community_id = null;
      } else {
        this.item.community_id = selection.id;
      }
    },
    getValidationState({ dirty, validated, valid = null }) {
      if (this.rulesOrNothing === "") {
        return null;
      }

      if (dirty && !validated) {
        return null;
      }

      return validated ? valid : null;
    },
    getInvalidFeedback({errors}){
      if( errors ) {
        return errors.map(e => e.replace('community_id', this.$t('fields.community_id'))).join(', ')
      }
    },
    lastReportOfLocation(location) {
      let reportsOfLocation;
      if( !this.item.reports ) {
        reportsOfLocation = [];
      } else {
        reportsOfLocation = this.item.reports
          .filter(r => r.location == location)
          .toSorted((a,b) => a.updated_at < b.updated_at ? -1 : 1)
      }
      if( reportsOfLocation.length ) return reportsOfLocation[0]
      else return {
        location,
        scratch: false,
        bumps: false,
        stain: false,
        details: null,
        loanable_id: this.item.id,
        pictures: [],
      }
    },
    updateReport(report) {
      let reports = [...this.$store.state.loanables.item.reports]
      if( report.id ){
        // update existing report
        reports = reports.map(r => r.id == report.id ? ({...r, ...report}) : r)
      } else {
        // create a new report
        reports.push({
          ...report,
          id: 'new'+this._uid
        })
      }
      this.$store.commit("loanables/patchItem", {reports});
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
        cars: locales.en.cars,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
        cars: locales.fr.cars,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style scoped lang="scss">
.main_section {
  border-bottom: none;
  padding: 1.25rem;
  background: white;
  border-radius: 10px;
}
::v-deep label {
  font-weight: 600;
}
::v-deep .forms-builder__field {
  .input-and-button {
    & > div {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .forms-relation-input {
      flex-grow: 1;
      margin-right: 1em;
    }
  }
}
</style>
