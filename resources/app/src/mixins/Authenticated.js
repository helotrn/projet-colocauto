export default {
  async beforeMount() {
    if (this.auth.token) {
      if (!this.$store.state.loaded && !this.$store.state.loading) {
        try {
          await this.$store.dispatch('loadUser');
          if (this.$route.name === 'login') {
            this.skipToApp();
          }
        } catch (e) {
          this.$store.commit('user', null);
          if (this.$route.meta.auth) {
            this.$router.push(`/login?r=${this.$route.fullPath}`);
          }
        }
      }
    } else if (this.$route.meta.auth) {
      this.$store.commit('user', null);
      this.$router.push(`/login?r=${this.$route.fullPath}`);
    }
  },
  computed: {
    allLoans() {
      return [
        ...(this.user.loanables || []).reduce((acc, loanable) => {
          acc.push(
            ...loanable.loans.filter(l => !l.canceled_at).map(l => ({
              ...l,
              loanable: {
                ...loanable,
                owner: {
                  ...this.user.owner,
                  user: this.user,
                },
              },
            })),
          );
          return acc;
        }, []),
        ...(this.user.loans || []).filter(l => !l.canceled_at),
      ];
    },
    auth() {
      const { token, refreshToken } = this.$store.state;
      return {
        token,
        refreshToken,
      };
    },
    hasOngoingLoans() {
      return this.ongoingLoans.length > 0;
    },
    hasUpcomingLoans() {
      return this.upcomingLoans.length > 0;
    },
    hasWaitingLoans() {
      return this.waitingLoans.length > 0;
    },
    ongoingLoans() {
      const now = this.$dayjs().format('YYYY-MM-DD HH:mm:ss');

      return this.allLoans
        .filter((l) => {
          if (l.actions.length <= 1) {
            return false;
          }

          const payment = l.actions.find(a => a.type === 'payment');
          return l.departure_at < now
            && (!payment || payment.status !== 'completed');
        });
    },
    pastLoans() {
      return this.allLoans
        .filter((l) => {
          if (l.actions.length <= 1) {
            return false;
          }

          const payment = l.actions.find(a => a.type === 'payment');
          return !!payment && payment.status === 'completed';
        })
        .slice(0, 3);
    },
    user() {
      return this.$store.state.user;
    },
    upcomingLoans() {
      const now = this.$dayjs().format('YYYY-MM-DD HH:mm:ss');

      return this.allLoans
        .filter(l => l.actions.length > 1 && l.departure_at > now
          && !l.actions.find(a => a.type === 'payment'));
    },
    waitingLoans() {
      return this.allLoans.filter(l => l.actions.length === 1);
    },
  },
  methods: {
    skipToLogin() {
      this.$router.replace('/login');
    },
    skipToApp() {
      if (this.$route.query.r) {
        this.$router.replace(this.$route.query.r);
      } else {
        this.$router.replace('/app');
      }
    },
  },
};
