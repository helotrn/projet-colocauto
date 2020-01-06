export default function RestModule(slug, state) {
  return {
    state: {
      ...state,
      ajax: null,
      data: [],
      dataById: {},
      loaded: false,
      loading: true,
      search: [],
      slug,
      total: undefined,
    },
    mutations: {

    },
    actions: {
    },
  };
}
