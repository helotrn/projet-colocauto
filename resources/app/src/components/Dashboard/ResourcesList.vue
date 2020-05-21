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

      <li class="dashboard-resources-list__resources__committee" v-if="hasCommunity">
        <router-link to="/community?tag=committee">
          <img src="/icons/allo.png">
          <span>
            Motiver son quartier
          </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__desjardins">
        <router-link to="/guide/assurance-desjardins">
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
          <ul>
            <li><a href="https://m.me/join/AbYKAXEN1zNM-SYv" target="_blank">Bellechasse</a></li>
            <li><a href="https://m.me/join/Abblub9IWQl1Xz7Y" target="_blank">Masson</a></li>
            <li><a href="https://m.me/join/AbYnbYDmbGzYdwzc" target="_blank">Papineau</a></li>
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
    hasCommunity: {
      required: false,
      type: Boolean,
      default: true,
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

  ul {
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
        &__list {
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
