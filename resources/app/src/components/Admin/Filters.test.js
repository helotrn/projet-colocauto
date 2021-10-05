import filterElement from "./Filters.vue";
import { render, screen, prettyDOM } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import store from "../../store";
import Vuei18n from "vue-i18n";
import { BootstrapVue, BootstrapVueIcons } from "bootstrap-vue";
import router from "../../router";
import messages from "../../locales";
import { filters } from "../../helpers";

jest.spyOn(filterElement.methods, "setParam");

describe("views.admin.Users", () => {
  beforeEach(() => {
    const props = {
      filters: {
        id: "number",
        created_at: "date",
        full_name: "text",
        email: "text",
        deleted_at: "date",
        "communities.name": "text",
      },
      entity: "users",
      params: {
        order: "name",
        page: 1,
        per_page: 10,
        q: "",
        type: null,
        full_name: "",
      },
    };

    render(filterElement, { store, router, props }, (vue) => {
      vue.use(Vuei18n, { filters });
      Object.keys(filters).forEach((f) => vue.filter(f, filters[f]));
      vue.prototype.$filters = filters;

      vue.use(BootstrapVue);
      vue.use(BootstrapVueIcons);
      const i18n = new Vuei18n({
        locale: "fr",
        fallbackLocale: "fr",
        formatFallbackMessages: true,
        messages: {
          fr: {
            ...messages.fr,
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
    it("Should trigger events that are matching the filter", async () => {
      userEvent.click(screen.getByText("Filtres"));
      await new Promise((resolve) => setTimeout(resolve, 0));
      userEvent.type(screen.getByLabelText("Nom complet"), "ma");
      expect(filterElement.methods.setParam.mock.calls.length).toBe(2);
      expect(filterElement.methods.setParam.mock.calls[1][1]).toBe("ma");
    });
  });
});
