import communities from '@/locales/models/communities';
import loanables from '@/locales/models/loanables';
import AdminFilters from './components/Admin/Filters';

export default {
  communities: communities.fr,
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
  loanables: loanables.en,
  locales: {
    en: 'English',
    fr: 'Français',
  },
  profile: {
    titles: {
      account: 'Account informations',
      payment: 'Payment',
      reservations: 'Reservations',
      vehicles: 'Vehicles',
    },
  },
  titles: {
    community: 'community | communities',
    dashboard: 'dashboard',
    login: 'login',
    account: 'Account',
  },
};
