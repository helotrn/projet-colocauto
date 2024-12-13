<template>
  <div class="profile-loanables" v-if="routeDataLoaded && !loading && loaded">
    <div v-if="user.owner">

      <b-row v-if="loanables.length === 0">
        <b-col>Pas de véhicule.</b-col>
      </b-row>
      <b-row
        v-else
        v-for="loanable in loanables"
        :key="loanable.id"
        class="profile-loanables__loanables"
      >
        <b-col class="profile-loanables__loanables__loanable">
          <loanable-info-box :buttons="['remove']" :show_community="true" v-bind="loanable" @disabled="loadListData" />
        </b-col>
      </b-row>

      <b-row v-if="lastPage > 1">
        <b-col>
          <b-pagination
            align="right"
            v-model="contextParams.page"
            :total-rows="total"
            :per-page="contextParams.per_page"
          />
        </b-col>
      </b-row>
    </div>
    <div v-else>
      <b-row>
        <b-col>Pas de véhicule.</b-col>
      </b-row>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import LoanableInfoBox from "@/components/Loanable/InfoBox.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import { extractErrors } from "@/helpers";
import locales from "@/locales";

export default {
  name: "ProfileLoanables",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  components: { LoanableInfoBox },
  data() {
    return {
      selected: [],
      fields: [
        { key: "id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "type", label: "Type", sortable: false },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  computed: {
    loanables() {
      return this.data.filter(l => l.community.id == this.currentCommunity)
    }
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    async createOwnerProfile() {
      try {
        await this.$store.dispatch("users/update", {
          id: this.user.id,
          data: {
            id: this.user.id,
            owner: {},
          },
          params: {},
        });
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: "Erreur de validation",
                variant: "danger",
                type: "profile-loanables",
              });
              break;
            default:
              throw e;
          }
        } else {
          throw e;
        }
      }
      await this.$store.dispatch("loadUser");
    },
  },
};
</script>

<style lang="scss">
.profile-loanables__loanables__loanable {
  margin-bottom: 20px;
}
</style>
