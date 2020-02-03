import Admin from '../views/Admin.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminCommunities from '../views/admin/Communities.vue';
import AdminCommunity from '../views/admin/Community.vue';

export default {
  path: '/admin',
  component: Admin,
  meta: {
    auth: true,
  },
  children: [
    {
      path: '',
      component: AdminDashboard,
      meta: {
        auth: true,
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
              fields: 'id,name,type',
            },
          },
        },
        title: 'titles.communauté',
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
          fields: '*,users.*',
        },
        form: {
          id: {
            type: 'number',
            disabled: true,
            required: true,
            label: 'ID',
          },
          name: {
            type: 'text',
            required: true,
            label: 'Nom',
          },
          description: {
            type: 'textarea',
            required: true,
            label: 'Description',
          },
          type: {
            type: 'select',
            label: 'Type',
            options: [
              {
                text: 'Privée',
                value: 'private',
              },
              {
                text: 'Voisinage',
                value: 'neighborhood',
              },
              {
                text: 'Quartier',
                value: 'borough',
              },
            ],
          },
        },
      },
    },
  ],
};

