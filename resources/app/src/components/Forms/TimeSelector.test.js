import { mount } from "@vue/test-utils";

import BootstrapVue from "bootstrap-vue";
import TimeSelector from "./TimeSelector.vue";
import Vue from "vue";

Vue.use(BootstrapVue);

describe("TimeSelector", () => {
  it("Creates options", () => {
    // mount() returns a wrapped Vue component we can interact with
    const wrapper = mount(TimeSelector, {
      propsData: {},
    });

    const options = wrapper.find("select").findAll("option");

    expect(options.length).toBe(96);
    expect(options.at(0).text()).toBe("00:00");
    expect(options.at(-1).text()).toBe("23:45");
  });
});
