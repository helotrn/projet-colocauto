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
        Promise.all(
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
          .catch((e) => {
            if (e.request) {
              switch (e.request.status) {
                case 401:
                default:
                  vm.$router.push('/login');
              }
            }
          });
      });
    }

    return true;
  },
  computed: {
    routeDataLoaded() {
      return Object.keys(this.$route.meta.data).reduce(
        (acc, k) => acc && this.$store.state[k].loaded,
        true,
      );
    },
  },
};
