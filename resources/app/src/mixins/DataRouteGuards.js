import store from '@/store';

export default {
  beforeRouteEnter(to, from, next) {
    if (to.meta && to.meta.data) {
      return Promise.all(
        Object.keys(to.meta.data)
          .reduce((acc, collection) => {
            const actions = Object.keys(to.meta.data[collection]);

            acc.push(...actions.map(action => store.dispatch(
              `${collection}/${action}`,
              to.meta.data[collection][action],
            )));

            return acc;
          }, []),
      )
        .then(next)
        .catch((e) => {
          if (e.request) {
            switch (e.request.status) {
              case 401:
              default:
                this.$store.commit('addNotification', {
                  content: 'Veuillez vous connecter pour accéder à cette page.',
                  title: 'Non connecté.',
                  variant: 'danger',
                  type: 'not_logged_in',
                });
            }
          }
        });
    }

    return true;
  },
};
