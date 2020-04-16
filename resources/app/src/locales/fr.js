import AdminFilters from './components/Admin/Filters';
import bikes from '@/locales/models/bikes';
import borrowers from '@/locales/models/borrowers';
import cars from '@/locales/models/cars';
import communities from '@/locales/models/communities';
import invoices from '@/locales/models/invoices';
import loans from '@/locales/models/loans';
import loanables from '@/locales/models/loanables';
import padlocks from '@/locales/models/padlocks';
import paymentMethods from '@/locales/models/paymentMethods';
import tags from '@/locales/models/tags';
import users from '@/locales/models/users';

export default {
  bikes: bikes.fr,
  borrowers: borrowers.fr,
  cars: cars.fr,
  communities: communities.fr,
  components: {
    admin: {
      filters: AdminFilters.fr,
    },
  },
  invoices: invoices.fr,
  forms: {
    actions: 'actions',
    afficher: 'afficher',
    approuver: 'approuver',
    enregistrer: 'enregistrer',
    facultatif: 'facultatif',
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
  loans: loans.fr,
  loanables: loanables.fr,
  locales: {
    en: 'English',
    fr: 'Français',
  },
  padlocks: padlocks.fr,
  paymentMethods: paymentMethods.fr,
  profile: {
    titles: {
      account: 'Mon profil Locomotion',
      borrower: "Mon profil d'emprunteur",
      communities: 'Mes communautés',
      invoice: 'Ma facture',
      invoices: 'Mes factures',
      loans: "Historique d'emprunts",
      loanable: 'Mon véhicule',
      loanables: 'Mes véhicules',
      payment_method: 'Ma méthode de paiement',
      payment_methods: 'Mes méthodes de paiement',
    },
  },
  tags: tags.fr,
  titles: {
    account: 'compte',
    admin: 'administration',
    borrower: 'emprunteur',
    communities: 'communautés',
    community: 'communauté',
    dashboard: 'tableau de bord',
    find_vehicle: 'trouve un véhicule',
    invoice: 'facture',
    invoices: 'factures',
    loan: 'emprunt',
    loans: 'emprunts',
    loanable: 'véhicule',
    loanables: 'véhicules',
    login: 'se connecter',
    padlock: 'Cadenas',
    padlocks: 'Cadenas',
    payment_method: 'méthode de paiement',
    payment_methods: 'méthodes de paiement',
    profile: 'Profil',
    register: "s'inscrire",
    tags: 'Mots-clés',
    tag: 'Mot-clé',
    user: 'membre',
    users: 'membres',
  },
  users: users.fr,
};
