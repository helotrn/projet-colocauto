import { mount } from "@vue/test-utils";

import BootstrapVue from "bootstrap-vue";
import CommunityUsersList from "./CommunityUsersList.vue";
import Vue from "vue";

Vue.use(BootstrapVue);

Vue.filter("capitalize", (value) => {
  if (!value) {
    return "";
  }
  const string = value.toString();
  return string.charAt(0).toUpperCase() + string.slice(1);
});

describe("CommunityUsersList", () => {
  it("Renders correctly by default", () => {
    const wrapper = mount(CommunityUsersList, {
      mocks: {
        // Mock translation
        $t: (msg) => msg,
      },
    });

    // No items, no fields should display table with body
    expect(wrapper.find("table").exists()).toBe(true);
    expect(wrapper.find("tbody").exists()).toBe(true);
    // Expect pagination to be rendered.
    expect(wrapper.find(".pagination.b-pagination").exists()).toBe(true);
  });

  it("Renders all fields by default", () => {
    const wrapper = mount(CommunityUsersList, {
      mocks: { $t: (msg) => msg },
    });

    const colHeaders = wrapper.findAll("table th");

    expect(colHeaders.at(0).text()).toBe("lists.id (Click to sort Ascending)");
    expect(colHeaders.at(1).text()).toBe("communities.fields.user.id");
    expect(colHeaders.at(2).text()).toBe("communities.fields.user.name (Click to sort Ascending)");
    expect(colHeaders.at(3).text()).toBe("communities.fields.community.id");
    expect(colHeaders.at(4).text()).toBe("communities.fields.community.name");
    expect(colHeaders.at(5).text()).toBe("communities.fields.user.role");
    expect(colHeaders.at(6).text()).toBe("communities.fields.user.approved_at");
    expect(colHeaders.at(7).text()).toBe("communities.fields.user.suspended_at");
    expect(colHeaders.at(8).text()).toBe("communities.fields.user.proof");
    expect(colHeaders.at(9).text()).toBe("communities.fields.user.actions");
  });

  it("Renders no field if visible fields is empty", () => {
    const wrapper = mount(CommunityUsersList, {
      mocks: { $t: (msg) => msg },
      propsData: {
        visibleFields: [],
      },
    });

    expect(wrapper.findAll("table th").length).toBe(0);
  });

  it("Renders visible fields only", () => {
    const wrapper = mount(CommunityUsersList, {
      mocks: { $t: (msg) => msg },
      propsData: {
        visibleFields: ["proof", "role", "id"],
      },
    });

    const colHeaders = wrapper.findAll("table th");

    expect(colHeaders.at(0).text()).toBe("communities.fields.user.proof");
    expect(colHeaders.at(1).text()).toBe("communities.fields.user.role");
    expect(colHeaders.at(2).text()).toBe("lists.id (Click to sort Ascending)");
  });

  it("Does not complain if a field is not defined", () => {
    const wrapper = mount(CommunityUsersList, {
      mocks: { $t: (msg) => msg },
      propsData: {
        visibleFields: ["proof", "role", "id", "undefined_field"],
      },
    });

    const colHeaders = wrapper.findAll("table th");

    expect(colHeaders.at(0).text()).toBe("communities.fields.user.proof");
    expect(colHeaders.at(1).text()).toBe("communities.fields.user.role");
    expect(colHeaders.at(2).text()).toBe("lists.id (Click to sort Ascending)");

    expect(wrapper.findAll("table th").length).toBe(3);
  });
});
