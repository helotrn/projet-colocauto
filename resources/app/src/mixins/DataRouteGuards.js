function drillParams(object, vm) {
  return Object.keys(object).reduce((p, k) => {
    const newAcc = { ...p };

    if (['conditional', 'mapResults'].indexOf(k) > -1) {
      return newAcc;
    }

    if (typeof object[k] === 'function') {
      newAcc[k] = object[k]({
        user: vm.user,
        route: vm.$route,
      });
    } else if (typeof object[k] === 'object') {
      newAcc[k] = drillParams(object[k], vm);
    } else {
      newAcc[k] = object[k];
    }

    return newAcc;
  }, {});
}

export default {
  beforeRouteEnter(to, from, next) {
    if (to.meta && to.meta.data) {
      next((vm) => {
        vm.loadDataRoutesData(vm, to);
      });
    } else {
      next();
    }
  },
  computed: {
    routeDataLoaded() {
      if (this.reloading) {
        return true;
      }

      if (!this.$route.meta || !this.$route.meta.data) {
        return true;
      }

      const {
        $store: {
          state,
        },
        $route: {
          meta: {
            data,
          },
        },
      } = this;

      return Object.keys(data).reduce(
        (acc, collection) => {
          if (Object.keys(data[collection]).indexOf('options') !== -1) {
            return acc && !!state[collection].form;
          }

          return acc && !!state[collection].loaded;
        },
        true,
      );
    },
  },
  methods: {
    loadDataRoutesData(vm, to) {
      return Promise.all(
        Object.keys(to.meta.data)
          .reduce((acc, collection) => {
            const actions = Object.keys(to.meta.data[collection]);

            acc.push(...actions.map((action) => {
              const routeParams = to.meta.data[collection][action];

              const zeroedOutFilters = Object.keys(vm.$store.state[collection].filters || {})
                .reduce((filters, k) => ({
                  ...filters,
                  [k]: undefined,
                }), {});
              const params = {
                ...zeroedOutFilters,
                ...drillParams(routeParams, vm),
                ...vm.contextParams,
                ...vm.$route.query,
              };

              if (routeParams.conditional && !routeParams.conditional({
                user: vm.user,
                route: vm.$route,
              })) {
                return null;
              }

              return vm.$store.dispatch(
                `${collection}/${action}`,
                params,
              ).then(() => {
                if (routeParams.mapResults) {
                  vm.$store.commit(
                    `${collection}/data`,
                    vm.$store.state[collection].data
                      .map(routeParams.mapResults.bind({
                        user: vm.user,
                        route: vm.$route,
                      })),
                  );
                }
              });
            }));

            return acc;
          }, []),
      )
        .then(vm.dataRouteGuardsCallback)
        .catch((e) => {
          if (e.request) {
            switch (e.request.status) {
              case 401:
              default:
                vm.$store.commit('user', null);
                vm.$router.push(`/login?r=${vm.$route.fullPath}`);
            }
          }
        });
    },
  },
};
