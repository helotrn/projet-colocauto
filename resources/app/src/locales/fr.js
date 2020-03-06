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
  forms: {
    actions: 'actions',
    approuver: 'approuver',
    enregistrer: 'enregistrer',
    modifier: 'modifier',
    nouveau: 'nouveau',
    réinitialiser: 'réinitialiser',
    rétablir: 'rétablir',
    retirer: 'retirer',
    suspendre: 'suspendre',
    supprimer: 'supprimer',
  },
  pricings: {
    types: {
      car: 'voiture',
      bike: 'vélo',
      trailer: 'remorque',
      génerique: 'générique',
    },
  },
  loanables: loanables.fr,
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
      loans: "Historique d'emprunts",
      loanable: 'Mon véhicule',
      loanables: 'Mes véhicules',
    },
  },
  titles: {
    account: 'compte',
    admin: 'administration',
    borrower: 'emprunteur',
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
