<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>Communautés</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">Créer une communauté</b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          striped hover :items="data"
          selectable select-mode="multi" @row-selected="rowSelected"
          :busy="loading" :fields="fields" no-local-sorting
          :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" no-sort-reset
          :show-empty="true" empty-text="Pas de communauté">
          <template v-slot:cell(actions)="row">
            <div class="text-right">
              <b-button size="sm" variant="primary" :to="`/admin/${slug}/${row.item.id}`">
                Modifier
              </b-button>
              <b-button size="sm" class="mr-1" variant="danger">Supprimer</b-button>
            </div>
          </template>
        </b-table>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

export default {
  name: 'AdminCommunities',
  mixins: [DataRouteGuards, ListMixin],
  data() {
    return {
      selected: [],
      fields: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
  },
};
</script>

<style lang="scss">
</style>
