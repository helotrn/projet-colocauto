<template>
  <b-container fluid v-if="item && routeDataLoaded">
    <b-row>
      <b-col>
        <h1 v-if="item.full_name">{{ item.full_name }}</h1>
        <h1 v-else>
          <em>{{ $tc("model_name", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form class="form" @submit.prevent="checkInvalidThenSubmit(passes)">
            <div class="form__section">
              <h2>Informations générales</h2>

              <div v-if="item.borrower && item.borrower.approved">
                <label>Membre depuis le</label>
                {{ item.borrower.approved_at | date }}
              </div>
              <div v-else>
                <label>Membre en attente d'approbation depuis le</label>
                {{ item.created_at | date }}
              </div>
              <hr />

              <forms-builder :definition="form.general" v-model="item" entity="users" />
            </div>

            <div class="form__section" v-if="loggedInUserIsAdmin">
              <h2>Administrateur global</h2>

              <b-alert show variant="danger">
                Attention! N'accordez pas le rôle d'administration globale à la légère: un-e
                administrateur-rice global-e peut <strong>tout</strong> faire avec un minimum de
                validations.
              </b-alert>

              <b-form-select
                id="role"
                name="role"
                :options="[
                  { value: null, text: 'Régulier' },
                  { value: 'admin', text: 'Admin global' },
                  { value: 'community_admin', text: 'Administrateur de communauté' },
                ]"
                v-model="item.role"
              />
            </div>

            <div class="form__section" v-show="loggedInUserIsAdmin">
              <h2>Mot de passe</h2>

              <user-password-form
                :loading="loading"
                :is-admin="loggedInUserIsAdmin"
                :user="item"
                ref="passwordForm"
                @updated="resetPasswordFormAndShowModal"
              />

              <b-modal
                size="md"
                header-bg-variant="success"
                title-class="font-weight-bold"
                ok-only
                no-close-on-backdrop
                no-close-on-esc
                hide-header-close
                :title="$t('password_change.title')"
                id="password-change-modal"
              >
                <div v-html="$t('password_change.content')" />
              </b-modal>
            </div>

            <div class="form__section">
              <a id="borrower" />
              <h2>Profil d'emprunteur</h2>

              <p><strong>Statut:</strong> {{ borrowerStatus }}</p>

              <p v-if="item.borrower">
                <b-button
                  v-if="!item.borrower.approved_at"
                  size="sm"
                  class="ml-1"
                  variant="primary"
                  :disabled="loading"
                  @click="approveBorrower(item)"
                >
                  {{ $t("approve") | capitalize }}
                </b-button>
                <b-button
                  v-else-if="!item.borrower.suspended_at"
                  size="sm"
                  class="ml-1"
                  variant="warning"
                  :disabled="loading"
                  @click="suspendBorrower(item)"
                >
                  {{ $t("suspend") | capitalize }}
                </b-button>
                <b-button
                  v-else
                  size="sm"
                  class="mr-1"
                  variant="success"
                  :disabled="loading"
                  @click="unsuspendBorrower(item)"
                >
                  {{ $t("rétablir") | capitalize }}
                </b-button>
              </p>

              <forms-builder
                :definition="form.borrower"
                v-model="item.borrower"
                entity="borrowers"
              />
            </div>

            <div class="form__section" v-if="!!item.id">
              <h2>Véhicules</h2>

              <b-table
                striped
                hover
                :items="item.loanables"
                selectable
                select-mode="multi"
                @row-selected="loanableRowSelected"
                :fields="loanablesTable"
                no-sort-reset
                :show-empty="true"
                empty-text="Pas de véhicules"
              >
                <template v-slot:cell(actions)="row">
                  <div class="text-right">
                    <b-button size="sm" :to="`/admin/loanables/${row.item.id}`">
                      {{ $t("modifier") | capitalize }}
                    </b-button>
                  </div>
                </template>
              </b-table>
            </div>

            <div class="form__section" v-if="!!item.id">
              <h2>Communautés</h2>

              <b-table
                striped
                hover
                :items="item.communities"
                selectable
                select-mode="single"
                @row-selected="communityRowSelected"
                :fields="communitiesTable"
                no-sort-reset
                :show-empty="true"
                empty-text="Pas de communauté"
              >
                <template v-slot:cell(actions)="row">
                  <div class="text-right">
                    <b-button
                      v-if="!row.item._new"
                      size="sm"
                      variant="success"
                      @click="viewUserInCommunity(row.item)"
                    >
                      {{ $t("afficher") | capitalize }} dans la communauté
                    </b-button>
                  </div>
                </template>
              </b-table>

              <forms-validated-input
                type="relation"
                name="community"
                label="Ajouter une communauté"
                :value="null"
                reset-after-select
                :query="{
                  slug: 'communities',
                  value: 'id',
                  text: 'name',
                  params: {
                    '!id': item.communities.map((c) => c.id).join(','),
                  },
                }"
                @relation="addCommunity"
              />
            </div>

            <div class="form__section" v-if="!!item.id">
              <h2>Emprunts</h2>

              <b-table
                striped
                hover
                :items="allLoans"
                selectable
                select-mode="multi"
                @row-selected="loanRowSelected"
                :fields="loansTable"
                no-sort-reset
                :show-empty="true"
                empty-text="Pas d'emprunt"
              >
                <template v-slot:cell(borrower)="row">
                  {{ row.item.borrower.user.full_name }}
                </template>
                <template v-slot:cell(loanable)="row">
                  {{ row.item.loanable.name }}
                </template>
                <template v-slot:cell(date)="row">
                  <span v-if="isLoanMultipleDays(row.item)">
                    {{ row.item.departure_at | date }} {{ row.item.departure_at | time }}<br />
                    {{ computeReturnAt(row.item) | date }} {{ computeReturnAt(row.item) | time }}
                  </span>
                  <span v-else>
                    {{ row.item.departure_at | date }}<br />
                    {{ row.item.departure_at | time }} à {{ computeReturnAt(row.item) | time }}
                  </span>
                </template>
                <template v-slot:cell(actions)="row">
                  <div class="text-right">
                    <b-button size="sm" :to="`/admin/loans/${row.item.id}`">
                      {{ $t("modifier") | capitalize }}
                    </b-button>
                  </div>
                </template>
              </b-table>
            </div>

            <div class="form__buttons">
              <b-button-group>
                <b-button variant="success" type="submit" :disabled="!changed || loading">
                  Sauvegarder
                </b-button>
                <b-button type="reset" :disabled="!changed" @click="reset">
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
import FormsBuilder from "@/components/Forms/Builder.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UserPasswordForm from "@/components/User/PasswordForm.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import { filters } from "@/helpers";
import locales from "@/locales";

const { capitalize } = filters;

export default {
  name: "AdminUser",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
    FormsValidatedInput,
    UserPasswordForm,
  },
  data() {
    return {
      communitiesSelected: [],
      communitiesTable: [
        { key: "id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      invoicesSelected: [],
      invoicesTable: [
        { key: "created_at", label: "Date", sortable: true },
        { key: "items_count", label: "Nb d'items", sortable: true },
        {
          key: "total",
          label: "Total",
          sortable: true,
          tdClass: "text-right",
        },
        {
          key: "total_with_taxes",
          label: "Total avec taxes",
          sortable: true,
          tdClass: "text-right",
        },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      loanablesSelected: [],
      loanablesTable: [
        { key: "id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      loansSelected: [],
      loansTable: [
        { key: "id", label: "ID", sortable: true },
        { key: "borrower", label: "Emprunteur", sortable: true },
        { key: "loanable", label: "Objet", sortable: true },
        { key: "date", label: "Date", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      paymentMethodsTable: [
        { key: "id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "type", label: "Type", sortable: true },
        { key: "informations", label: "Informations" },
      ],
      tagsSelected: [],
      tagsTable: [
        {
          key: "id",
          label: capitalize(this.$t("fields.communities.tags.id")),
          sortable: true,
        },
        {
          key: "name",
          label: capitalize(this.$t("fields.communities.tags.name")),
          sortable: true,
        },
        {
          key: "slug",
          label: capitalize(this.$t("fields.communities.tags.slug")),
          sortable: true,
        },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  computed: {
    allLoans() {
      return [
        ...this.item.loanables.reduce((acc, loanable) => {
          acc.push(
            ...loanable.loans.map((l) => ({
              ...l,
              loanable: {
                ...loanable,
                owner: {
                  ...this.item.owner,
                  user: this.item,
                },
              },
            }))
          );
          return acc;
        }, []),
        ...this.item.loans,
      ];
    },
    borrowerStatus() {
      if (!this.item.borrower) {
        return "L'utilisateur n'a pas encore commencé à remplir son dossier de conduite";
      } else if (!this.item.borrower.approved) {
        return "Non approuvé";
      }
      return this.item.borrower.suspended ? "Suspendu" : "Approuvé";
    },
    loggedInUserIsAdmin() {
      const { user } = this.$store.state;
      return user && (user.role === "admin" || user.role === "community_admin");
    },
    communitySelected() {
      return this.communitiesSelected[0];
    },
    communitySelectedTagIds() {
      if (!this.communitySelected) {
        return "0";
      }

      return this.communitySelected.tags.map((t) => t.id).join(",") || "0";
    },
  },
  methods: {
    addCommunity(community) {
      this.item.communities.push({
        ...community,
        _new: true,
      });
    },
    addCommunityTag(community, tag) {
      if (tag) {
        community.tags.push(tag);
      }
    },
    afterSubmit() {
      this.$refs.passwordForm.reset();
    },
    async approveBorrower(user) {
      await this.$store.dispatch(`${this.slug}/approveBorrower`, user.id);
    },
    async checkInvalidThenSubmit(passes) {
      await passes(this.submit);

      const invalidItems = document.getElementsByClassName("is-invalid");
      if (invalidItems.length > 0) {
        invalidItems[0].scrollIntoView({
          behavior: "smooth",
        });
      }
    },
    communityRowSelected(rows) {
      this.communitiesSelected = rows;
    },
    computeReturnAt(loan) {
      return this.$dayjs(loan.departure_at)
        .add(loan.duration_in_minutes, "minute")
        .format("YYYY-MM-DD HH:mm:ss");
    },
    invoiceRowSelected(rows) {
      this.invoicesSelected = rows;
    },
    isLoanMultipleDays(loan) {
      return (
        this.$dayjs(loan.departure_at).format("YYYY-MM-DD") !==
        this.$dayjs(this.computeReturnAt(loan)).format("YYYY-MM-DD")
      );
    },
    loanableRowSelected(rows) {
      this.loanablesSelected = rows;
    },
    loanRowSelected(rows) {
      this.loansSelected = rows;
    },
    removeCommunityTag(community, tag) {
      const index = community.tags.indexOf(tag);
      community.tags.splice(index, 1);
    },
    resetPasswordFormAndShowModal() {
      this.$refs.passwordForm.reset();
      this.$bvModal.show("password-change-modal");
    },
    async suspendBorrower(user) {
      await this.$store.dispatch(`${this.slug}/suspendBorrower`, user.id);
    },
    tagRowSelected(rows) {
      this.tagsSelected = rows;
    },
    async unsuspendBorrower(user) {
      await this.$store.dispatch(`${this.slug}/unsuspendBorrower`, user.id);
    },
    viewUserInCommunity(community) {
      this.$store.commit("admin.community/communityUserListParam", {
        name: "filters",
        value: { user_id: this.item.id, user_full_name: this.item.full_name },
      });
      this.$router.push(`/admin/communities/${community.id}#members`);
    },
    getSignedAmount(type, amount) {
      if (type === "debit") return -amount;

      return amount;
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

<style lang="scss"></style>
