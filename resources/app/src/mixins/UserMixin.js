export default {
  computed: {
    canLoanCar() {
      return (
        this.canLoanVehicle && !!this.user.borrower.approved_at && !this.user.borrower.suspended_at
      );
    },
    canLoanVehicle() {
      return (
        this.isLoggedIn &&
        this.user.borrower &&
        this.user.communities &&
        this.user.communities.reduce((acc, c) => acc || (!!c.approved_at && !c.suspended_at), false)
      );
    },
    hasCommunity() {
      return this.isLoggedIn && this.user.communities && this.user.communities.length > 0;
    },
    mainCommunity() {
      if (this.user.communities && this.user.communities.length > 0) {
        return this.user.communities[0];
      } else {
        return false;
      }
    },
    hasNotSubmittedProofOfResidency() {
      return (
        this.user.communities && !this.user.communities.reduce((acc, c) => acc && !!c.proof, true)
      );
    },
    waitingForProfileApproval() {
      return (
        this.isLoggedIn &&
        this.hasCompletedRegistration &&
        !this.hasNotSubmittedProofOfResidency &&
        !this.hasProfileApproved
      );
    },
    hasProfileApproved() {
      return (
        this.isLoggedIn &&
        this.user.communities &&
        this.user.communities.reduce((acc, c) => acc || (!!c.approved_at && !c.suspended_at), false)
      );
    },
    hasCompletedRegistration() {
      return this.isLoggedIn && this.isRegistered;
    },
    isGlobalAdmin() {
      return this.isLoggedIn && this.user.role === "admin";
    },
    isAdmin() {
      return (
        this.isLoggedIn &&
        (this.user.role === "admin" ||
          (this.user.communities && !!this.user.communities.find((c) => c.role === "admin")))
      );
    },
    isLoggedIn() {
      return !!this.user;
    },
    // Has finalized his account creation
    isRegistered() {
      const requiredFields = ["name", "date_of_birth", "address", "phone"];

      for (let i = 0, len = requiredFields.length; i < len; i += 1) {
        if (!this.user[requiredFields[i]]) {
          return false;
        }
      }

      return true;
    },
    user() {
      return this.$store.state.user;
    },
  },
  methods: {
    isAdminOfCommunity(community) {
      return !!this.user.communities.find((c) => c.id === community.id && c.role === "admin");
    },
  },
};
