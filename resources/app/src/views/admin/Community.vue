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

            <b-card no-body variant="info" class="admin-community-form__language-definition">
              <template v-slot:header>
                <b-button size="lg" block href="#"
                  v-b-toggle.language-definition variant="transparent">
                  Définition du langage de tarification
                </b-button>
              </template>

              <b-collapse id="language-definition">
                <div class="card-body">
                  <p>
                    Une règle par ligne. La dernière règle ne doit pas être conditionnelle.
                  </p>

                  <p>
                    Une règle est de la forme <code>SI condition ALORS calcul</code> (forme dite
                    conditionnelle) ou <code>calcul</code> où<br>
                  </p>

                  <ul>
                    <li>
                      <code>condition</code> est de la forme <code>expression comparateur expression
                        (opérateur_logique condition)*</code>;
                    </li>

                    <li>
                      <code>expression</code> est un équation mathématique composée d'un ou
                      plusieurs variables, <code>opérateur</code> et de constantes;
                    </li>

                    <li>
                      <code>opérateur</code> est un opérateur mathématique;
                    </li>

                    <li>
                      <code>opérateur_logique</code> est un opérateur logique parmi
                      <code>ET OU</code>.
                    </li>
                  </ul>

                  <p>Les règles de priorité des opérateurs s'appliquent. Utilisez des parenthèses
                    pour forcer un ordre.</p>

                  <h4>Variables disponibles</h4>
                  <ul>
                    <li><code>$KM</code>, un entier représentant le kilométrage de l'emprunt;</li>
                    <li><code>$MINUTES</code>, un entier représentant la durée de l'emprunt en
                      minutes;</li>
                    <li><code>$OBJET</code>, une entité donnant accès à l'objet touché par la
                      tarification (non accessible pour la tarification générique).</li>
                  </ul>

                  <p>
                    En ce qui concerne <code>OBJET</code>, on peut accéder à ses propriétés avec
                    un point. Par exemple <code>OBJET.engine</code> pour le mode de combustion d'une
                    voiture ou <code>OBJET.size</code> pour la taille d'un vélo.
                  </p>
                </div>
              </b-collapse>
            </b-card>

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
            <h2>Membres</h2>

            <b-table
              striped hover :items="item.users"
              selectable select-mode="multi" @row-selected="userRowSelected"
              :fields="userTable" no-sort-reset
              :show-empty="true" empty-text="Pas de membres">
              <template v-slot:cell(role)="row">
                {{ (row.item.role || 'membre') | capitalize }}
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
                <span v-if="row.item.proof">
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
                  <b-button v-if="!row.item.approved_at"
                    size="sm" class="mr-1" variant="primary"
                    @click="approveUser(row.item)">
                    {{ $t('approuver') | capitalize }}
                  </b-button>
                  <b-button v-else-if="!row.item.suspended_at"
                    size="sm" class="mr-1" variant="warning"
                    @click="suspendUser(row.item)">
                    {{ $t('suspendre') | capitalize }}
                  </b-button>
                  <b-button v-else
                    size="sm" class="mr-1" variant="success"
                    @click="unsuspendUser(row.item)">
                    {{ $t('rétablir') | capitalize }}
                  </b-button>

                  <b-button size="sm" variant="danger"
                    @click="removeUser(row.item)">
                    {{ $t('retirer') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed">
                Sauvegarder
              </b-button>
              <b-button type="reset" :disabled="!changed" @click="reset">
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
import PricingForm from '@/components/Pricing/Form.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

export default {
  name: 'AdminCommunity',
  mixins: [FormMixin],
  components: {
    FormsBuilder,
    PricingForm,
  },
  data() {
    return {
      newPricingType: null,
      usersSelected: [],
      userTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'full_name', label: 'Nom complet', sortable: true },
        { key: 'role', label: 'Rôle', sortable: true },
        { key: 'approved_at', label: 'Approuvé', sortable: true },
        { key: 'suspended_at', label: 'Suspendu', sortable: true },
        { key: 'proof', label: 'Preuve', sortable: false },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
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
    approveUser(user) {
      const item = {
        ...this.item,
      };
      const index = item.users.indexOf(user);
      const approvedUser = {
        ...item.users[index],
        approved_at: new Date(),
      };
      item.users.splice(index, 1, approvedUser);

      this.$store.commit(`${this.slug}/item`, item);

      this.$store.dispatch(`${this.slug}/updateItem`, this.params);
    },
    removePricing(pricing) {
      const pricings = this.item.pricings.filter(p => p !== pricing);

      this.$store.commit(`${this.slug}/patchItem`, { pricings });
    },
    removeUser(user) {
      const users = this.item.users.filter(u => u !== user);

      this.$store.commit(`${this.slug}/patchItem`, { users });
    },
    suspendUser(user) {
      const item = {
        ...this.item,
      };
      const index = item.users.indexOf(user);
      const suspendedUser = {
        ...item.users[index],
        suspended_at: new Date(),
      };
      item.users.splice(index, 1, suspendedUser);

      this.$store.dispatch(`${this.slug}/update`, {
        id: item.id,
        data: {
          id: item.id,
          users: item.users,
        },
        params: this.params,
      });
    },
    unsuspendUser(user) {
      const item = {
        ...this.item,
      };
      const index = item.users.indexOf(user);
      const suspendedUser = {
        ...item.users[index],
        suspended_at: null,
      };
      item.users.splice(index, 1, suspendedUser);

      this.$store.dispatch(`${this.slug}/update`, {
        id: item.id,
        data: {
          id: item.id,
          users: item.users,
        },
        params: this.params,
      });
    },
    updatePricing(oldValue, newValue) {
      const pricings = [...this.item.pricings];
      const index = pricings.indexOf(oldValue);
      pricings.splice(index, 1, newValue);
      this.$store.commit(`${this.slug}/patchItem`, { pricings });
    },
    userRowSelected(rows) {
      this.usersSelected = rows;
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
.admin-community-form__language-definition {
  margin-bottom: 20px;

  .card-header {
    padding: 0;

    > a {
      padding: 0.75rem 1.25rem;
    }
  }
}
</style>
