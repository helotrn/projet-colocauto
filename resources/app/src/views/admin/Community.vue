<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $tc("communauté", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations générales</h2>

            <forms-builder :definition="formExceptArea" v-model="item" entity="communities" />
          </div>

          <div class="form__section" v-if="item.id">
            <h2>Tarifications</h2>

            <pricing-language-definition />

            <div v-for="(pricing, index) in item.pricings" :key="pricing.id || `idx-${index}`">
              <pricing-form
                :pricing="pricing"
                @remove="removePricing(pricing)"
                @change="updatePricing(pricing, $event)"
              />
            </div>

            <p class="form-inline" v-if="remainingPricingTypes.length > 0">
              Ajouter une tarification pour
              <b-select
                class="ml-1 mr-1"
                :options="remainingPricingTypes"
                v-model="newPricingType"
              />
              <b-button @click="addPricing">OK</b-button>
            </p>
          </div>

          <div class="form__section" v-if="item.id">
            <a id="members" />
            <b-row>
              <b-col>
                <h2>Membres</h2>
              </b-col>
            </b-row>

            <b-row>
              <b-col class="admin__filters">
                <community-users-filters
                  :visibleFields="['user_id', 'user_full_name']"
                  :filters="communityUserListParams.filters"
                  @change="onChangeFilters"
                />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <community-users-list
                  :visibleFields="[
                    'id',
                    'user_full_name',
                    'approved_at',
                    'suspended_at',
                    'actions',
                  ]"
                  :items="communityUsers"
                  :totalItemCount="communityUsersTotal"
                  :itemsPerPage="10"
                  :sortBy="communityUsersSortBy"
                  :sortDesc="communityUsersSortDesc"
                  :busy="communityUsersLoading"
                  @changePage="onChangePage"
                  @changeOrder="onChangeOrder"
                  @changeUserRole="onChangeUserRole"
                  @action="onCommunityUserAction"
                >
                </community-users-list>
              </b-col>
            </b-row>

            <b-row>
              <b-col md="6">
                <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
                  Générer (CSV)
                </b-button>
                <a
                  variant="outline-primary"
                  :href="usersExportUrl"
                  target="_blank"
                  v-if="usersExportUrl"
                  @click="resetUsersExportUrl"
                >
                  Télécharger (CSV)
                </a>
              </b-col>

              <b-col md="6">
                <forms-validated-input
                  type="relation"
                  name="community"
                  label="Ajouter un membre"
                  :value="null"
                  reset-after-select
                  :query="{
                    slug: 'users',
                    value: 'id',
                    text: 'full_name',
                    params: {
                      fields: 'id,full_name',
                      '!id': users.map((u) => u.id).join(','),
                    },
                  }"
                  @relation="addUser"
                />
              </b-col>
            </b-row>
          </div>

          <div class="form__section" v-if="item.id">
            <a id="admins" />
            <b-row>
              <b-col>
                <h2>Admins</h2>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <community-users-list
                  :visibleFields="[
                    'id',
                    'user_full_name',
                    'actions',
                  ]"
                  :items="communityAdmins"
                  :totalItemCount="communityAdminsTotal"
                  :itemsPerPage="10"
                  :sortBy="communityAdminsSortBy"
                  :sortDesc="communityAdminsSortDesc"
                  :busy="communityAdminsLoading"
                  @changePage="onAdminChangePage"
                  @changeOrder="onAdminChangeOrder"
                  @action="onCommunityAdminAction"
                  :allowedActions="['remove']"
                >
                </community-users-list>
              </b-col>
            </b-row>

            <b-row>
              <b-col md="6">
                <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
                  Générer (CSV)
                </b-button>
                <a
                  variant="outline-primary"
                  :href="usersExportUrl"
                  target="_blank"
                  v-if="usersExportUrl"
                  @click="resetUsersExportUrl"
                >
                  Télécharger (CSV)
                </a>
              </b-col>

              <b-col md="6">
                <forms-validated-input
                  type="relation"
                  name="administrable_community"
                  label="Ajouter un admin"
                  :value="null"
                  reset-after-select
                  :query="{
                    slug: 'users',
                    value: 'id',
                    text: 'full_name',
                    params: {
                      fields: 'id,full_name',
                      '!id': users.map((u) => u.id).join(','),
                      role: 'community_admin'
                    },
                  }"
                  @relation="addAdmin"
                />
              </b-col>
            </b-row>
          </div>

          <div class="form__section" v-if="item.id && balance && balance.users && balance.users.length > 0">
            <b-row>
              <b-col>
                <h2>Les comptes</h2>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <users-balance :users="balance.users"/>
              </b-col>
            </b-row>
          </div>

          <div class="form__section" v-if="item.id">
            <b-row>
              <b-col>
                <h2>Invitations</h2>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <invitations-table-list
                  :visibleFields="[
                    'id',
                    'email',
                    'token',
                    'status',
                    'actions',
                  ]"
                  :items="item.invitations"
                  :busy="loading"
                >
                </invitations-table-list>
              </b-col>
            </b-row>
            <b-button variant="success" :disabled="loading" :to="`/admin/invitations/new?community_id=${item.id}`">
              Créer
            </b-button>
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
import { watch } from "vue";
import CommunityUsersFilters from "@/components/Community/CommunityUsersFilters.vue";
import CommunityUsersList from "@/components/Community/CommunityUsersList.vue";
import InvitationsTableList from "@/components/Invitation/InvitationsTableList.vue";

