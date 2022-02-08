import { mount } from "@vue/test-utils";

import BootstrapVue from "bootstrap-vue";
import dayjs from "../../helpers/dayjs";
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

  it("Disables options using a function", () => {
    const wrapper = mount(TimeSelector, {
      propsData: {
        disabledTimesFct: (time) => {
          // Disable times before noon.
          return time.isBefore(dayjs().startOfDay().hour(12));
        },
      },
    });

    const options = wrapper.find("select").findAll("option");

    expect(options.at(0).attributes("disabled")).toBe("disabled");
    expect(options.at(47).attributes("disabled")).toBe("disabled");
    expect(options.at(48).attributes("disabled")).toBe(undefined);
    expect(options.at(95).attributes("disabled")).toBe(undefined);
  });
});
