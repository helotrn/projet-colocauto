import AdminFilters from '@/locales/components/Admin/Filters';
import bikes from '@/locales/models/bikes';
import borrowers from '@/locales/models/borrowers';
import cars from '@/locales/models/cars';
import communities from '@/locales/models/communities';
import faq from '@/locales/faq';
import invoices from '@/locales/models/invoices';
import loans from '@/locales/models/loans';
import loanables from '@/locales/models/loanables';
import padlocks from '@/locales/models/padlocks';
import paymentMethods from '@/locales/models/paymentMethods';
import tags from '@/locales/models/tags';
import trailers from '@/locales/models/trailers';
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
  faq: faq.fr,
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
  invoices: invoices.fr,
  loans: loans.fr,
  loanables: loanables.fr,
  locales: {
    en: 'English',
    fr: 'Français',
  },
  padlocks: padlocks.fr,
  paymentMethods: paymentMethods.fr,
  pricings: {
    types: {
      car: 'voiture',
      bike: 'vélo',
      trailer: 'remorque',
      génerique: 'générique',
    },
  },
  profile: {
    titles: {
      account: 'Mon profil LocoMotion',
      borrower: 'Mon dossier de conduite',
      communities: 'Mes voisinages',
      invoice: 'Ma facture',
      invoices: 'Mes factures',
      loans: "Historique d'emprunts",
      loanable: 'Mon véhicule',
      loanables: 'Mes véhicules',
      payment_method: 'Mon mode de paiement',
      payment_methods: 'Mes modes de paiement',
    },
  },
  tags: tags.fr,
  titles: {
    account: 'compte',
    admin: 'administration',
    borrower: 'emprunteur',
    communities: 'communautés',
    'communities-overview': 'communautés',
    community: 'communauté',
    dashboard: 'tableau de bord',
    faq: 'foire aux questions',
    find_vehicle: 'trouve un véhicule',
    insurance: 'assurances Desjardins',
    invoice: 'facture',
    invoices: 'factures',
    loan: 'emprunt',
    loans: 'emprunts',
    loanable: 'véhicule',
    loanables: 'véhicules',
    login: 'se connecter',
    padlock: 'Cadenas',
    padlocks: 'Cadenas',
    password: 'Mot de passe',
    'password-request': 'Réinitialisation de mot de passe',
    'password-reset': 'Réinitialisation de mot de passe',
    payment_method: 'méthode de paiement',
    payment_methods: 'méthodes de paiement',
    privacy: "conditions générales d'utilisation",
    profile: 'Profil',
    register: "s'inscrire",
    'register-map': 'choisir un voisinage',
    tags: 'Mots-clés',
    tag: 'Mot-clé',
    user: 'membre',
    users: 'membres',
  },
  trailers: trailers.fr,
  users: users.fr,
};
