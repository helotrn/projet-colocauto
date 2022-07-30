import AdminFilters from "@/locales/components/Admin/Filters";
import LoginBox from "@/locales/components/Login/Box";
import communities from "@/locales/models/communities";
import loanables from "@/locales/models/loanables";

export default {
  communities: communities.en,
  components: {
    admin: {
      filters: AdminFilters.en,
    },
    login: {
      box: LoginBox.en,
    }
  },
  forms: {
    actions: "actions",
    archiver: "archive",
    modifier: "modify",
    restaurer: "restore",
    supprimer: "delete",
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
