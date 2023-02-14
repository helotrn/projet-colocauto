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
          <b-form class="form" @submit.prevent="passes(submit)">
            <div class="form__section">
              <h2>Informations</h2>

              <forms-builder :definition="form.general" v-model="item" entity="loanables">
                <!-- remove unused parameters -->
                <template v-slot:share_with_parent_communities></template>
                <template v-slot:is_self_service></template>
                <template v-slot:location_description></template>
                <template v-slot:instructions></template>
                <template v-slot:padlock_id></template>

                <template v-slot:owner_id="{ def, item, property }">
                  <validation-provider
                    class="forms-validated-input"
                    mode="eager"
                    name="owner_id"
                    v-slot="validationContext"
                  >
                    <b-form-group label="Propriétaire" label-for="owner_id">
                      <forms-relation-input
                        id="owner_id"
                        name="owner_id"
                        :query="ownerQuery"
                        placeholder="Propriétaire"
                        :object-value="itemOwner"
                        :value="item.owner_id"
                        @input="setLoanableOwner"
                      />
                    </b-form-group>
                  </validation-provider>
                </template>
              </forms-builder>
            </div>

            <div class="form__section" v-if="item.type === 'car'">
              <h2>Détails de la voiture</h2>

              <forms-builder :definition="form.car" v-model="item" entity="cars">
                <!-- remove unused parameters -->
                <template v-slot:year_of_circulation></template>
                <template v-slot:plate_number></template>
                <template v-slot:is_value_over_fifty_thousand></template>
              </forms-builder>
            </div>

            <div class="form__section">
              <h2>Partage des coûts</h2>

              <forms-builder :definition="form.costs" v-model="item" entity="cars">
                
              </forms-builder>
            </div>

            <div class="form__section" v-if="item.type && item.id">
              <loanable-availability-rules :changed="changed" :loanable="item" :loading="loading" />
            </div>

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
  },
  data() {
    return {
      ownerQuery: {
        slug: "users",
        value: "owner.id",
        text: "full_name",
        params: {
          fields: "full_name, owner.id",
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
  },
  methods: {
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
            fields: "owner.id,full_name",
          },
        });

        const updatedUser = this.$store.state.users.item;

        this.$store.commit("loanables/patchItem", {
          owner: {
            id: updatedUser.owner.id,
            user: {
              id: updatedUser.id,
              full_name: updatedUser.full_name,
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
          },
        },
        owner_id: user.owner_id,
      });
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

<style lang="scss"></style>
