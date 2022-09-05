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

          <div class="form__section">
            <h2>Zone géographique</h2>
            <b-form-group
              :description="
                `Zone géographique sous la forme d'une liste de tuples ` +
                '(latitude, longitude), un par ligne.'
              "
              label-for="area"
            >
              <b-form-textarea id="area" name="area" v-model="area" rows="6" max-rows="12" />
            </b-form-group>
          </div>

          <div class="form__section">
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
              <b-col md="8">
                <h2>Membres</h2>
              </b-col>

              <b-col md="4" class="text-right">
                <b-input v-model="usersFilter" placeholder="Tapez pour filtrer..." />
              </b-col>
            </b-row>

            <b-table
              :busy="usersLoading"
              :filter="usersFilter"
              empty-filtered-text="Pas de membre correspondant"
              :filter-function="localizedFilter(userTableFilterFields)"
              striped
              hover
              :items="users"
              sort-by="full_name"
              no-sort-reset
              :fields="userTable"
              sticky-header="500px"
              :show-empty="true"
              empty-text="Pas de membre"
            >
              <template v-slot:table-busy>
                <span>Chargement...</span>
              </template>
              <template v-slot:cell(full_name)="row">
                <router-link :to="`/admin/users/${row.item.id}`">
                  {{ row.item.full_name }}
                </router-link>
              </template>
              <template v-slot:cell(role)="row">
                <b-select
                  :options="[
                    { value: null, text: 'Membre' },
                    { value: 'admin', text: 'Admin' },
                  ]"
                  :value="row.item.role"
                  @change="setUserRole(row.item, $event)"
                />
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

                  <b-modal
                    size="xl"
                    :title="`Preuve de résidence (${row.item.full_name})`"
                    :id="`proof-${row.item.id}`"
                    footer-class="d-none"
                  >
                    <img class="img-fit" :src="row.item.proof.url" />
                  </b-modal>
                </span>
              </template>
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <div v-if="!row.item._new">
                    <b-button
                      v-if="!row.item.approved_at"
                      :disabled="usersLoading || loading"
                      size="sm"
                      class="ml-1 mb-1"
                      variant="primary"
                      @click="approveUser(row.item)"
                    >
                      {{ $t("approuver") | capitalize }}
                    </b-button>
                    <b-button
                      v-else-if="!row.item.suspended_at"
                      :disabled="usersLoading || loading"
                      size="sm"
                      class="ml-1 mb-1"
                      variant="warning"
                      @click="suspendUser(row.item)"
                    >
                      {{ $t("suspendre") | capitalize }}
                    </b-button>
                    <b-button
                      v-else
                      :disabled="usersLoading || loading"
                      size="sm"
                      class="ml-1 mb-1"
                      variant="success"
                      @click="unsuspendUser(row.item)"
                    >
                      {{ $t("rétablir") | capitalize }}
                    </b-button>
                  </div>

                  <b-button
                    size="sm"
                    variant="danger"
                    class="ml-1 mb-1"
                    :disabled="usersLoading || loading"
                    @click="removeUser(row.item)"
                  >
                    {{ $t("retirer") | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>

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

            <community-users-list
              :visibleFields="[
                'id',
                'user_full_name',
                'role',
                'approved_at',
                'suspended_at',
                'proof',
                'actions',
              ]"
              :items="communityUsers"
              :totalItemCount="communityUsersTotal"
              :itemsPerPage="10"
              @changePage="onChangePage"
            >
            </community-users-list>
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
import CommunityUsersList from "@/components/Community/CommunityUsersList.vue";

import FormsBuilder from "@/components/Forms/Builder.vue";
import PricingForm from "@/components/Pricing/PricingForm.vue";
import PricingLanguageDefinition from "@/components/Pricing/LanguageDefinition.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import FormMixin from "@/mixins/FormMixin";
import DataRouteGuards from "@/mixins/DataRouteGuards";

import locales from "@/locales";

export default {
  name: "AdminCommunity",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    CommunityUsersList,
    FormsBuilder,
    FormsValidatedInput,
    PricingForm,
    PricingLanguageDefinition,
  },
  data() {
    return {
      newPricingType: null,
      userTable: [
        { key: "id", label: "ID", sortable: true },
        { key: "full_name", label: "Nom complet", sortable: true },
        { key: "role", label: "Rôle", sortable: true },
        { key: "approved_at", label: "Approuvé", sortable: true },
        { key: "suspended_at", label: "Suspendu", sortable: true },
        { key: "proof", label: "Preuve", sortable: false },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      userTableFilterFields: ["full_name"],
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
      return this.$store.state.users.data.filter(() => true);
    },
    /*
      Ensure the format follows the community user format. This is what we are working on.
    */
    communityUsers() {
      const users = this.$store.state.users.data.filter(() => true);

      // Convert data recieved from the backend to communityUsers required by the list.
      let communityUser;
      let communityUsers = [];

      for (const user of users) {
        for (const community of user.communities) {
          communityUser = {
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
          };

          communityUsers.push(communityUser);
        }
      }

      return communityUsers;
    },
    communityUsersTotal() {
      return this.$store.state.users.total;
    },
    usersFilter: {
      get() {
        return this.$store.state["admin.community"].usersFilter;
      },
      set(val) {
        this.$store.commit("admin.community/usersFilter", val);
      },
    },
    communityUserListParams() {
      return this.$store.state['admin.community'].communityUserListParams;
    },
    usersLoading() {
      return !!this.$store.state.users.cancelToken;
    },
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
    localizedFilter(columns) {
      return (row, filter) =>
        columns
          .map((c) => row[c] || "")
          .join(",")
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .match(new RegExp(filter.normalize("NFD").replace(/[\u0300-\u036f]/g, ""), "i"));
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
    resetUsersExportUrl() {
      this.$store.commit(`${this.slug}/usersExportUrl`, null);
    },
    async setUserRole(user, role) {
      await this.updateUser(user, (u) => {
        const data = {
          ...u,
        };
        // Only update the community_user.role
        const community = data.communities.find((c) => c.id === this.item.id);
        community.role = role;
        data.role = community.role; // the data was misplaced so we leave it also here

        return data;
      });
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
        "communities.id": this.item.id,
      };

      let contextParams = {
        page: this.communityUserListParams.page,
      }

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
          console.log(e);
        }
      }, 250);

      return true;
    },
    onChangePage(page) {
      this.communityUserSetListParam({ name: "page", value: page });
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
