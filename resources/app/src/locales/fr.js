import AdminFilters from './components/Admin/Filters';

export default {
  'tableau de bord': 'tableau de bord',
  communities: {
    communauté: 'communauté | communautés',
    'créer une communauté': 'créer une communauté',
    '{count} communauté sélectionnée': 'aucune communauté sélectionnée | {count} communauté sélectionnée | {count} communautés sélectionnées',
    fields: {
      id: 'ID',
      name: 'nom',
      type: 'type',
    },
    types: {
      neighborhood: 'voisinage',
      null: "n'importe quel type",
      borough: 'quartier',
      private: 'privée',
    },
  },
  components: {
    admin: {
      filters: AdminFilters.fr,
    },
  },
  loanables: {
    types: {
      bike: 'vélo',
      car: 'voiture',
      null: "n'importe quel type",
      trailer: 'remorque',
    },
  },
  forms: {
    actions: 'actions',
    modifier: 'modifier',
    supprimer: 'supprimer',
  },
  locales: {
    en: 'English',
    fr: 'Français',
  },
  titles: {
    admin: 'administration',
    community: 'communauté',
    communities: 'communautés',
    loanables: 'véhicules',
    login: 'se connecter',
  },
};
