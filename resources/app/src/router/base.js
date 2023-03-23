import Dashboard from "@/views/Dashboard.vue";
import Help from "@/views/Help.vue";
import Home from "@/views/Home.vue";

export default [
  {
    path: "/",
    redirect: "/login",
  },
  {
    path: "/app",
    name: "dashboard",
    component: Dashboard,
    meta: {
      auth: true,
      title: "titles.dashboard",
    },
  },
  {
    path: "/help",
    name: "help",
    component: Help,
    meta: {
      title: "Aide de LocoMotion",
    },
  },
];
