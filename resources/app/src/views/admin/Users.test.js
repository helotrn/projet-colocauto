import Users from "./Users";
import store from "../../store";
import { BootstrapVue } from "bootstrap-vue";
import router from "../../router";
import { filters } from "../../helpers";
import { shallowMount } from "@vue/test-utils";
import Vue from "vue";
import axios from "axios";
import VueAxios from "vue-axios";

window.scrollTo = jest.fn();
jest.mock("axios");
axios.CancelToken = { source: () => ({ token: "token" }) };
Object.keys(filters).forEach((f) => Vue.filter(f, filters[f]));
Vue.use(BootstrapVue);
Vue.use(VueAxios, axios);

let mountedUsers;

describe("views.admin.Users", () => {
  beforeEach(() => {
    router.push("/admin/users");
    axios.get = jest.fn(async () => {
      // We simulate the the api calls will take 100 ms
      await new Promise((resolve) => setTimeout(resolve, 100));
      return { data: "data", total: 1, lastPage: 1 };
    });

    store.replaceState({
      ...store.state,
      users: {
        filters: {
          id: "number",
          created_at: "date",
          full_name: "text",
          email: "text",
          deleted_at: "date",
          "communities.name": "text",
        },
        params: {
          order: "name",
          page: 1,
          per_page: 10,
          q: "",
          type: null,
          full_name: "",
        },
        loaded: true,
      },
    });

    mountedUsers = shallowMount(Users, {
      store,
      router,
      mocks: {
        $t: () => {},
        $tc: () => {},
      },
    });
  });

  describe("Given many changes in the filters", () => {
    beforeEach(async () => {
      mountedUsers.vm.setParam({ name: "full_name", value: "m" });
    });
    it("should call the correct api after the debounce", async () => {
      await new Promise((resolve) => setTimeout(resolve, 250));
      expect(axios.get).toHaveBeenCalled();
      mountedUsers.vm.setParam({ name: "full_name", value: "ma" });
      // should call the api a second time even if the previous
      // request did not come back yet
      await new Promise((resolve) => setTimeout(resolve, 600));
      expect(axios.get).toHaveBeenCalledTimes(2);
    });
  });
});
