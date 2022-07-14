import Community from "@/views/Community.vue";
import CommunityDashboard from "@/views/community/Dashboard.vue";
import CommunityView from "@/views/community/CommunityView.vue";

export default {
  path: "/community",
  component: Community,
  meta: {
    auth: true,
    titles: "titles.community",
  },
  children: [
    {
      path: "",
      component: CommunityDashboard,
      meta: {
        auth: true,
        title: "titles.community",
        data: {
          global: {
            load: {},
          },
          communities: {
            retrieveOne: {
              params: {
                fields:
                  "id,name,long_description,chat_group_url,type,parent.id,parent.name," +
                  "area,area_google,center,center_google,approved_users_count," +
                  "parent.area,parent.center,parent.area_google,parent.center_google," +
                  "parent.children.id,parent.children.name,parent.children.area," +
                  "parent.children.center,parent.children.area_google," +
                  "parent.children.approved_users_count," +
                  "parent.children.center_google,parent.approved_users_count," +
                  "children.id,children.name,children.area,children.center," +
                  "children.area_google,children.center_google,children.approved_users_count," +
                  "users.id,users.full_name,users.avatar,users.description," +
                  "users.owner.id,users.tags.*",
              },
              id({ user }) {
                if (!user || !user.communities || !user.communities[0]) {
                  return 0;
                }

                return user.communities[0].id;
              },
            },
          },
        },
      },
    },
    {
      path: ":view",
      name: "community-view",
      component: CommunityView,
      props: true,
      meta: {
        auth: true,
        title: "titles.find_vehicle",
        slug: "loanables",
        data: {
          loanables: {
            retrieve: {
              fields:
                "id,type,name,position_google,available,owner.user.id,owner.user.name,owner.user.last_name,owner.user.full_name,owner.user.avatar,image.*",
              per_page: 100,
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
