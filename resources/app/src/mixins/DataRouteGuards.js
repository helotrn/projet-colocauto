import store from '@/store';

function drillParams(object, vm) {
  return Object.keys(object).reduce((p, k) => {
    const newAcc = { ...p };

    if (typeof object[k] === 'function') {
      newAcc[k] = object[k]({
        user: vm.user,
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

            const params = drillParams(routeParams, vm);

            return store.dispatch(
              `${collection}/${action}`,
              params,
            );
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
