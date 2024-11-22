<template>
  <div>
    <b-alert v-if="!loanable.owner" variant="info" show>
      Ajoutez un propriétaire avant de configurer les droits de gestion.
    </b-alert>
    <b-alert v-if="hasNoVisibleContacts" variant="warning" show>
      Personne n'est présentement affiché comme contact lors des emprunts.
    </b-alert>
    <b-row>
      <b-col v-if="loanable.owner" xl="6" class="mb-3">
        <user-blurb
          v-if="!isEditingOwner && !isChangingOwner"
          class="coowner"
          :user="loanable.owner.user"
          show-phone
          description="Propriétaire"
        >
          <template #nameicon>
            <b-icon
              v-if="loanable.show_owner_as_contact"
              v-b-tooltip.hover
              title="Visible aux emprunteurs"
              variant="primary"
              icon="eye-fill"
            />
          </template>
          <template v-if="canEditOwner" #buttons>
            <div class="action-buttons">
              <icon-button
                v-if="loanable.id"
                :disabled="disabled"
                size="sm"
                role="edit"
                @click="() => (isEditingOwner = true)"
              >
                Modifier
              </icon-button>
              <icon-button
                v-if="canChangeOwner"
                :disabled="disabled"
                size="sm"
                variant="ghost-secondary"
                icon="arrow-left-right"
                @click="() => (isChangingOwner = true)"
              >
                Changer
              </icon-button>
            </div>
          </template>
        </user-blurb>
        <owner-form
          v-else
          :changing-owner="isChangingOwner"
          :disabled="disabled"
          :loanable="loanable"
          @done="
            () => {
              isEditingOwner = false;
              isChangingOwner = false;
            }
          "
        />
      </b-col>
      <b-col v-for="coowner in loanable.coowners" :key="coowner.id" class="mb-3" xl="6">
        <user-blurb
          v-if="!editingIds.includes(coowner.id)"
          class="coowner"
          :user="coowner.user"
          show-phone
          :description="coowner.title"
          :class="{ loading: isDeletingCoowner(coowner) }"
          :show-admin-link="showAdminLinks"
        >
          <template #nameicon>
            <b-icon
              v-if="coowner.show_as_contact"
              v-b-tooltip.hover
              title="Visible aux emprunteurs"
              variant="primary"
              icon="eye-fill"
            />
          </template>
          <template #buttons>
            <div class="action-buttons">
              <icon-button
                v-if="!isDeletingCoowner(coowner) && canEditCoowner(coowner)"
                :disabled="disabled"
                size="sm"
                role="edit"
                @click="() => editCoowner(coowner.id)"
              >
                Modifier
              </icon-button>
              <icon-button
                v-if="!isDeletingCoowner(coowner) && canDeleteCoowner(coowner)"
                :disabled="disabled"
                size="sm"
                role="remove-item"
                @click="() => removeCoowner(coowner)"
              >
                Retirer
              </icon-button>
              <layout-loading v-if="isDeletingCoowner(coowner)" with-button></layout-loading>
            </div>
          </template>
        </user-blurb>
        <coowner-form
          v-else
          :disabled="disabled"
          :coowner="coowner"
          :can-edit-paid-amounts="canEditCoownerPaidAmounts(coowner)"
          :has-paid-insurance="loanable.type === 'car'"
          @done="finishEditCoowner"
        />
      </b-col>
      <b-col v-if="newUser" xl="6" class="mb-3">
        <user-blurb class="loading coowner" :user="newUser">
          <layout-loading with-button></layout-loading>
        </user-blurb>
      </b-col>
    </b-row>
    <b-alert v-if="loanable.owner && !loanable.id" variant="info" show>
      Sauvegardez le nouveau véhicule avant de donner des droits de gestion à un autre utilisateur.
    </b-alert>
    <forms-validated-input
      v-if="canAddCoowner && loanable.owner && loanable.id && !disabled"
      type="relation"
      name="coowner"
      label="Donnez des droits de gestion à un autre utilisateur"
      description="Cet utilisateur pourra voir et gérer tous les aspects de ce véhicule, sauf les droits d'accès d'autres utilisateurs et la suppression du véhicule."
      :value="null"
      reset-after-select
      :disabled="!!newUser"
      :query="{
        slug: 'users',
        value: 'id',
        text: 'full_name',
        details: 'description',
        params: {
          fields: 'id,full_name,description,avatar.*',
          '!id': userFilter,
          ...loanableCommunitiesFilter,
        },
      }"
      @relation="addCoowner"
    />
  </div>
