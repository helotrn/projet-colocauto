<template>
  <b-container fluid v-if="item && routeDataLoaded">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $tc("invitation", 1) | capitalize }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder :definition="maybeReadonlyFormRules" v-model="item" entity="invitations">
            <template v-slot:user_id="{item}">
              <b-form-group :label="$t('fields.user_id')">
                <router-link v-if="item.user_id" :to="`/admin/users/${item.user_id}`">{{ item.user.full_name }}</router-link>
                <p v-else>Aucun utilisateur lié</p>
              </b-form-group>
            </template>
          </forms-builder>

          <div class="form__buttons">
            <b-button-group v-if="!item.id">
              <b-button variant="success" type="submit" :disabled="!changed || loading">
                Sauvegarder
              </b-button>
              <b-button type="reset" :disabled="!changed" @click="reset"> Réinitialiser </b-button>
            </b-button-group>
            <b-button-group v-else>
              <b-button
                variant="success"
                :disabled="loading || !!item.consumed_at"
                @click="resend"
              >Renvoyer</b-button>
              <b-button
                variant="danger"
                :disabled="loading || !!item.consumed_at"
                @click="destroy"
                >Désactiver</b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import locales from "@/locales";

export default {
  name: "AdminInvitation",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsBuilder,
  },
  methods: {
    async resend() {
      await this.$store.dispatch("invitations/resend", this.item.id);
    },
    async destroy() {
      await this.$store.dispatch("invitations/destroy", this.item.id);
      this.loadItem();
    },
  },
  computed: {
    maybeReadonlyFormRules() {
      // make email and community readonly for already sent invitations
      if(this.item.id) {
        return {
          ...this.form,
          email: {...this.form.email, disabled: true},
          for_community_admin: {...this.form.for_community_admin, disabled: true},
          community_id: {...this.form.community_id, disabled: true},
        }
      } else {
        return this.form;
      }
    }
  },
  mounted: async function() {
    // set default pre-selected community
    if( this.$route.query.community_id ) {
      this.$store.commit('invitations/patchItem', {
        community_id: this.$route.query.community_id,
      })
    }
    if( this.$route.query.email ) {
      this.$store.commit('invitations/patchItem', {
        email: this.$route.query.email,
      })
    }

    if( this.$route.query.community_id || this.$route.query.email ) {
      // when relation input load data, community_id is reset : set it again
      const unwatch = this.$watch(() => this.$store.state.invitations.item.community_id, community_id => {
        if( community_id == undefined ) {
          this.$store.commit('invitations/patchItem', {
            community_id: this.$route.query.community_id,
            email: this.$route.query.email,
          })
        }
        // remove parameter from url
        this.$router.replace(this.$route.path)
        unwatch()
      })
    }
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invitations,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.invitations,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
