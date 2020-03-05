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

            <forms-builder :definition="form" :item="item" entity="communities" />
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

          <div class="form__section" v-if="item.id">
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
                  <b-button v-if="!row.item.suspended_at"
                    size="sm" class="mr-1" variant="warning"
                    @click="suspendUser(row.item)">
                    {{ $t('suspendre') | capitalize }}
                  </b-button>
                  <b-button v-if="row.item.suspended_at"
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

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'AdminCommunity',
  mixins: [FormMixin],
  components: {
    FormsBuilder,
  },
  data() {
    return {
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
  },
  methods: {
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
    userRowSelected(rows) {
      this.usersSelected = rows;
    },
  },
};
</script>

<style lang="scss">
</style>
