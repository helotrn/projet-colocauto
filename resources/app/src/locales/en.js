import AdminFilters from './components/Admin/Filters';

export default {
  'tableau de bord': 'dashboard',
  communities: {
    communauté: 'community | communities',
    'créer une communauté': 'create a community',
    '{count} communauté sélectionnée': 'no community selected | {count} community selected | {count} communities selected',
    fields: {
      id: 'ID',
      name: 'name',
      type: 'type',
    },
    types: {
      neighborhood: 'neighborhood',
      null: 'any type',
      borough: 'borough',
      private: 'private',
    },
  },
  components: {
    admin: {
      filters: AdminFilters.en,
    },
  },
  forms: {
    actions: 'actions',
    modifier: 'modify',
    supprimer: 'delete',
  },
  locales: {
    en: 'English',
    fr: 'Français',
  },
  titles: {
    community: 'community | communities',
    login: 'login',
  },
};
