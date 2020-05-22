<template>
  <div class="dashboard-resources-list">
    <h3>Ressources</h3>

    <ul class="dashboard-resources-list__resources">
      <li class="dashboard-resources-list__resources__faq">
        <router-link to="/faq">
          <img src="/icons/faq.png">
          <span>
            Foire aux questions (FAQ)
          </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__committee" v-if="hasCommunity()">
        <router-link to="/community?tag=committee">
          <img src="/icons/allo.png">
          <span>
            Motiver son quartier
          </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__desjardins">
        <router-link to="/assurances-desjardins">
          <img src="/icons/desjardins-assurances.png">
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__messenger">
        <div>
          <img src="/icons/messenger.png">
          <span>
            Discuter avec son voisinage
          </span>
        </div>

        <div class="dashboard-resources-list__resources__messenger__list">
          <span />
          <ul>
            <li v-if="hasCommunity(1)">
              <a href="https://www.facebook.com/groups/2992806784171151" target="_blank">
                Bellechasse
              </a>
            </li>
            <li v-if="hasCommunity(5)">
              <a href="https://www.facebook.com/groups/278871156602975" target="_blank">
                Fleury-Est
              </a>
            </li>
            <li v-if="hasCommunity(7)">
              <a href="https://www.facebook.com/groups/292112945285190" target="_blank">
                Fleury-Ouest
              </a>
            </li>
            <li v-if="hasCommunity(3)">
              <a href="https://www.facebook.com/groups/2304440433199032" target="_blank">
                Papineau
              </a>
            </li>
            <li v-if="hasCommunity(6)">
              <a href="https://www.facebook.com/groups/2740404116192047" target="_blank">
                Youville
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'DashboardResourcesList',
  props: {
    user: {
      required: true,
      type: Object,
    },
  },
  methods: {
    hasCommunity(id) {
      if (!id) {
        return this.user.communities.length > 0;
      }

      return this.user.communities.find(c => c.id === id);
    },
  },
};
</script>

<style lang="scss">
.dashboard-resources-list {
  h3 {
    font-size: 20px;
    font-weight: 600;
  }

  &__resources {
    list-style-type: none;
    padding: 0;
    margin: 0;

    li > a, li > div {
      color: $black;

      &, &:hover, &:active, &:focus {
        text-decoration: none;
      }

      display: flex;
      flex-direction: row;
      margin-top: 20px;
    }

    li {
      img {
        margin-right: 20px;
        flex: 0 0 58px;
      }

      &.dashboard-resources-list__resources__desjardins img {
        margin-right: 0;
        flex: 1 0 100%;
        max-width: 300px;
      }

      &.dashboard-resources-list__resources__messenger {
        .dashboard-resources-list__resources__messenger__list {
          display: flex;

          span {
            margin-right: 20px;
            flex: 0 0 58px;
          }

          ul {
            padding: 0 0 0 20px;
            max-width: 300px;
          }

          margin-top: 0;

          a {
            margin-top: 0;
          }
        }
      }

      span {
        display: flex;
        flex-direction: column;
        justify-content: center;

        font-size: 15px;
      }
    }
  }
}
</style>
