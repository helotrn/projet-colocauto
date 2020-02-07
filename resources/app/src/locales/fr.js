import AdminFilters from './components/Admin/Filters';
import bikes from '@/locales/models/bikes';
import communities from '@/locales/models/communities';
import loanables from '@/locales/models/loanables';
import users from '@/locales/models/users';

export default {
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
    enregistrer: 'enregistrer',
    modifier: 'modifier',
    réinitialiser: 'réinitialiser',
    supprimer: 'supprimer',
  },
  locales: {
    en: 'English',
    fr: 'Français',
  },
  profile: {
    titles: {
      account: 'Informations du compte',
      payment: 'Paiement',
      reservations: 'Historique des réservations',
      vehicles: 'Mes véhicules',
    },
  },
  titles: {
    account: 'compte',
    admin: 'administration',
    communities: 'communautés',
    community: 'communauté',
    dashboard: 'tableau de bord',
    loanable: 'véhicule',
    loanables: 'véhicules',
    login: 'se connecter',
    profile: 'Profil',
    register: "s'inscrire",
  },
  users: users.fr,
};
