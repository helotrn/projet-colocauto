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
          this.$router.push(`/login?r=${this.$route.fullPath}`);
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
        ...this.user.loanables.reduce((acc, loanable) => {
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
        ...this.user.loans.filter(l => !l.canceled_at),
      ];
    },
    auth() {
      const { token, refreshToken } = this.$store.state;
      return {
        token,
        refreshToken,
      };
    },
    canLoanCar() {
      return this.canLoanVehicle && this.user.borrower.approved_at;
    },
    canLoanVehicle() {
      return this.user.borrower
        && this.user.communities
          .reduce((acc, c) => acc || (c.approved_at && !c.suspended_at), false);
    },
    hasCommunity() {
      return this.isLoggedIn && this.user.communities && this.user.communities.length > 0;
    },
    hasCompletedRegistration() {
      return !!this.user.submitted_at;
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
    isAdmin() {
      return this.isLoggedIn && this.user.role === 'admin';
    },
    isLoggedIn() {
      return !!this.user;
    },
    isRegistered() {
      const requiredFields = [
        'name', 'last_name', 'date_of_birth',
        'address', 'postal_code', 'phone',
      ];

      for (let i = 0, len = requiredFields.length; i < len; i += 1) {
        if (!this.user[requiredFields[i]]) {
          return false;
        }
      }

      return true;
    },
    ongoingLoans() {
      const now = this.$dayjs().format('YYYY-MM-DD HH:mm:ss');

      return this.allLoans
        .filter(l => l.actions.length > 1
          && l.departure_at < now
          && !l.actions.find(a => a.type === 'payment'));
    },
    pastLoans() {
      return this.allLoans
        .filter(l => l.actions.length > 1 && !!l.actions.find(a => a.type === 'payment'))
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
    isAdminOfCommunity(community) {
      return !!this.user.communities.find(c => c.id === community.id && c.role === 'admin');
    },
    logout() {
      this.$store.dispatch('logout');

      this.$store.commit('addNotification', {
        content: "Vous n'êtes plus connecté à Locomotion. À bientôt!",
        title: 'Déconnexion réussie.',
        variant: 'success',
        type: 'logout',
      });

      this.$router.push('/');
    },
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
