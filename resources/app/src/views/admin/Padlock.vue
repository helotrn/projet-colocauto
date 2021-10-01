<template>
  <b-container fluid v-if="item && routeDataLoaded">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $tc("cadenas", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder
            :disabled="true"
            :definition="form"
            v-model="item"
            entity="padlocks"
          >
            <template v-slot:loanable_id>
              <b-form-group label="Véhicule">
                <div class="d-flex">
                  <b-input :disabled="true" :value="item.loanable.name">
                    aalo
                  </b-input>
                  <b-button
                    size="sm"
                    variant="success"
                    :to="`/admin/loanables/${item.loanable.id}`"
                  >
                    {{ $t("afficher") | capitalize }}
                  </b-button>
                </div>
              </b-form-group>
            </template>
          </forms-builder>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import locales from "@/locales";

export default {
  name: "AdminPadlock",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.padlocks,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.padlocks,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
