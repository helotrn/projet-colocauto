<template>
  <b-container fluid>
    <b-row class="sm-stack">
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
          <b-dropdown left :disabled="selected.length === 0" text="Actions groupées">
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
          @change="customSetParam"
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
          <template v-slot:cell(communities_name)="row">
            <router-link v-if="row.item.communities && row.item.communities.length" :to="'/admin/communities/'+row.item.communities[0].id">
              {{ row.item.communities[0].name }}
            </router-link>
            <template v-else-if="row.item.administrable_communities && row.item.administrable_communities.length">
              <router-link v-for="community in row.item.administrable_communities" :to="'/admin/communities/'+community.id">
                {{ community.name }}
              </router-link>
            </template>
          </template>
          <template v-slot:cell(role)="{item}">
            {{ item.role == 'community_admin' ? 'Admin de communauté' : (item.role == 'admin' ? 'Admin global' : item.role) }}
          </template>
          <template v-slot:cell(actions)="row">
            <div class="user-actions">
              <admin-list-actions :columns="['edit']" :row="row" :slug="slug" />
              <b-button
                v-if="!row.item.deleted_at"
                size="sm"
                class="mr-1"
                variant="danger"
                @click="destroyItemModal(row.item)"
              >
                {{ $t("archive") | capitalize }}
              </b-button>
              <b-button
                v-else
                size="sm"
                class="mr-1"
                variant="warning"
                @click="restoreItemModal(row.item)"
              >
                {{ $t("restore") | capitalize }}
              </b-button>
              <b-button
                v-if="!row.item.deleted_at"
                :id="'mandate-' + row.item.id"
                size="sm"
                variant="warning"
                v-on:click="mandate(row.item.id)"
              >
                <i class="bi bi-person-badge"></i>
              </b-button>
              <b-tooltip :target="'mandate-' + row.item.id" triggers="hover">
                {{ $t("mandate_tool_tip") }}
              </b-tooltip>
            </div>
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
          <b-spinner small v-if="generatingCSV"></b-spinner>
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
        <admin-pagination :params="contextParams" :total="total" @change="setParam" />
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
        { key: "id", label: "ID", sortable: true, class: "text-right tabular-nums" },
        { key: "full_name", label: "Nom", sortable: true },
        { key: "email", label: "Courriel", sortable: true },
        { key: "communities_name", label: "Communauté", sortable: true },
        { key: "role", label: "Admin", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  methods: {
    // We use a setParam wrapper to make sure the default params always exclude deactivated users
    customSetParam(param) {
      if (param.name === "is_deactivated" && typeof param.value === "undefined") {
        this.setParam({ ...param, value: 0 });
      } else {
        this.setParam(param);
      }
    },
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
    activateUser(id) {
      return this.$store.dispatch(`${this.slug}/update`, {
        id: id,
        data: {
          is_deactivated: 0,
        },
      });
    },
    deactivateUser(id) {
      return this.$store.dispatch(`${this.slug}/update`, {
        id: id,
        data: {
          is_deactivated: 1,
        },
      });
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
  .btn {
    margin: 0 10px;
  }
}
.btn .spinner-border {
  vertical-align: top;
}
</style>
