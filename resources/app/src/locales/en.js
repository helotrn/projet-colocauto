import AdminFilters from "@/locales/components/Admin/Filters";
import LoginBox from "@/locales/components/Login/Box";
import RegisterForm from "@/locales/components/Register/RegisterForm";
import Dashboard from "@/locales/views/Dashboard";
import communities from "@/locales/models/communities";
import loanables from "@/locales/models/loanables";
import invitations from "@/locales/models/invitations";
import expenses from "@/locales/models/expenses";

export default {
  communities: communities.en,
  invitations: invitations.en,
  expenses: expenses.en,
  components: {
    admin: {
      filters: AdminFilters.en,
    },
    login: {
      box: LoginBox.en,
    },
    register: {
      registerform: RegisterForm.en,
    },
  },
  views: {
    dashboard: Dashboard.en,
  },
  forms: {
    actions: "actions",
    archiver: "archive",
    modifier: "modify",
    restaurer: "restore",
    supprimer: "delete",
  },
  lists: {
    // Generic label for list ids.
    id: "ID",
  },
  loanables: loanables.en,
  locales: {
    en: "English",
    fr: "Français",
  },
  profile: {
    titles: {
      account: "Account informations",
      payment: "Payment",
      reservations: "Reservations",
      vehicles: "Vehicles",
    },
  },
  titles: {
    community: "community | communities",
    dashboard: "dashboard",
    login: "login",
    account: "Account",
  },
};
