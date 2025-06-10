<template>
  <b-container fluid v-if="item && routeDataLoaded">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $tc("model_name", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row v-if="item.changes && item.changes.length">
      <b-col class="mb-3">
        <h2>Historique des modifications</h2>
        <b-button block v-b-toggle.changes variant="outline bg-white border d-flex justify-content-between align-items-center text-left">
          {{item.changes.length}} modification{{item.changes.length > 1 ? 's' : ''}} effectuée{{item.changes.length > 1 ? 's' : ''}}
          <b-icon icon="chevron-down" />
        </b-button>
        <b-collapse id="changes" class="mb-4">
          <b-card>
            <p v-for="change in item.changes" :key="change.id" class="card-text border-bottom pb-3">
              <!-- split several lines descriptions -->
              <template v-for="description in change.description.split(',')">
                <!-- replace => with a SVG arrow -->
                <span>{{ description.split('=>')[0] }}</span>
                <template v-if="description.split('=>').length > 1">
                  <arrow-right />
                  <span>{{ description.split('=>')[1] }}</span>
                </template>
                <br/>
              </template>
              <small class="d-flex justify-content-between">
                <span>par {{ change.user.full_name }}</span>
                <span>le {{ change.created_at | date }}</span>
              </small>
            </p>
          </b-card>
        </b-collapse>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder :definition="form" v-model="item" entity="refunds" />

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed || loading">
                Sauvegarder
              </b-button>
              <b-button v-if="!item.id" type="reset" :disabled="!changed" @click="reset"> Réinitialiser </b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";
import ArrowRight from "@/assets/svg/arrow-right.svg";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import locales from "@/locales";

export default {
  name: "AdminInvitation",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
    ArrowRight,
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.refunds,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.refunds,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss" scoped>
  .not-collapsed > svg {
    transform: rotate(180deg);
  }
  .collapse .card-text:last-child {
    border-bottom: none!important;
    padding-bottom: 0!important;
  }
  .collapse .card {
    border-top-right-radius: 0;
    border-top-left-radius: 0;
  }
</style>
