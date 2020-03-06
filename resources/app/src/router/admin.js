import Admin from '../views/Admin.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminCommunities from '../views/admin/Communities.vue';
import AdminCommunity from '../views/admin/Community.vue';
import AdminLoanable from '../views/admin/Loanable.vue';
import AdminLoanables from '../views/admin/Loanables.vue';

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
        title: 'tableau de bord',
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
          fields: '*,owner.id,owner.full_name',
        },
        title: 'titles.loanable',
      },
    },
  ],
};
