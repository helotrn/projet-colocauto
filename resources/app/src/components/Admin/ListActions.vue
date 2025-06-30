<template>
  <div class="admin-list-actions text-right">
    <b-button
      v-if="hasColumn('view')"
      size="sm"
      variant="success"
      :to="`/admin/${slug}/${row.item.id}`"
    >
      {{ $t("afficher") | capitalize }}
    </b-button>
    <b-button
      v-if="hasColumn('edit')"
      size="sm"
      variant="primary"
      :to="`/admin/${slug}/${row.item.id}`"
    >
      {{ $t("modifier") | capitalize }}
    </b-button>
    <b-button
      v-if="hasColumn('destroy') && !row.item.deleted_at"
      size="sm"
      class="mr-1"
      variant="outline-primary"
      @click="$emit('destroy')"
    >
      {{ $t("archiver") | capitalize }}
    </b-button>
    <b-button
      v-if="hasColumn('restore') && !!row.item.deleted_at"
      size="sm"
      class="mr-1"
      variant="outline-primary"
      @click="$emit('restore')"
    >
      {{ $t("restaurer") | capitalize }}
    </b-button>
  </div>
</template>

<script>
import locales from "@/locales";

export default {
  name: "AdminListActions",
  props: {
    columns: {
      type: Array,
      required: false,
      default() {
        return ["edit", "destroy", "restore"];
      },
    },
    row: {
      type: Object,
      required: true,
    },
    slug: {
      required: true,
      type: String,
    },
  },
  methods: {
    hasColumn(name) {
      return this.columns.indexOf(name) !== -1;
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
