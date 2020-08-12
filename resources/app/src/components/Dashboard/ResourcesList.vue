<template>
  <div class="dashboard-resources-list">
    <h3>Ressources</h3>

    <ul class="dashboard-resources-list__resources">
      <li class="dashboard-resources-list__resources__desjardins">
        <router-link to="/assurances-desjardins">
          <img src="/icons/assurances.png">
          <span>
            Mon assurance
          </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__motivate" v-if="hasCommunity()">
        <a href="https://bit.ly/voisinage-LocoMotion" target="_blank">
          <img src="/icons/allo.png">
          <span>
            Motiver son quartier
          </span>
        </a>
      </li>

      <li class="dashboard-resources-list__resources__kit" v-if="hasCommunity()">
        <a href="https://bit.ly/locomotion-bienvenue" target="_blank">
          <img src="/icons/allo.png">
          <span>
            Trousse de départ
          </span>
        </a>
      </li>
    </ul>

    <h3>Des questions?</h3>

    <ul class="dashboard-resources-list__resources">
      <li class="dashboard-resources-list__resources__messenger">
        <div>
          <img src="/icons/messenger.png">
          <span>
            Discuter avec mon voisinage
          </span>
        </div>

        <div class="dashboard-resources-list__resources__messenger__list">
          <span />
          <ul>
            <li v-if="hasCommunity(1)">
              <a href="http://bit.ly/locomotion_bellechasse" target="_blank">
                Bellechasse
              </a>
            </li>
            <li v-if="hasCommunity(5)">
              <a href="https://bit.ly/locomotion_fleury_est" target="_blank">
                Fleury-Est
              </a>
            </li>
            <li v-if="hasCommunity(7)">
              <a href="http://bit.ly/locomotion_fleury_ouest" target="_blank">
                Fleury-Ouest
              </a>
            </li>
            <li v-if="hasCommunity(3)">
              <a href="http://bit.ly/locomotion_papineau" target="_blank">
                Papineau
              </a>
            </li>
            <li v-if="hasCommunity(6)">
              <a href="https://bit.ly/locomotion_youville" target="_blank">
                Youville
              </a>
            </li>
            <li v-if="hasNoCommunityIn([1, 5, 7, 3, 6])">
              <a href="https://bit.ly/voisinage-LocoMotion" target="_blank">
                LocoMotion
              </a>
            </li>
          </ul>
        </div>
      </li>

      <li class="dashboard-resources-list__resources__faq">
        <router-link to="/faq">
          <img src="/icons/faq.png">
          <span>
            Foire aux questions (FAQ)
          </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__resources__telephone">
        <a href="tel:+14384763343">
          <span>
            Numéro d'aide<br>
            (pour cadenas uniquement)<br>
            <strong>(438) 476-3343</strong>
          </span>
        </a>
      </li>
    </ul>

    <p class="dashboard-resources-list__mailto text-center">
      <a href="mailto:info@locomotion.app">Écrivez-nous!</a>
    </p>
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
    hasNoCommunityIn(ids) {
      return ids.reduce((acc, id) => acc && !this.hasCommunity(id), true);
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

  &__mailto {
    a {
      color: $black;
      text-decoration: underline;
    }
  }

  &__resources {
    list-style-type: none;
    padding: 0;
    margin: 0;

    margin-bottom: 30px;

    &:last-child {
      margin-bottom: 0;
    }

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
