<template>
  <div>
    <b-table
      striped
      hover
      no-local-sorting
      :busy="busy"
      :fields="fields"
      :items="items"
      :show-empty="true"
      :empty-text="$t('communities.user_list_empty_text') | capitalize"
      @sort-changed="$emit('changeOrder', $event)"
    >
      <template v-slot:cell(user_full_name)="row">
        <!-- Allow an URL-generating function in field definition -->
        <router-link v-if="row.field.urlFct" :to="row.field.urlFct(row.item)">
          {{ row.item.user_full_name }}
        </router-link>
        <template v-else>
          {{ row.item.user_full_name }}
        </template>
      </template>

      <template v-slot:cell(community_name)="row">
        <!-- Allow an URL-generating function in field definition -->
        <router-link v-if="row.field.urlFct" :to="row.field.urlFct(row.item)">
          {{ row.item.community_name }}
        </router-link>
        <template v-else>
          {{ row.item.community_name }}
        </template>
      </template>

      <template v-slot:cell(role)="row">
        <b-select
          :options="[
            { value: 'member', text: $t('communities.fields.user.role_labels.member') },
            { value: 'admin', text: $t('communities.fields.user.role_labels.admin') },
          ]"
          :value="row.item.role"
          @change="$emit('changeUserRole', row.item, $event)"
        />
      </template>

      <template v-slot:cell(approved_at)="row">
        <span>{{ row.item.approved_at | date }}</span>
      </template>

      <template v-slot:cell(suspended_at)="row">
        <span>{{ row.item.suspended_at | date }}</span>
      </template>

      <template v-slot:cell(proof)="row">
        <span v-if="row.item.proof" class="community-users-list__proof-of-residence">
          <a href="#" v-b-modal="`community-users-list__proof-of-residence--${row.item.id}`">
            {{ row.item.proof.original_filename }}
          </a>
          <b-modal
            size="xl"
            :id="`community-users-list__proof-of-residence--${row.item.id}`"
            :title="
              $t('communities.user_proof_of_residence', { user_full_name: row.item.user_full_name })
                | capitalize
            "
            footer-class="d-none"
            class="proof-of-residence-modal"
          >
            <img
              class="proof-of-residence-modal__proof-image"
              :src="row.item.proof.url"
              :alt="
                $t('communities.user_proof_of_residence', {
                  user_full_name: row.item.user_full_name,
                }) | capitalize
              "
            />
          </b-modal>
        </span>
      </template>

      <template v-slot:cell(actions)="row">
        <b-button
          v-if="canDoActions.find((i) => i.id == row.item.id)['approve']"
          :disabled="busy"
          size="sm"
          class="ml-1 mb-1"
          variant="primary"
          @click="$emit('action', row.item, 'approve')"
        >
          {{ $t("communities.fields.user.action_labels.approve") | capitalize }}
        </b-button>
        <b-button
          v-if="canDoActions.find((i) => i.id == row.item.id)['suspend']"
          :disabled="busy"
          size="sm"
          class="ml-1 mb-1"
          variant="warning"
          @click="$emit('action', row.item, 'suspend')"
        >
          {{ $t("communities.fields.user.action_labels.suspend") | capitalize }}
        </b-button>
        <b-button
          v-if="canDoActions.find((i) => i.id == row.item.id)['unsuspend']"
          :disabled="busy"
          size="sm"
          class="ml-1 mb-1"
          variant="success"
          @click="$emit('action', row.item, 'unsuspend')"
        >
          {{ $t("communities.fields.user.action_labels.unsuspend") | capitalize }}
        </b-button>

        <b-button
          v-if="canDoActions.find((i) => i.id == row.item.id)['remove']"
          :disabled="busy"
          size="sm"
          variant="danger"
          class="ml-1 mb-1"
          @click="$emit('action', row.item, 'remove')"
        >
          {{ $t("communities.fields.user.action_labels.remove") | capitalize }}
        </b-button>
      </template>
    </b-table>
    <b-pagination
      v-model="pageNum"
      :total-rows="totalItemCount"
      :per-page="itemsPerPage"
      @change="$emit('changePage', $event)"
    >
    </b-pagination>
  </div>
</template>

<script>
export default {
  name: "CommunityUsersList",
  props: {
    busy: {
      type: Boolean,
      required: false,
    },
    /* List of fields to display and their order. Show all by default. */
    visibleFields: {
      type: Array,
      required: false,
      default: () => {
        return [
          "id",
          "user_id",
          "user_full_name",
          "community_id",
          "community_name",
          "role",
          "approved_at",
          "suspended_at",
          "proof",
          "actions",
        ];
      },
    },
    fieldDefs: {
      type: Array,
      required: false,
      default: function () {
        return [
          { key: "id", label: this.$t("lists.id"), sortable: true },
          { key: "user_id", label: this.$t("communities.fields.user.id") },
          {
            key: "user_full_name",
            label: this.$t("communities.fields.user.name"),
            sortable: true,
            urlFct: function (item) {
              return `/admin/users/${item.user_id}`;
            },
          },
          { key: "community_id", label: this.$t("communities.fields.community.id") },
          {
            key: "community_name",
            label: this.$t("communities.fields.community.name"),
            urlFct: function (item) {
              return `/admin/communities/${item.community_id}`;
            },
          },
          { key: "role", label: this.$t("communities.fields.user.role") },
          { key: "approved_at", label: this.$t("communities.fields.user.approved_at") },
          { key: "suspended_at", label: this.$t("communities.fields.user.suspended_at") },
          { key: "proof", label: this.$t("communities.fields.user.proof") },
          { key: "actions", label: this.$t("communities.fields.user.actions") },
        ];
      },
    },
    items: {
      type: [Array, Function],
      required: false,
    },
    itemsPerPage: {
      type: Number,
      required: false,
    },
    /*
      Total number of items in the unpaginated list.
    */
    totalItemCount: {
      type: Number,
      required: false,
    },
  },
  computed: {
    fields() {
      let visibleFields = [];
      for (const fieldName of this.visibleFields) {
        const fieldDef = this.fieldDefs.find((f) => f.key === fieldName);
        if (fieldDef) visibleFields.push(fieldDef);
      }

      return visibleFields;
    },
    canDoActions() {
      let canDoActions = [];

      for (const item of this.items) {
        canDoActions.push({
          id: item.id,
          // Approval is possible if not alerady approved and not suspended.
          approve: !item.approved_at && !item.suspended_at,
          // Suspension is possible if approved and not already suspended.
          suspend: item.approved_at && !item.suspended_at,
          // Restoration of approval is possible if suspended.
          unsuspend: item.suspended_at,
          // It is always allowed to remove a user from a community.
          remove: true,
        });
      }

      return canDoActions;
    },
  },
  data() {
    return {
      pageNum: 1,
    };
  },
};
</script>

<style lang="scss">
.community-users-list {
  &__proof-of-residence {
    display: inline-block;
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
/*
  Let's consider the proof modal as an independant block.
*/
.proof-of-residence-modal {
  &__proof-image {
    max-width: 100%;
  }
}
</style>