</template>
<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LayoutLoading from "@/components/Layout/Loading.vue";
import CoownerForm from "@/components/Loanable/CoownerForm.vue";
import OwnerForm from "@/components/Loanable/OwnerForm.vue";
import IconButton from "@/components/shared/IconButton.vue";
import UserBlurb from "@/components/User/UserBlurb.vue";
import {
  canAddCoowner,
  canChangeOwner,
  canEditCoowner,
  canEditCoownerPaidAmounts,
  canRemoveCoowner,
} from "@/helpers/permissions/loanables";
import { isGlobalAdmin } from "@/helpers/permissions/users";

export default {
  name: "CoownersForm",
  components: { IconButton, OwnerForm, CoownerForm, UserBlurb, LayoutLoading, FormsValidatedInput },
  props: {
    disabled: { type: Boolean, default: false },
    loanable: { type: Object, required: true },
    showAdminLinks: { type: Boolean, default: false },
  },
  data() {
    return {
      newUser: null,
      deletingIds: [],
      editingIds: [],
      isEditingOwner: false,
      isChangingOwner: false,
    };
  },
  computed: {
    user() {
      return this.$store.state.user;
    },
    userIsOwner() {
      return this.loanable.owner.user.id === this.user.id;
    },
    canEditOwner() {
      return this.userIsOwner || isGlobalAdmin(this.user);
    },
    canChangeOwner() {
      return canChangeOwner(this.user, this.loanable);
    },
    canAddCoowner() {
      return canAddCoowner(this.user, this.loanable);
    },
    hasNoVisibleContacts() {
      return (
        !this.loanable.show_owner_as_contact &&
        this.loanable.coowners?.reduce((acc, c) => acc && !c.show_as_contact, true)
      );
    },
    userFilter() {
      const denyList = this.loanable.coowners ? this.loanable.coowners.map((c) => c.user.id) : [];
      if (this.loanable.owner) {
        denyList.push(this.loanable.owner.user.id);
      }
      return denyList.join(",");
    },
    loanableCommunitiesFilter() {
      if (this.loanable.owner) {
        return {
          "communities.id": this.loanable.owner.user.communities
            ? this.loanable.owner.user.communities.map((c) => c.id).join(",")
            : this.user.communities.map((c) => c.id).join(","),
        };
      }

      // Creating loanable from admin page
      if (isGlobalAdmin(this.user)) {
        return {};
      }

    },
  },
  methods: {
    async addCoowner(user) {
      if (!user) {
        return;
      }

      this.newUser = user;
      try {
        await this.$store.dispatch("loanables/addCoowner", { loanable: this.loanable, user });
      } finally {
        this.newUser = null;
      }
    },
    async removeCoowner(coowner) {
      try {
        const isRemovingSelf = coowner.user.id === this.user.id;
        if (
          !isRemovingSelf ||
          confirm("Êtes-vous certains de vouloir vous enlever en tant que copropriétaire?")
        ) {
          this.deletingIds.push(coowner.id);
          await this.$store.dispatch("loanables/removeCoowner", {
            loanable: this.loanable,
            user: coowner.user,
            coownerId: coowner.id,
          });
          if (isRemovingSelf) {
            await this.$router.push("/app");
          }
        }
      } finally {
        this.deletingIds = this.deletingIds.filter((i) => i !== coowner.id);
      }
    },
    editCoowner(coownerId) {
      this.editingIds.push(coownerId);
    },
    finishEditCoowner(coownerId) {
      this.editingIds = this.editingIds.filter((id) => id !== coownerId);
    },
    canEditCoowner(coowner) {
      return canEditCoowner(this.user, this.loanable, coowner);
    },
    canEditCoownerPaidAmounts() {
      return canEditCoownerPaidAmounts(this.user, this.loanable);
    },
    canDeleteCoowner(coowner) {
      return canRemoveCoowner(this.user, this.loanable, coowner);
    },
    isDeletingCoowner(coowner) {
      return this.deletingIds.includes(coowner.id);
    },
  },
};
</script>

<style scoped lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";
.loading {
  opacity: 0.5;
}
.action-buttons {
  gap: 0.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;

  @include media-breakpoint-down(sm) {
    width: 100%;
    flex-direction: row;
    justify-content: start;
  }
}
</style>
