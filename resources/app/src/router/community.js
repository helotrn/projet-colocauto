import Community from '@/views/Community.vue';
import CommunityDashboard from '@/views/community/Dashboard.vue';
import CommunityView from '@/views/community/CommunityView.vue';

export default {
  path: '/community',
  component: Community,
  meta: {
    auth: true,
    titles: 'titles.community',
  },
  children: [
    {
      path: '',
      component: CommunityDashboard,
      meta: {
        auth: true,
        title: 'titles.community',
        data: {
          communities: {
            retrieveOne: {
              params: {
                fields: 'id,name,users.id,users.full_name,users.avatar,users.description,'
                + 'users.owner.id,users.tags.*',
              },
              id({ user }) {
                return user.communities[0].id;
              },
            },
          },
        },
      },
    },
    {
      path: ':view',
      name: 'community-view',
      component: CommunityView,
      props: true,
      meta: {
        auth: true,
        title: 'titles.find_vehicle',
        slug: 'loanables',
        data: {
          loanables: {
            retrieve: {
              fields: 'id,type,name,position_google,available,owner.user.id,owner.user.name,owner.user.full_name,owner.user.avatar,image.*',
              '!owner.user.id': 'me',
            },
          },
          loans: {
            loadEmpty: {},
          },
        },
      },
    },
  ],
};
