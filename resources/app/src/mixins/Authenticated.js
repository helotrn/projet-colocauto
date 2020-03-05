export default {
  async beforeMount() {
    if (this.auth.token) {
      if (!this.$store.state.loaded && !this.$store.state.loading) {
        try {
          await this.$store.dispatch('loadUser');
        } catch (e) {
          this.$router.push('/login');
        }
      }
    } else if (this.$route.meta.auth) {
      this.$router.push('/login');
    }
  },
  computed: {
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
      return [];
    },
    pastLoans() {
      return [];
    },
    user() {
      return this.$store.state.user;
    },
    upcomingLoans() {
      const now = this.$dayjs().format('YYYY-MM-DD HH:mm:ss');

      return this.user.loanables.reduce((acc, loanable) => {
        acc.push(...loanable.loans.filter(l => l.departure_at > now));
        return acc;
      }, []);
    },
    waitingLoans() {
      return [];
    },
  },
  methods: {
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
    skipToLogin(from) {
      this.$router.replace('/login', { from });
    },
    skipToApp() {
      this.$router.replace('/app');
    },
  },
};
