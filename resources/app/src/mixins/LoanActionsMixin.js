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
    loan: {
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
      return this.loan.borrower;
    },
    borrowerAvatar() {
      const { avatar } = this.borrower.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    owner() {
      return this.loan.loanable.owner;
    },
    ownerAvatar() {
      const { avatar } = this.owner.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    userRole() {
      return this.user.id === this.owner.user.id ? 'owner' : 'borrower';
    },
  },
  methods: {
    abortAction(action) {
      if (!action.id) {
        this.$emit('aborted');
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
              break;
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
