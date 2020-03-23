import Check from '@/assets/svg/check.svg';
import Danger from '@/assets/svg/danger.svg';
import Waiting from '@/assets/svg/waiting.svg';

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
    borrowerAvatar() {
      const { avatar } = this.loan.borrower.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    ownerAvatar() {
      const { avatar } = this.loan.loanable.owner.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    userRole() {
      return this.user.id === this.loan.loanable.owner.user.id ? 'owner' : 'borrower';
    },
  },
  methods: {
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
