<template>
  <div class="dashboard-resources-list">
    <h3>Ressources</h3>

    <ul class="dashboard-resources-list__list">
      <li class="dashboard-resources-list__item">
        <router-link to="/assurances-desjardins" class="dashboard-resources-list__item-title">
          <img class="dashboard-resources-list__item-title-icon" src="/icons/assurances.png" />
          <span class="dashboard-resources-list__item-title-text"> Mon assurance </span>
        </router-link>
      </li>

      <li class="dashboard-resources-list__item" v-if="hasCommunity">
        <a
          href="https://bit.ly/locomotion-bienvenue"
          target="_blank"
          class="dashboard-resources-list__item-title"
        >
          <img class="dashboard-resources-list__item-title-icon" src="/icons/allo.png" />
          <span class="dashboard-resources-list__item-title-text"> Guide de départ </span>
        </a>
      </li>

      <li class="dashboard-resources-list__item" v-if="communitiesWithChatGroup.length > 0">
        <div class="dashboard-resources-list__item-title">
          <img class="dashboard-resources-list__item-title-icon" src="/icons/messenger.png" />
          <span class="dashboard-resources-list__item-title-text">
            Discuter avec mon quartier
          </span>
        </div>

        <div class="dashboard-resources-list__item-content">
          <ul v-if="communitiesWithChatGroup.length > 0">
            <li v-for="c in communitiesWithChatGroup" :key="c.id">
              <a :href="c.chat_group_url" target="_blank">{{ c.name }}</a>
            </li>
          </ul>
        </div>
      </li>

      <li class="dashboard-resources-list__item">
        <router-link to="/faq" class="dashboard-resources-list__item-title">
          <img class="dashboard-resources-list__item-title-icon" src="/icons/faq.png" />
          <span class="dashboard-resources-list__item-title-text"> FAQ / Contact</span>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "DashboardResourcesList",
  props: {
    user: {
      required: true,
      type: Object,
    },
  },
  computed: {
    communitiesWithChatGroup() {
      if (!this.user || !this.user.communities) {
        return [];
      }

      return this.user.communities.filter((c) => !!c.chat_group_url);
    },
    hasCommunity() {
      if (!this.user || !this.user.communities) {
        return false;
      }

      return this.user.communities.length > 0;
    },
  },
};
</script>

<style lang="scss">
.dashboard-resources-list {
  a {
    &,
    &:hover,
    &:active,
    &:focus {
      color: $locomotion-green;
    }
  }

  &__list {
    list-style-type: none;
    padding: 0;
    margin: 0;

    margin-bottom: 30px;

    &:last-child {
      margin-bottom: 0;
    }

    li > a,
    li > div {
      display: flex;
      flex-direction: row;
    }
  }

  &__item {
    margin-top: 1rem;
  }

  &__item-title {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
  }

  &__item-title-icon {
    height: 3.625rem; /* Was 58px initially. */
    width: 3.625rem;
  }

  &__item-title-text {
    margin-left: 1rem;
  }

  &__item-content {
    display: block;
    margin-left: 4.875rem; /* Was 58px initially. */

    ul {
      padding-left: 1rem;
    }
  }

  &__footer {
    span {
      display: block;
      text-align: center;
    }
  }
}
</style>
