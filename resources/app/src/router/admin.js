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
              fields: 'id,name,type',
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
        form: {
          general: {
            id: {
              type: 'number',
              disabled: true,
              required: true,
            },
            name: {
              type: 'text',
              required: true,
            },
            position: {
              type: 'point',
              required: true,
            },
            location_description: {
              type: 'textarea',
              required: true,
            },
            comments: {
              type: 'textarea',
              required: true,
            },
            instructions: {
              type: 'textarea',
              required: true,
            },
            type: {
              type: 'select',
              options: [
                {
                  text: 'Voiture',
                  value: 'car',
                },
                {
                  text: 'Vélo',
                  value: 'bike',
                },
                {
                  text: 'Remorque',
                  value: 'trailer',
                },
              ],
            },
            // $table->text('availability_ics');
            // $table->unsignedBigInteger('owner_id')->nullable();
            // $table->unsignedBigInteger('community_id')->nullable();
          },
          bike: {
            model: {
              type: 'text',
              required: true,
            },
            bike_type: {
              type: 'select',
              options: [
                {
                  text: 'Régulier',
                  value: 'regular',
                },
                {
                  text: 'Électrique',
                  value: 'electric',
                },
                {
                  text: 'Roue fixe',
                  value: 'fixed_wheel',
                },
              ],
            },
            size: {
              type: 'select',
              options: [
                {
                  text: 'Grand',
                  value: 'big',
                },
                {
                  text: 'Moyen',
                  value: 'medium',
                },
                {
                  text: 'Petit',
                  value: 'small',
                },
                {
                  text: 'Enfant',
                  value: 'kid',
                },
              ],
            },
          },
        },
        title: 'titles.loanable',
      },
    },
  ],
};
