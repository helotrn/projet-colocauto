<template>
  <b-card class="bordered-card">
    <forms-validated-input
      v-if="changingOwner"
      type="relation"
      name="owner_id"
      :query="ownerQuery"
      :value="loanable.owner_id"
      :object-value="owner"
      :rules="{ required: true }"
      label="Propriétaire"
      mode="eager"
      @relation="setLoanableOwner"
    />

    <template v-else>
      <b-form>
        <div class="mb-3 font-weight-bold">{{ loanable.owner.user.full_name }}</div>
        <forms-validated-input
          v-model="show_as_contact"
          label="Afficher comme contact"
          name="title"
          type="checkbox"
          :disabled="disabled || loading"
          description="Si ses coordonnées devraient être affichées aux emprunteurs-ses."
        ></forms-validated-input>
      </b-form>
      <!-- form with two fields: show on contact and title -->

      <icon-button role="save" :disabled="disabled" :loading="loading" @click.prevent="save">
        Enregistrer
      </icon-button>
    </template>
    &nbsp;
    <icon-button
      v-if="!noCancel"
      role="cancel"
      :disabled="disabled || loading"
      @click.prevent="$emit('done')"
    >
      Annuler
    </icon-button>
    <layout-loading v-if="loading" with-button />
  </b-card>
</template>
<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LayoutLoading from "@/components/Layout/Loading.vue";
import IconButton from "@/components/shared/IconButton.vue";

export default {
  name: "OwnerForm",
  components: { IconButton, LayoutLoading, FormsValidatedInput },
  props: {
    changingOwner: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    loanable: {
      type: Object,
      required: true,
    },
    noCancel: { type: Boolean, default: false },
  },
  data() {
    return {
      show_as_contact: null,
      loading: false,
      ownerQuery: {
        slug: "users",
        value: "owner.id",
        text: "full_name",
        details: "email",
        params: {
          fields:
            "full_name,owner.id,communities.id,communities.name,available_loanable_types,communities.area",
        },
      },
    };
  },
  computed: {
    owner() {
      if (this.loanable.owner) {
        return {
          ...this.loanable.owner.user,
          owner: {
            id: this.loanable.owner.id,
          },
        };
      }

      return null;
    },
  },
  mounted() {
    this.show_as_contact = this.loanable.show_owner_as_contact;
  },
  methods: {
    async save() {
      this.loading = true;
      await Vue.ajax.put(
        "/loanables/" + this.loanable.id,
        {
          show_owner_as_contact: this.show_as_contact,
        },
        {
          cleanupCallback: () => (this.loading = false),
          notifications: {
            action: "changement",
            onSuccess: "changements sauvegardés!",
          },
        }
      );

      this.loanable.show_owner_as_contact = this.show_as_contact;
      this.$emit("done");
    },

    async setLoanableOwner(user) {
      this.loading = true;
      try {
        // If the user hasn't created an 'owner' profile yet, create it for them.
        if (!user.owner) {
          await this.$store.dispatch("users/update", {
            id: user.id,
            data: {
              id: user.id,
              owner: {},
            },
            params: this.ownerQuery.params,
          });

          user = this.$store.state.users.item;
        }

        await this.$store.commit("loanables/patchItem", {
          owner: {
            id: user.owner.id,
            user,
          },
          owner_id: user.owner.id,
        });
        this.$emit("done");
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
<style scoped lang="scss">
.bordered-card {
  border: 1px solid $light-grey;
}
</style>
