import Profile from '../views/Profile.vue';
import ProfileAccount from '../views/profile/Account.vue';
import ProfileBorrower from '../views/profile/Borrower.vue';
import ProfileCommunities from '../views/profile/Communities.vue';
import ProfileInvoices from '../views/profile/Invoices.vue';
import ProfileLoanables from '../views/profile/Loanables.vue';
import ProfileLoanable from '../views/profile/Loanable.vue';
import ProfilePaymentMethods from '../views/profile/PaymentMethods.vue';
import ProfilePaymentMethod from '../views/profile/PaymentMethod.vue';

export default {
  path: '/profile',
  name: 'profile',
  component: Profile,
  meta: {
    auth: true,
    title: 'titles.profile',
  },
  children: [
    {
      path: 'account',
      component: ProfileAccount,
      meta: {
        auth: true,
        title: 'titles.account',
        slug: 'users',
        params: {
          fields: '*,avatar.*',
        },
      },
    },
    {
      path: 'borrower',
      component: ProfileBorrower,
      meta: {
        auth: true,
        title: 'titles.borrower',
        slug: 'users',
        params: {
          fields: '*,borrower.*',
        },
      },
    },
    {
      path: 'communities',
      component: ProfileCommunities,
      meta: {
        auth: true,
        title: 'titles.communities',
        slug: 'users',
        params: {
          fields: 'id,communities.id,communities.name,communities.requirements,communities.proof',
        },
      },
    },
    {
      path: 'invoices',
      component: ProfileInvoices,
      meta: {
        auth: true,
        title: 'titles.invoices',
        slug: 'invoices',
        data: {
          invoices: {
            retrieve: {
              fields: '*',
            },
          },
        },
      },
    },
    {
      path: 'loanables',
      component: ProfileLoanables,
      meta: {
        auth: true,
        creatable: true,
        title: 'titles.loanables',
        slug: 'loanables',
        data: {
          loanables: {
            retrieve: {
              fields: 'id,name,type,image.*',
              'owner.user.id': 'me',
            },
          },
        },
      },
    },
    {
      path: 'loanables/:id',
      component: ProfileLoanable,
      props: true,
      meta: {
        auth: true,
        slug: 'loanables',
        params: {
          fields: '*,events,type,community.id,community.center,owner.id,owner.user.id,'
            + 'owner.user.communities.center',
        },
        title: 'titles.loanable',
        data: {
          communities: {
            retrieve: {
              fields: 'id,name,center',
            },
          },
        },
      },
    },
    {
      path: 'payment_methods',
      component: ProfilePaymentMethods,
      meta: {
        auth: true,
        creatable: true,
        title: 'titles.payment_methods',
        slug: 'paymentMethods',
        data: {
          paymentMethods: {
            retrieve: {
              fields: '*',
            },
          },
        },
      },
    },
    {
      path: 'payment_methods/:id',
      component: ProfilePaymentMethod,
      props: true,
      meta: {
        auth: true,
        slug: 'paymentMethods',
        params: {
          fields: '*',
        },
        title: 'titles.payment_method',
      },
    },
  ],
};
