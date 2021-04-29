import Check from '@/assets/svg/check.svg';
import Danger from '@/assets/svg/danger.svg';
import Waiting from '@/assets/svg/waiting.svg';

import { extractErrors } from '@/helpers';

export default {
  components: {
    'svg-check': Check,
    'svg-danger': Danger,
    'svg-waiting': Waiting,
  },
  props: {
    action: {
      type: Object,
      required: true,
    },
    item: {
      type: Object,
      required: true,
    },
    open: {
      type: Boolean,
      required: false,
      default: false,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    borrower() {
      return this.item.borrower;
    },
    borrowerAvatar() {
      const { avatar } = this.borrower.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    isContestable() {
      return !!this.action.executed_at
        && this.action.status !== 'canceled'
        && !!this.owner;
    },
    isContested() {
      return !!this.action.executed_at
        && this.action.status === 'canceled'
        && !!this.owner;
    },
    owner() {
      return this.item.loanable.owner;
    },
    ownerAvatar() {
      if (!this.owner) {
        return '';
      }

      const { avatar } = this.owner.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    /*
      Deprecated. User can have multiple roles esp. owner and borrower at the
      same time. Use userRoles instead.
    */
    userRole() {
      if (this.user.role === 'admin') {
        return 'admin';
      }

      return (this.owner && this.user.id === this.owner.user.id) ? 'owner' : 'borrower';
    },
    /*
      Returns an array containing all roles.
    */
    userRoles() {
      const roles = [];

      if (this.user.role === 'admin') {
        roles.push('admin');
      }

      // Owner may be null.
      if (this.user.id === this.owner?.user?.id) {
        roles.push('owner');
      }

      if (this.user.id === this.borrower.user.id) {
        roles.push('borrower');
      }

      return roles;
    },
    userIsAdmin() {
      if ((this.item.loanable.owner && this.user.id === this.item.loanable.owner.user.id)
        || this.user.id === this.item.borrower.user.id) {
        return false; // Can't be admin on your own loans
      }

      if (this.user.role === 'admin') {
        return true;
      }

      const community = this.user.communities.find(c => c.id === this.item.community_id);
      if (community) {
        return community.role === 'admin';
      }

      return false;
    },
  },
  methods: {
    abortAction() {
      if (!this.action.id) {
        this.$emit('aborted', this.action);
      }
    },
    async createAction() {
      try {
        await this.$store.dispatch('loans/createAction', this.action);
        this.$emit('created');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit('addNotification', {
                content: extractErrors(e.response.data).join(', '),
                title: 'Erreur de validation',
                variant: 'danger',
                type: 'extension',
              });
              break;
            default:
              throw e;
          }
        } else {
          throw e;
        }
      }
    },
    async completeAction() {
      await this.$store.dispatch('loans/completeAction', this.action);
      this.$emit('completed');
    },
    async cancelAction() {
      await this.$store.dispatch('loans/cancelAction', this.action);
      this.$emit('canceled');
    },
  },
};
