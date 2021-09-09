<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc("model_name", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-button variant="primary" v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("list.create") | capitalize }}
        </b-button>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <b-button-group variant="info">
          <b-dropdown
            left
            :disabled="selected.length === 0"
            text="Actions groupées"
          >
            <b-dropdown-item @click="sendPasswordResetEmail">
              Courriel de réinit. de mot de passe
            </b-dropdown-item>
            <b-dropdown-item @click="sendRegistrationSubmittedEmail">
              Courriel de bienvenue (inscr. complétée)
            </b-dropdown-item>
            <b-dropdown-item @click="sendRegistrationApprovedEmail">
              Courriel de bienvenue (approbation)
            </b-dropdown-item>
            <b-dropdown-item @click="sendRegistrationStalledEmail">
              Courriel de relance
            </b-dropdown-item>
            <b-dropdown-item @click="sendRegistrationRejectedEmail">
              Courriel de refus d'inscription
            </b-dropdown-item>
            <b-dropdown-divider />
            <b-dropdown-item disabled>{{
              $tc("list.selected", selected.length, { count: selected.length })
            }}</b-dropdown-item>
          </b-dropdown>
        </b-button-group>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="users"
          :filters="filters"
          :params="contextParams"
          @change="setParam"
        />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          ref="usersTable"
          striped
          hover
          :items="data"
          selectable
          select-mode="multi"
          @row-selected="rowSelected"
          :busy="loading"
          :fields="table"
          no-local-sorting
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          no-sort-reset
          :show-empty="true"
          empty-text="Pas de membre"
        >
          <template v-slot:head(id)="data">
            <b-form-checkbox
              inline
              style="margin-right: 0"
              @input="toggleSelection"
              :indeterminate="selectionIndeterminate"
              :checked="selectionStatus"
            />
            {{ data.label.toUpperCase() }}
          </template>
          <template v-slot:cell(type)="row">
            {{ $t(`types.${row.item.type}`) | capitalize }}
          </template>
          <template v-slot:cell(owner)="row">
            <span v-if="row.item.owner">
              {{ row.item.owner.user.full_name }}
            </span>
          </template>
          <template v-slot:cell(actions)="row">
            <div class="user-actions">
              <admin-list-actions
                :columns="['edit', 'delete']"
                :row="row"
                :slug="slug"
              />
              <b-button
                :id="'mandate-' + row.item.id"
                size="sm"
                variant="warning"
                v-on:click="mandate(row.item.id)"
              >
                <i class="bi bi-person-badge"></i>
              </b-button>
              <b-tooltip :target="'mandate-' + row.item.id" triggers="hover">
                Cliquez ici pour vous connecter à la place de l'utilisateur
              </b-tooltip>
            </div>
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
          Générer (CSV)
        </b-button>
        <a
          variant="outline-primary"
          :href="exportUrl"
          target="_blank"
          v-if="context.exportUrl"
          @click="resetExportUrl"
        >
          Télécharger (CSV)
        </a>
      </b-col>

      <b-col md="6">
        <admin-pagination
          :params="contextParams"
          :total="total"
          @change="setParam"
        />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminFilters from "@/components/Admin/Filters.vue";
import AdminListActions from "@/components/Admin/ListActions.vue";
import AdminPagination from "@/components/Admin/Pagination.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import locales from "@/locales";

export default {
  name: "AdminUsers",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
  },
  data() {
    return {
      table: [
        { key: "id", label: "ID", sortable: true },
        { key: "full_name", label: "Nom", sortable: true },
        { key: "email", label: "Courriel", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  methods: {
    async mandate(mandatedUserId) {
      this.$store.dispatch("account/mandate", { mandatedUserId });
    },
    displayMailStatus(data) {
      const { report } = data;

      if (report) {
        const succeeded = report.reduce(
          (acc, s) => acc + (s.response.status === "success" ? 1 : 0),
          0
        );
        const failed = report.reduce((acc, s) => acc + (s.response.status === "error" ? 1 : 0), 0);
        this.$store.commit("addNotification", {
          content: `Résultat de l'envoi: ${succeeded} réussi(s) et ${failed} échoués`,
          title: "Accès refusé",
          variant: "success",
          type: "send_email",
        });
      }
    },
    async sendPasswordResetEmail() {
      const { data } = await this.axios.put(
        `/users/send/password_reset?id=${this.selected.map((s) => s.id).join(",")}`
      );
      this.displayMailStatus(data);
    },
    async sendRegistrationStalledEmail() {
      const { data } = await this.axios.put(
        `/users/send/registration_stalled?id=${this.selected.map((s) => s.id).join(",")}`
      );
      this.displayMailStatus(data);
    },
    async sendRegistrationSubmittedEmail() {
      const { data } = await this.axios.put(
        `/users/send/registration_submitted?id=${this.selected.map((s) => s.id).join(",")}`
      );
      this.displayMailStatus(data);
    },
    async sendRegistrationApprovedEmail() {
      const { data } = await this.axios.put(
        `/users/send/registration_approved?id=${this.selected.map((s) => s.id).join(",")}`
      );
      this.displayMailStatus(data);
    },
    async sendRegistrationRejectedEmail() {
      const { data } = await this.axios.put(
        `/users/send/registration_rejected?id=${this.selected.map((s) => s.id).join(",")}`
      );
      this.displayMailStatus(data);
    },
    toggleSelection(val) {
      if (val === true) {
        this.$refs.usersTable.selectAllRows();
      } else if (val === false) {
        this.$refs.usersTable.clearSelected();
      }
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.users,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.users,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
.user-actions {
  display: flex;
}
.btn {
  margin: 0 10px;
}
</style>
