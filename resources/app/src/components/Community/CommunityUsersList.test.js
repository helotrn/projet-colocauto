import { mount } from "@vue/test-utils";

import BootstrapVue from "bootstrap-vue";
import CommunityUsersList from "./CommunityUsersList.vue";
import Vue from "vue";

Vue.use(BootstrapVue);

describe("CommunityUsersList", () => {
  it("Renders correctly by default", () => {
    const wrapper = mount(CommunityUsersList, {});

    // No items, no fields should display table with body
    expect(wrapper.find("table").exists()).toBe(true);
    expect(wrapper.find("tbody").exists()).toBe(true);
  });
});
