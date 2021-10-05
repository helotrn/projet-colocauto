import Users from "./Users.vue";
import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import store from "../../store";
import Vuei18n from "vue-i18n";
import { BootstrapVue, BootstrapVueIcons } from "bootstrap-vue";
import router from "../../router";
import messages from "../../locales";
import { filters } from "../../helpers";

window.scrollTo = jest.fn();

let renderResponse;

describe("views.admin.Users", () => {
  beforeEach(() => {
    router.push("admin/users");
    renderResponse = render(Users, { store, router }, (vue) => {
      vue.use(Vuei18n, { filters });
      Object.keys(filters).forEach((f) => vue.filter(f, filters[f]));
      vue.use(BootstrapVue);
      vue.use(BootstrapVueIcons);
      const i18n = new Vuei18n({
        locale: "fr",
        fallbackLocale: "fr",
        formatFallbackMessages: true,
        messages: {
          fr: {
            ...messages.fr,
            ...messages.fr.users,
            ...messages.fr.forms,
          },
        },
      });
      return {
        i18n,
      };
    });
  });
  describe("Given the user filter by multiple names at different intervalles", () => {
    beforeEach(() => {});
    it("Should make calls that are matching the filter", async () => {
      await userEvent.click(screen.getByText("Filtres"));
      userEvent.keyboard(screen.getByLabelText("Nom complet"));
    });
  });
});