import FormsBuilder from "@/components/Forms/Builder.vue";
import PricingForm from "@/components/Pricing/PricingForm.vue";
import PricingLanguageDefinition from "@/components/Pricing/LanguageDefinition.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UsersBalance from "@/components/Balance/UsersBalance.vue";

import FormMixin from "@/mixins/FormMixin";
import DataRouteGuards from "@/mixins/DataRouteGuards";

import locales from "@/locales";

export default {
  name: "AdminCommunity",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    CommunityUsersFilters,
    CommunityUsersList,
    InvitationsTableList,
    FormsBuilder,
    FormsValidatedInput,
    PricingForm,
    PricingLanguageDefinition,
    UsersBalance,
  },
  mounted() {
    // Initial load of sublist data accounting for filter, order and page num.
    this.loadCommunityUserListData();

    // wait for state.item.it to be loaded
    if( this.item ) this.loadCommunityAdminsListData();
    else {
      const done = watch( this.context, () => {
        if( this.item ) {
          this.loadCommunityAdminsListData();
          done();
        }
      })
    }
  },
  data() {
    return {
      newPricingType: null,
      adminsListDebounce: null,
    };
  },
  computed: {
    area: {
      get() {
        return (this.item.area || []).join("\n");
      },
      set(area) {
        const newItem = {
          ...this.item,
          area: area.split("\n").map((t) => t.split(",")),
        };
        this.$store.commit(`${this.slug}/item`, newItem);
      },
    },
    usersExportUrl() {
      return this.context.usersExportUrl;
    },
    formExceptArea() {
      const form = { ...this.form };
      delete form.area;
      return form;
    },
    remainingPricingTypes() {
      const currentPricingTypes = this.item.pricings.map((p) => p.object_type);
      return [
        {
          value: null,
          text: "Générique",
        },
        {
          value: "car",
          text: "Voiture",
        },
        {
          value: "bike",
          text: "Vélo",
        },
        {
          value: "trailer",
          text: "Remorque",
        },
      ].filter((p) => currentPricingTypes.indexOf(p.value) === -1);
    },
    users() {
      return this.$store.state.users.data;
    },
    admins() {
      return this.$store.state.communities.admins.data;
    },
    /*
      Ensure the format follows the community user format. This is what we are working on.
    */
    communityUsers() {
      // Convert data recieved from the backend to communityUsers required by the list.
      let communityUsers = [];

      for (const user of this.users) {
        if (!user.communities) {
          // When users are loaded from the list, they may not have their communities set.
          continue;
        }
        for (const community of user.communities) {
          if (community.id != this.$route.params.id) {
            continue;
          }
          communityUsers.push({
            // Pour pouvoir linker aus actions...
            id: user.id,
            user_id: user.id,
            user_full_name: user.full_name,
            community_id: community.id,
            community_name: community.name,
            // Member role is defined as null. List expects it to be explicitly defined.
            role: community.role ? community.role : "member",
            approved_at: community.approved_at,
            suspended_at: community.suspended_at,
            proof: community.proof,
          });
        }
      }

      return communityUsers;
    },
    communityUsersTotal() {
      return this.$store.state.users.total;
    },
    communityAdminsTotal() {
      return this.$store.state.communities.admins.total;
    },
    communityUserListParams() {
      return this.$store.state["admin.community"].communityUserListParams;
    },
    communityAdminListParams() {
      return this.$store.state["admin.community"].communityAdminListParams;
    },
    communityUsersLoading() {
      return !!this.$store.state.users.cancelToken;
    },
    communityAdminsLoading() {
      return !!this.$store.state.communities.cancelToken;
    },
    communityUsersSortBy() {
      // Field name without the minus sign indicating order.
      const sortFieldName = this.communityUserListParams.order.replace("-", "");

      let sortFieldKey = "";

      // Convert backend field names to list field keys.
      switch (sortFieldName) {
        // Some remain unchanged
        case "id":
          sortFieldKey = sortFieldName;
          break;

        case "full_name":
          sortFieldKey = "user_full_name";
          break;

        default:
          sortFieldKey = "";
          break;
      }

      return sortFieldKey;
    },
    communityAdminsSortBy() {
      // Field name without the minus sign indicating order.
      const sortFieldName = this.communityAdminListParams.order.replace("-", "");

      let sortFieldKey = "";

      // Convert backend field names to list field keys.
      switch (sortFieldName) {
        // Some remain unchanged
        case "id":
          sortFieldKey = sortFieldName;
          break;

        case "full_name":
          sortFieldKey = "user_full_name";
          break;

        default:
          sortFieldKey = "";
          break;
      }

      return sortFieldKey;
    },
    communityUsersSortDesc() {
      return this.communityUserListParams.order[0] === "-";
    },
    communityAdminsSortDesc() {
      return this.communityAdminListParams.order[0] === "-";
    },
    communityAdmins() {
      // Convert data recieved from the backend to communityAdmins required by the list.
      let communityAdmins = [];

      for (const user of this.admins) {
        if (!user.administrable_communities) {
          // When users are loaded from the list, they may not have their communities set.
          continue;
        }
        for (const community of user.administrable_communities) {
          if (community.id != this.$route.params.id) {
            continue;
          }
          communityAdmins.push({
            // Pour pouvoir linker aus actions...
            id: user.id,
            user_id: user.id,
            user_full_name: user.full_name,
            community_id: community.id,
            community_name: community.name,
            organisation: community.organisation ? community.organisation : "",
            approved_at: community.approved_at,
            suspended_at: community.suspended_at,
          });
        }
      }

      return communityAdmins;
    },
    balance() {
      return this.$store.state['admin.community'].balance;
    }
  },
  methods: {
    addPricing() {
      this.$store.commit(`${this.slug}/mergeItem`, {
        pricings: [
          {
            object_type: this.newPricingType,
            rule: "",
            name: "",
          },
        ],
      });
    },
    async addUser(user) {
      await this.$store.dispatch(`${this.slug}/addUser`, {
        id: this.item.id,
        data: {
          id: user.id,
        },
      });
    },
    async addAdmin(user) {
      await this.$store.dispatch(`${this.slug}/addAdmin`, {
        id: this.item.id,
        data: {
          id: user.id,
        },
      });
    },
    async approveUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find((c) => c.id === this.item.id);
        community.approved_at = new Date();
        data.approved_at = community.approved_at; // the data was misplaced so we leave it also here

        return data;
      });
    },
    async exportCSV() {
      await this.$store.dispatch(`${this.slug}/exportUsers`, {
        ...this.routeParams,
        ...this.contextParams,
      });
    },
    removePricing(pricing) {
      const pricings = this.item.pricings.filter((p) => p !== pricing);

      this.$store.commit(`${this.slug}/patchItem`, { pricings });
    },
    async removeUser(user) {
      await this.$store.dispatch(`${this.slug}/removeUser`, {
        id: this.item.id,
        userId: user.id,
      });

      this.$store.commit(
        "users/data",
        this.$store.state.users.data.filter((u) => u.id !== user.id)
      );
    },
    async removeAdmin(user) {
      await this.$store.dispatch(`${this.slug}/removeAdmin`, {
        id: this.item.id,
        userId: user.id,
      });

      this.$store.commit(
        "communities/adminUsersData",
        this.$store.state.communities.admins.data.filter((u) => u.id !== user.id)
      );
    },
    resetUsersExportUrl() {
      this.$store.commit(`${this.slug}/usersExportUrl`, null);
    },
    async suspendUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find((c) => c.id === this.item.id);
        community.suspended_at = new Date();
        data.suspended_at = community.suspended_at; // the data was misplaced so we leave it also here

        return data;
      });
    },
    async unsuspendUser(user) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };

        const community = data.communities.find((c) => c.id === this.item.id);
        community.suspended_at = null;
        data.suspended_at = null; // the data was misplaced so we leave it also here

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
    },
    communityUserSetListParam({ name, value }) {
      this.$store.commit("admin.community/communityUserListParam", { name, value });
    },
    loadCommunityUserListData() {
      const filtersByKey = this.communityUserListParams.filters;
      let filtersByName = {};

      for (const fieldKey in filtersByKey) {
        let fieldName = "";

        switch (fieldKey) {
          case "user_id":
            fieldName = "id";
            break;

          case "user_full_name":
            fieldName = "full_name";
            break;

          default:
            fieldName = "";
            break;
        }

        if ("" !== fieldName) {
          filtersByName[fieldName] = filtersByKey[fieldKey];
        }
      }

      let routeParams = {
        fields: [
          "id",
          "full_name",
          "communities.role",
          "communities.proof",
          "communities.approved_at",
          "communities.suspended_at",
        ].join(","),
        is_deactivated: 0,
        "communities.id": this.$route.params.id,
      };

      let contextParams = {
        page: this.communityUserListParams.page,
        order: this.communityUserListParams.order,
        ...filtersByName,
      };

      if (this.listDebounce) {
        clearTimeout(this.listDebounce);
      }

      this.listDebounce = setTimeout(() => {
        try {
          this.$store.dispatch(`users/retrieve`, {
            // Pas route params, mais les paramètres spécifiques à la liste.
            ...routeParams,
            // Pas context params, mais les paramètres spécifiques à la liste.
            ...contextParams,
          });
          
          this.listDebounce = null;
        } catch (e) {
          this.$store.commit("addNotification", {
            content: `Erreur de chargement de données (${this.slug})`,
            title: `${this.slug}`,
            variant: "warning",
            type: "data",
          });
        }
      }, 250);

      this.$store.dispatch('invitations/retrieve');
      if( this.$route.params.id !== 'new' ) {
        this.$store.dispatch('admin.community/loadUsersBalance', this.$route.params.id);
      }


      return true;
    },
    loadCommunityAdminsListData() {

      let routeParams = {
        fields: [
          "id",
          "full_name",
          "administrable_communities.organisation",
          "administrable_communities.approved_at",
          "administrable_communities.suspended_at",
        ].join(","),
      };

      let contextParams = {
        page: this.communityAdminListParams.page,
        order: this.communityAdminListParams.order,
      };

      if (this.adminListDebounce) {
        clearTimeout(this.adminListDebounce);
      }

      this.adminListDebounce = setTimeout(() => {
        try {
          this.$store.dispatch(`communities/getAdmins`, {
            // Pas route params, mais les paramètres spécifiques à la liste.
            ...routeParams,
            // Pas context params, mais les paramètres spécifiques à la liste.
            ...contextParams,
          });
        } catch (e) {
          this.$store.commit("addNotification", {
            content: `Erreur de chargement de données (${this.slug})`,
            title: `${this.slug}`,
            variant: "warning",
            type: "data",
          });
        }
      }, 250);
    },
    onChangeFilters(filters) {
      this.communityUserSetListParam({ name: "filters", value: filters });
    },
    onChangePage(page) {
      this.communityUserSetListParam({ name: "page", value: page });
    },
    onAdminChangePage(page) {
      this.communityAdminSetListParam({ name: "page", value: page });
    },
    onChangeOrder(context) {
      // If descending order, then prepend field key with minus sign.
      let sortOrder = context.sortDesc ? "-" : "";

      // Convert field keys to field names understood by the backend.
      switch (context.sortBy) {
        // Some remain unchanged
        case "id":
          sortOrder += context.sortBy;
          break;

        case "user_full_name":
          sortOrder += "full_name";
          break;

        default:
          sortOrder = "";
          break;
      }

      this.communityUserSetListParam({ name: "order", value: sortOrder });
    },
    onAdminChangeOrder(context) {
      // If descending order, then prepend field key with minus sign.
      let sortOrder = context.sortDesc ? "-" : "";

      // Convert field keys to field names understood by the backend.
      switch (context.sortBy) {
        // Some remain unchanged
        case "id":
          sortOrder += context.sortBy;
          break;

        case "user_full_name":
          sortOrder += "full_name";
          break;

        default:
          sortOrder = "";
          break;
      }

      this.communityAdminSetListParam({ name: "order", value: sortOrder });
    },
    async onChangeUserRole(item, role) {
      // Find the modified user.
      const user = this.users.find((u) => u.id === item.user_id);

      await this.updateUser(user, (u) => {
        // Only update the community_user.role
        const community = u.communities.find((c) => c.id === this.item.id);

        // Backend interprets role as:
        //   - null: User is a regular member of this community.
        //   - "admin": User is an administrator of this community.
        // The list components accepts {"member", "admin"}.
        community.role = role === "member" ? null : role;

        return u;
      });
    },
    async onCommunityUserAction(item, action) {
      // Find user.
      const user = this.users.find((u) => u.id === item.user_id);

      // Then call requested action.
      switch (action) {
        case "approve":
          this.approveUser(user);
          break;

        case "suspend":
          this.suspendUser(user);
          break;

        case "unsuspend":
          this.unsuspendUser(user);
          break;

        case "remove":
          this.removeUser(user);
          break;
      }
    },
    async onCommunityAdminAction(item, action) {
      // Find user.
      const user = this.admins.find((u) => u.id === item.user_id);

      // Then call requested action.
      switch (action) {
        case "remove":
          this.removeAdmin(user);
          break;
      }
    },
    afterSubmit() {
      if( this.item.created_at == this.item.updated_at ){
        // reload the page to have more edition details after creation
        location.reload();
      }
    }
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
  watch: {
    communityUserListParams: {
      deep: true,
      handler() {
        this.loadCommunityUserListData();
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
