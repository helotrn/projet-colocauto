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
        this.user.accept_conditions &&
        this.user.borrower &&
        this.user.communities &&
        this.user.communities.reduce((acc, c) => acc || (!!c.approved_at && !c.suspended_at && c.loanables_count > 0), false)
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
    hasSubmittedProofOfResidency() {
      return this.user.communities?.reduce((hasProofForSomeCommunity, c) => {
        return hasProofForSomeCommunity || this.userHasProofForCommunity(c);
      }, false);
    },
    waitingForProfileApproval() {
      return (
        this.isLoggedIn &&
        this.hasCompletedRegistration &&
        this.hasSubmittedProofOfResidency &&
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
    isCommunityAdmin() {
      return this.isLoggedIn && this.user.role === "community_admin";
    },
    isAdmin() {
      return (
        this.isLoggedIn &&
        (this.user.role === "admin" ||
          this.user.role === "community_admin" ||
          (this.user.communities && !!this.user.communities.find((c) => c.role === "admin")))
      );
    },
    isLoggedIn() {
      return !!this.user;
    },
    // Has finalized his account creation
    isRegistered() {
      const requiredFields = ["name"];

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
    currentCommunity() {
      return this.$store.state.communities.current
        ? this.$store.state.communities.current
        : this.user.main_community.id;
    },
  },
  methods: {
    isAdminOfCommunity(community) {
      return (
        this.isGlobalAdmin ||
        (community &&
          !!this.user.communities.find((c) => c.id === community.id && c.role === "admin"))
      );
    },
    // has admin priveleges over other user.
    isAdminOfUser(otherUser) {
      return (
        this.isGlobalAdmin || (otherUser && otherUser?.communities?.find(this.isAdminOfCommunity))
      );
    },
    isAdminOfLoanable(loanable) {
      return (
        this.isAdminOfCommunity(loanable?.community) || this.isAdminOfUser(loanable?.owner?.user)
      );
    },
    userHasProofForCommunity(communityUser) {
      return communityUser?.proof?.length > 0;
    },
  },
};
