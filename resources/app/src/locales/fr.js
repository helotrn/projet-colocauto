import AdminFilters from './components/Admin/Filters';
import loanables from '@/locales/models/loanables';

export default {
  'tableau de bord': 'tableau de bord',
  communities: communities.fr,
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
