<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('communauté', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations générales</h2>

            <forms-builder :definition="formExceptArea" v-model="item" entity="communities" />
          </div>

          <div class="form__section">
            <h2>Zone géographique</h2>
            <b-form-group
              :description="`Zone géographique sous la forme d'une liste de tuples ` +
                '(latitude, longitude), un par ligne.'"
              label-for="area">
              <b-form-textarea
                id="area" name="area"
                v-model="area"
                rows="6" max-rows="12" />
            </b-form-group>
          </div>

          <div class="form__section">
            <h2>Tarifications</h2>

            <pricing-language-definition />

            <div v-for="(pricing, index) in item.pricings"
              :key="pricing.id || `idx-${index}`">
              <pricing-form  :pricing="pricing"
                @remove="removePricing(pricing)"
                @change="updatePricing(pricing, $event)" />
            </div>

            <p class="form-inline" v-if="remainingPricingTypes.length > 0">
              Ajouter une tarification pour
              <b-select class="ml-1 mr-1"
                :options="remainingPricingTypes" v-model="newPricingType" />
              <b-button @click="addPricing">OK</b-button>
            </p>
          </div>

          <div class="form__section" v-if="item.id">
            <a id="members" />
            <b-row>
              <b-col md="8">
                <h2>Membres</h2>
              </b-col>

              <b-col md="4" class="text-right">
                <b-input v-model="userTableFilter" placeholder="Tapez pour filtrer..." />
              </b-col>
            </b-row>

            <b-table :busy="usersLoading"
              :filter="userTableFilter" empty-filtered-text="Pas de membre correspondant"
              :filter-function="localizedFilter(userTableFilterFields)"
              striped hover :items="users"
              sort-by="full_name" no-sort-reset
              :fields="userTable" sticky-header="500px"
              :show-empty="true" empty-text="Pas de membre">
              <template v-slot:table-busy>
                <span>Chargement...</span>
              </template>
              <template v-slot:cell(role)="row">
                <b-select :options="[
                  { value: null, text: 'Membre' },
                  { value: 'admin', text: 'Admin' }
                ]" :value="row.item.role" @change="setUserRole(row.item, $event)" />
              </template>
              <template v-slot:cell(approved_at)="row">
                <small class="muted" v-if="!row.item.approved_at">N/A</small>
                <span v-else>{{ row.item.approved_at | date }}</span>
              </template>
              <template v-slot:cell(suspended_at)="row">
                <small class="muted" v-if="!row.item.suspended_at">N/A</small>
                <span v-else>{{ row.item.suspended_at | date }}</span>
              </template>
              <template v-slot:cell(proof)="row">
                <span v-if="row.item.proof" class="admin-community__users__table__proof">
                  <a href="#" v-b-modal="`proof-${row.item.id}`">
                    {{ row.item.proof.original_filename }}
                  </a>

                  <b-modal size="xl"
                    :title="`Preuve de résidence (${row.item.full_name})`"
                    :id="`proof-${row.item.id}`" footer-class="d-none">
                    <img class="img-fit" :src="row.item.proof.url">
                  </b-modal>
                </span>
              </template>
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <div v-if="!row.item._new">
                    <b-button v-if="!row.item.approved_at" :disabled="usersLoading || loading"
                      size="sm" class="ml-1 mb-1" variant="primary"
                      @click="approveUser(row.item)">
                      {{ $t('approuver') | capitalize }}
                    </b-button>
                    <b-button v-else-if="!row.item.suspended_at" :disabled="usersLoading || loading"
                      size="sm" class="ml-1 mb-1" variant="warning"
                      @click="suspendUser(row.item)">
                      {{ $t('suspendre') | capitalize }}
                    </b-button>
                    <b-button v-else :disabled="usersLoading || loading"
                      size="sm" class="ml-1 mb-1" variant="success"
                      @click="unsuspendUser(row.item)">
                      {{ $t('rétablir') | capitalize }}
                    </b-button>
                  </div>

                  <b-button size="sm" variant="danger" class="ml-1 mb-1"
                    :disabled="usersLoading || loading" @click="removeUser(row.item)">
                    {{ $t('retirer') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>

            <forms-validated-input type="relation"
              name="community" label="Ajouter un membre"
              :value="null" reset-after-select
              :query="{
                slug: 'users',
                value: 'id',
                text: 'full_name',
                params: {
                  'fields': 'id,full_name',
                  '!id': users.map(u => u.id).join(','),
                },
              }" @relation="addUser" />
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
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';
import PricingForm from '@/components/Pricing/PricingForm.vue';
import PricingLanguageDefinition from '@/components/Pricing/LanguageDefinition.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import FormMixin from '@/mixins/FormMixin';
import DataRouteGuards from '@/mixins/DataRouteGuards';

import locales from '@/locales';

export default {
  name: 'AdminCommunity',
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
    FormsValidatedInput,
    PricingForm,
    PricingLanguageDefinition,
  },
  data() {
    return {
      newPricingType: null,
      userTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'full_name', label: 'Nom complet', sortable: true },
        { key: 'role', label: 'Rôle', sortable: true },
        { key: 'approved_at', label: 'Approuvé', sortable: true },
        { key: 'suspended_at', label: 'Suspendu', sortable: true },
        { key: 'proof', label: 'Preuve', sortable: false },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      userTableFilter: '',
      userTableFilterFields: ['full_name'],
    };
  },
  computed: {
    area: {
      get() {
        return this.item.area.join('\n');
      },
      set(area) {
        const newItem = {
          ...this.item,
          area: area.split('\n').map(t => t.split(',')),
        };
        this.$store.commit(`${this.slug}/item`, newItem);
      },
    },
    formExceptArea() {
      const form = { ...this.form };
      delete form.area;
      return form;
    },
    remainingPricingTypes() {
      const currentPricingTypes = this.item.pricings.map(p => p.object_type);
      return [
        {
          value: null,
          text: 'Générique',
        },
        {
          value: 'car',
          text: 'Voiture',
        },
        {
          value: 'bike',
          text: 'Vélo',
        },
        {
          value: 'trailer',
          text: 'Remorque',
        },
      ].filter(p => currentPricingTypes.indexOf(p.value) === -1);
    },
    users() {
      return this.$store.state.users.data.filter(() => true);
    },
    usersLoading() {
      return !!this.$store.state.users.ajax;
    },
  },
  methods: {
    addPricing() {
      this.$store.commit(`${this.slug}/mergeItem`, {
        pricings: [{
          object_type: this.newPricingType,
          rule: '',
          name: '',
        }],
      });
    },
    async addUser(user) {
      await this.$store.dispatch(`${this.slug}/addUser`, {
        id: this.item.id,
        data: {
          id: user.id,
        },
      });

      this.$store.state.communities.lastAjax.then(({ data }) => {
        this.$store.commit('users/addData', [data]);
      });
    },
    async approveUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find(c => c.id === this.item.id);
        community.approved_at = new Date();

        return data;
      });
    },
    localizedFilter(columns) {
      return (row, filter) => columns
        .map(c => row[c] || '')
        .join(',')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .match(new RegExp(filter.normalize('NFD').replace(/[\u0300-\u036f]/g, ''), 'i'));
    },
    removePricing(pricing) {
      const pricings = this.item.pricings.filter(p => p !== pricing);

      this.$store.commit(`${this.slug}/patchItem`, { pricings });
    },
    async removeUser(user) {
      await this.$store.dispatch(`${this.slug}/removeUser`, {
        id: this.item.id,
        userId: user.id,
      });

      this.$store.commit(
        'users/data',
        this.$store.state.users.data.filter(u => u.id !== user.id),
      );
    },
    async setUserRole(user, role) {
      await this.updateUser(user, u => ({
        ...u,
        role,
      }));
    },
    async suspendUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find(c => c.id === this.item.id);
        community.suspended_at = new Date();

        return data;
      });
    },
    async unsuspendUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find(c => c.id === this.item.id);
        community.suspended_at = null;

        return data;
      });
    },
    updatePricing(oldValue, newValue) {
      const pricings = [...this.item.pricings];
      const index = pricings.indexOf(oldValue);
      pricings.splice(index, 1, newValue);
      this.$store.commit(`${this.slug}/patchItem`, { pricings });
    },
    async updateUser(user, transform) {
      const data = transform(user);

      await this.$store.dispatch(`${this.slug}/updateUser`, {
        id: this.item.id,
        data,
        userId: user.id,
      });

      this.$store.state.communities.lastAjax.then(({ data: d }) => {
        const item = this.$store.state.users.data.find(u => u.id === d.id);
        const index = this.$store.state.users.data.indexOf(item);

        const newData = [
          ...this.$store.state.users.data,
        ];
        newData.splice(index, 1, {
          ...item,
          ...d,
          id: item.id,
        });

        this.$store.commit('users/data', newData);
      });
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.communities,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.communities,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
.admin-community__users__table__proof {
  display: inline-block;
  max-width: 100px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
