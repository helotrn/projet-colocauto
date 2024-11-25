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
      loading: false,
      ownerQuery: {
        slug: "users",
        value: "owner.id",
        text: "full_name",
        details: "email",
        params: {
          fields:
            "full_name,avatar,owner.id,communities.id,communities.name,available_loanable_types,communities.area",
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
  methods: {

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
