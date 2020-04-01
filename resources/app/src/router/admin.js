import Admin from '../views/Admin.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminCommunities from '../views/admin/Communities.vue';
import AdminCommunity from '../views/admin/Community.vue';
import AdminInvoices from '../views/admin/Invoices.vue';
import AdminInvoice from '../views/admin/Invoice.vue';
import AdminLoanable from '../views/admin/Loanable.vue';
import AdminLoanables from '../views/admin/Loanables.vue';
import AdminUser from '../views/admin/User.vue';
import AdminUsers from '../views/admin/Users.vue';

export default {
  path: '/admin',
  component: Admin,
  meta: {
    auth: true,
    title: 'titles.admin',
  },
  children: [
    {
      path: '',
      component: AdminDashboard,
      meta: {
        auth: true,
        title: 'titles.dashboard',
      },
    },
    {
      path: 'communities',
      component: AdminCommunities,
      meta: {
        auth: true,
        creatable: true,
        slug: 'communities',
        data: {
          communities: {
            retrieve: {
              fields: 'id,name,type,users.id,users.loanables.id,loanables.id',
            },
          },
        },
        title: 'titles.communities',
      },
    },
    {
      path: 'communities/:id',
      component: AdminCommunity,
      props: true,
      meta: {
        auth: true,
        slug: 'communities',
        params: {
          fields: [
            '*',
            'users.id',
            'users.full_name',
            'users.role',
            'users.approved_at',
            'users.suspended_at',
            'users.proof.id',
            'pricings.*',
          ].join(','),
        },
      },
    },
    {
      path: 'loanables',
      component: AdminLoanables,
      meta: {
        auth: true,
        creatable: true,
        slug: 'loanables',
        data: {
          loanables: {
            retrieve: {
              fields: 'id,name,type,owner.id,owner.user.full_name,owner.user.id',
            },
          },
        },
        title: 'titles.loanables',
      },
    },
    {
      path: 'loanables/:id',
      component: AdminLoanable,
      props: true,
      meta: {
        auth: true,
        slug: 'loanables',
        params: {
          fields: '*,owner.id,owner.user.id,owner.user.full_name,community.id,community.name',
        },
        title: 'titles.loanable',
      },
    },
    {
      path: 'users',
      component: AdminUsers,
      meta: {
        auth: true,
        creatable: true,
        slug: 'users',
        data: {
          users: {
            retrieve: {
              fields: 'id,name,last_name,full_name,email',
            },
          },
        },
        title: 'titles.users',
      },
    },
    {
      path: 'users/:id',
      component: AdminUser,
      props: true,
      meta: {
        auth: true,
        slug: 'users',
        params: {
          fields: '*,owner.*,borrower.*,loanables.*,loanables.loans.*,'
            + 'loanables.loans.borrower.user.full_name,communities.*,loans.*,'
            + 'invoices.*,invoices.total,invoices.total_with_taxes,loans.borrower.user.*,'
            + 'loans.loanable.name',
        },
        title: 'titles.user',
      },
    },
    {
      path: 'invoices',
      component: AdminInvoices,
      meta: {
        auth: true,
        creatable: true,
        slug: 'invoices',
        data: {
          invoices: {
            retrieve: {
              fields: '*,user.id,user.full_name',
            },
          },
        },
        title: 'titles.invoices',
      },
    },
    {
      path: 'invoices/:id',
      component: AdminInvoice,
      props: true,
      meta: {
        auth: true,
        slug: 'invoices',
        params: {
          fields: '*,invoice_items.*,user.id,user.full_name',
        },
        title: 'titles.invoice',
      },
    },
  ],
};
