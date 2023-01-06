import AdminFilters from "@/locales/components/Admin/Filters";
import DashboardBalance from "@/locales/components/Dashboard/Balance";
import LoginBox from "@/locales/components/Login/Box";
import RegisterForm from "@/locales/components/Register/RegisterForm";
import UserEmailForm from "@/locales/components/User/EmailForm";
import UserPasswordForm from "@/locales/components/User/PasswordForm";
import Dashboard from "@/locales/views/Dashboard";
import Home from "@/locales/views/Home";
import PasswordRequest from "@/locales/views/Password/Request";
import PasswordReset from "@/locales/views/Password/Reset";
import AccountProfile from "@/locales/views/Profile/Account";
import bikes from "@/locales/models/bikes";
import borrowers from "@/locales/models/borrowers";
import cars from "@/locales/models/cars";
import communities from "@/locales/models/communities";
import faq from "@/locales/faq";
import invoices from "@/locales/models/invoices";
import loans from "@/locales/models/loans";
import loanables from "@/locales/models/loanables";
import padlocks from "@/locales/models/padlocks";
import paymentMethods from "@/locales/models/paymentMethods";
import tags from "@/locales/models/tags";
import trailers from "@/locales/models/trailers";
import users from "@/locales/models/users";
import invitations from "@/locales/models/invitations";
import expenses from "@/locales/models/expenses";
import expense_tags from "@/locales/models/expense_tags";
import refunds from "@/locales/models/refunds";

export default {
  bikes: bikes.fr,
  borrowers: borrowers.fr,
  cars: cars.fr,
  communities: communities.fr,
  invitations: invitations.fr,
  expenses: expenses.fr,
  expense_tags: expense_tags.fr,
  refunds: refunds.fr,
  components: {
    admin: {
      filters: AdminFilters.fr,
    },
    dashboard: {
      balance: DashboardBalance.fr,
    },
    login: {
      box: LoginBox.fr,
    },
    register: {
      registerform: RegisterForm.fr,
    },
    user: {
      emailform: UserEmailForm.fr,
      passwordform: UserPasswordForm.fr,
    },
  },
  views: {
    dashboard: Dashboard.fr,
    home: Home.fr,
    profile: {
      account: AccountProfile.fr,
    },
    password: {
      request: PasswordRequest.fr,
      reset: PasswordReset.fr,
    },
  },
  faq: faq.fr,
  forms: {
    actions: "actions",
    afficher: "afficher",
    archiver: "archiver",
    enregistrer: "enregistrer",
    facultatif: "facultatif",
    modifier: "modifier",
    nouveau: "nouveau",
    réinitialiser: "réinitialiser",
    restaurer: "restaurer",
    rétablir: "rétablir",
    retirer: "retirer",
    supprimer: "supprimer",
  },
  invoices: invoices.fr,
  lists: {
    // Generic label for list ids.
    id: "ID",
  },
  loans: loans.fr,
  loanables: loanables.fr,
  locales: {
    en: "English",
    fr: "Français",
  },
  padlocks: padlocks.fr,
  paymentMethods: paymentMethods.fr,
  pricings: {
    types: {
      car: "voiture",
      bike: "vélo",
      trailer: "remorque",
      génerique: "générique",
    },
  },
  profile: {
    titles: {
      account: "Mon compte",
      borrower: "Mon dossier de conduite",
      communities: "Mes voisinages",
      residency_proof: "Ma preuve de résidence",
      invoice: "Ma facture",
      invoices: "Mes factures",
      loans: "Historique d'emprunts",
      loanable: "Mon véhicule",
      loanables: "Mes véhicules",
      payment_method: "Mon mode de paiement",
      payment_methods: "Mes modes de paiement",
      profile: "Mon profil LocoMotion",
    },
  },
  wallet: {
    titles: {
      expenses: "Dépenses",
      wallet: "Portefeuille",
      refunds: "Remboursements",
      balance: "Équilibre",
    }
  },
  tags: tags.fr,
  titles: {
    account: "compte",
    admin: "administration",
    borrower: "dossier de conduite",
    communities: "voisinages",
    "communities-overview": "voisinages",
    community: "voisinage",
    dashboard: "tableau de bord",
    faq: "foire aux questions",
    find_vehicle: "réserver un véhicule",
    insurance: "assurances Desjardins",
    invoice: "facture",
    invoices: "factures",
    loan: "emprunt",
    loans: "emprunts",
    loanable: "véhicule",
    loanables: "véhicules",
    login: "se connecter",
    padlock: "Cadenas",
    padlocks: "Cadenas",
    password: "Mot de passe",
    "password-request": "Réinitialisation de mot de passe",
    "password-reset": "Réinitialisation de mot de passe",
    payment_method: "mode de paiement",
    payment_methods: "modes de paiement",
    privacy: "conditions générales d'utilisation",
    profile: "profil",
    register: "s'inscrire",
    "register-map": "rejoindre mes voisin-e-s",
    tags: "Mots-clés",
    tag: "Mot-clé",
    user: "membre",
    users: "membres",
    invitations: "Invitations",
    invitation: "Invitation",
    expenses: "Dépenses",
    expense: "Dépense",
    expense_tags: "Types de dépenses",
    expense_tag: "Type de dépense",
    refunds: "Remboursements",
    refund: "Remboursement",
  },
  trailers: trailers.fr,
  users: users.fr,
};
