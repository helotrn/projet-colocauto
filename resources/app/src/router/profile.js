import Profile from '../views/Profile.vue';
import ProfileAccount from '../views/profile/Account.vue';
import ProfileBorrower from '../views/profile/Borrower.vue';
import ProfileCommunities from '../views/profile/Communities.vue';
import ProfileLoanables from '../views/profile/Loanables.vue';
import ProfileLoanable from '../views/profile/Loanable.vue';

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
      name: 'account',
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
      name: 'borrower',
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
      name: 'communities',
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
      path: 'loanables',
      name: 'loanables',
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
  ],
};
