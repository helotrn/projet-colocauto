import AdminFilters from './components/Admin/Filters';
import bikes from '@/locales/models/bikes';
import borrowers from '@/locales/models/borrowers';
import communities from '@/locales/models/communities';
import loanables from '@/locales/models/loanables';
import users from '@/locales/models/users';

export default {
  bikes: bikes.fr,
  borrowers: borrowers.fr,
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
    nouveau: 'nouveau',
    réinitialiser: 'réinitialiser',
    rétablir: 'rétablir',
    retirer: 'retirer',
    suspendre: 'suspendre',
    supprimer: 'supprimer',
  },
  locales: {
    en: 'English',
    fr: 'Français',
  },
  profile: {
    titles: {
      account: 'Mon profil Locomotion',
      borrower: "Mon profil d'emprunteur",
      communities: 'Mes communautés',
      payment: 'Mes factures',
      reservations: 'Historique des réservations',
      loanable: 'Mon véhicule',
      loanables: 'Mes véhicules',
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
