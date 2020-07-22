import Register from '../views/Register.vue';
import RegisterIntro from '../views/register/Intro.vue';
import RegisterStep from '../views/register/Step.vue';
import RegisterMap from '../views/register/Map.vue';

export default {
  path: '/register',
  name: 'register',
  component: Register,
  meta: {
    title: 'titles.register',
  },
  children: [
    {
      path: '1',
      name: 'register-intro',
      component: RegisterIntro,
      meta: {
        title: 'titles.register',
      },
    },
    {
      path: 'map',
      name: 'register-map',
      component: RegisterMap,
      meta: {
        auth: true,
        slug: 'users',
        skipCleanup: true,
        data: {
          communities: {
            retrieve: {
              fields: 'id,name,type,description,center,area_google,center_google,'
                + 'parent.id,parent.name',
              type: 'neighborhood,borough',
            },
          },
        },
        title: 'titles.register-map',
      },
    },
    {
      path: ':step',
      name: 'register-step',
      component: RegisterStep,
      props: true,
      meta: {
        auth: true,
        slug: 'users',
        params: {
          fields: '*,avatar.*,owner.*,borrower.*.*,communities.id,communities.name,communities.role,communities.proof',
        },
      },
    },
  ],
};
