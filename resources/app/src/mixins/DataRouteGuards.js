/*
  drillParams recursively maps object properties onto a new object of the same
  structures.

  Function properties:
    Functions will be called with params (user, route), and the return value
    will be stored in the new object.
*/
function drillParams(object, vm) {
  return Object.keys(object).reduce((p, k) => {
    const newAcc = { ...p };

    // Conditional and mapResults are called before and after action dispatch
    // and are not part of params.
    if (["conditional", "mapResults"].indexOf(k) > -1) {
      return newAcc;
    }

    if (typeof object[k] === "function") {
      // Call function and set result
      newAcc[k] = object[k]({
        user: vm.$store.state.user,
        route: vm.$route,
      });
    } else if (typeof object[k] === "object") {
      // Recursively drill down object properties.
      newAcc[k] = drillParams(object[k], vm);
    } else {
      // Keep arrays strings or singletons as they are.
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
    /*
     * In case of an 'options' call, it justs
     * checks for a "form" attribute in the state.
     *
     * ex1: for users options: state.users.form has to exists
     */
    routeDataLoaded() {
      // According to Molotov, this is a particular case and it was not kept in
      // later versions of DataRouteGuards.
      if (this.reloading) {
        return true;
      }

      // meta.data specifies what data should be loaded for a given route. No
      // meta.data: noting to load, hence considered implicitely loaded.
      if (!this.$route.meta || !this.$route.meta.data) {
        return true;
      }

      const {
        $store: { state },
        $route: {
          meta: { data },
        },
      } = this;

      return Object.keys(data).reduce((acc, collection) => {
        const actions = Object.keys(data[collection]);

        if (actions.indexOf("options") !== -1) {
          return acc && !!state[collection].form;
        }

        return acc;
      }, true);
    },
  },
  methods: {
    reloadDataRoutesData() {
      return this.loadDataRoutesData(this, this.$route);
    },
    loadDataRoutesData(vm, to) {
      return Promise.all(
        Object.keys(to.meta.data).reduce((acc, collection) => {
          // Each element of the collection is an action.
          const actions = Object.keys(to.meta.data[collection]);

          acc.push(
            ...actions.map((action) => {
              const routeParams = to.meta.data[collection][action];

              const zeroedOutFilters = Object.keys(
                vm.$store.state[collection].filters || {}
              ).reduce(
                (filters, k) => ({
                  ...filters,
                  [k]: undefined,
                }),
                {}
              );

              const params = {
                ...zeroedOutFilters,
                ...drillParams(routeParams, vm),
                ...vm.contextParams,
                ...vm.$route.query,
              };

              // Don't fetch data for conditional routes with false condition.
              if (
                routeParams.conditional &&
                !routeParams.conditional({
                  user: vm.user,
                  route: vm.$route,
                })
              ) {
                return null;
              }

              return vm.$store
                .dispatch(
                  // Dispatching this action fetches the data. See RestModule.
                  `${collection}/${action}`,
                  params
                )
                .then(() => {
                  // Transform the item if necessary.
                  if (routeParams.mapResults) {
                    vm.$store.commit(
                      `${collection}/data`,
                      vm.$store.state[collection].data.map(
                        routeParams.mapResults.bind({
                          user: vm.user,
                          route: vm.$route,
                        })
                      )
                    );
                  }
                });
            })
          );

          return acc;
        }, [])
      )
        .then(vm.dataRouteGuardsCallback)
        .catch((e) => {
          if (e.request) {
            switch (e.request.status) {
              case 404:
                vm.$store.commit("addNotification", {
                  content: "Une ressource requise pour cette page est introuvable.",
                  title: "Ressource introuvable",
                  variant: "danger",
                  type: "route_data",
                });
                vm.$router.push("/app");
                break;
              case 422:
              case 400:
                vm.$store.commit("addNotification", {
                  content: "Une requête obligatoire pour une ressource de cette page a échoué.",
                  title: "Mauvaise requête",
                  variant: "danger",
                  type: "route_data",
                });
                vm.$router.push("/app");
                break;
              case 500:
                vm.$store.commit("addNotification", {
                  content: "Une erreur système s'est produite.",
                  title: "Erreur fatale",
                  variant: "danger",
                  type: "route_data",
                });
                vm.$router.push("/app");
                break;
              case 401:
              default:
                vm.$store.commit("user", null);
                vm.$router.push(`/login?r=${vm.$route.fullPath}`);
            }
          }
        });
    },
  },
};
