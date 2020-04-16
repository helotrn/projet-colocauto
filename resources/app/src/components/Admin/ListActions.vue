<template>
  <div class="admin-actions text-right">
    <b-button size="sm" variant="primary" :to="`/admin/${slug}/${row.item.id}`">
      {{ $t('modifier') | capitalize }}
    </b-button>
    <b-button v-if="!row.item.deleted_at"
      size="sm" class="mr-1" variant="danger"
      @click="$emit('destroy')">
      {{ $t('supprimer') | capitalize }}
    </b-button>
    <b-button v-else
      size="sm" class="mr-1" variant="warning"
      @click="$emit('restore')">
      {{ $t('restaurer') | capitalize }}
    </b-button>
  </div>
</template>

<script>
export default {
  name: 'AdminListActions',
  props: {
    row: {
      type: Object,
      required: true,
    },
    slug: {
      required: true,
      type: String,
    },
  },
};
</script>

<style lang="scss">
</style>
