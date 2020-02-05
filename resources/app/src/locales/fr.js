import AdminFilters from './components/Admin/Filters';
import bikes from '@/locales/models/bikes';
import communities from '@/locales/models/communities';
import loanables from '@/locales/models/loanables';

export default {
  'tableau de bord': 'tableau de bord',
  bikes: bikes.fr,
  communities: communities.fr,
  components: {
    admin: {
      filters: AdminFilters.fr,
    },
  },
  loanables: loanables.fr,
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
    loanable: 'véhicule',
    loanables: 'véhicules',
    login: 'se connecter',
  },
};
