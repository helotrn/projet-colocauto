<template>
  <b-form v-if="community" class="form text-end" @submit.prevent="submit">
    <forms-builder :definition="formName" v-model="community" entity="communities" class="text-start" />

    <fieldset v-if="community.id && invitation" class="collapsible-fieldset text-start">
      <h2 v-b-toggle:collapse-invitations class="toggle">
        Inviter des membres à la communauté <icons-caret class="b-icon" />
      </h2>
      <b-collapse id="collapse-invitations" visible>
        <div class="d-flex align-items-center" :style="invitationLoading ? '' : 'gap:2em'">
          <forms-builder :definition="invitationForm" v-model="invitation" entity="invitations" class="text-start" :key="refreshKey"/>
          <layout-loading v-if="invitationLoading" style="height:2em" />
          <b-button variant="outline-primary" :disabled="!invitation.email || invitationLoading" @click="createInvitation">
            Ajouter cet email
          </b-button>
        </div>
        <ul v-if="community.invitations && community.invitations.length" class="badge-list">
          <li v-for="inv in community.invitations" :key="inv.id"><b-badge>{{inv.email}}</b-badge></li>
        </ul>
      </b-collapse>
    </fieldset>
    <layout-loading v-else-if="community.id && invitationLoading" />

    <slot />
    <b-button
      variant="primary"
      type="submit"
      :disabled="!canSubmit"
    >
      {{ $route.name.match('register-') ? 'Suivant' : 'Enregistrer' }}
    </b-button>
  </b-form>
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";
import IconsCaret from "@/assets/icons/caret.svg";
import { extractErrors } from "@/helpers";

export default {
  name: "CommunityForm",
  components: { FormsBuilder, IconsCaret },
  data() {
    return ({
      refreshKey: Math.random()
    })
  },
  props: {
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    form: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    community: {
      type: Object,
      required: true,
    },
  },
  computed: {
    formName() {
      return { name: this.form.name }
    },
    invitationForm() {
      if( this.$store.state.invitations.form ) {
        return {
          email : this.$store.state.invitations.form.email,
        }
      }
    },
    invitation() {
      return this.$store.state.invitations.item;
    },
    invitations() {
      return this.$store.state.invitations.data;
    },
    invitationLoading() {
      return this.$store.state.invitations.loading;
    },
    canSubmit() {
      if( this.$route.name.match('register-') ) {
        return this.community.id || (this.changed && !this.loading)
      } else {
        return this.changed && !this.loading
      }
    },
  },
  methods: {
    submit(...params) {
      if( this.$route.name.match('register-') && this.community.id ) {
        this.$emit("next", ...params);
      } else {
        this.$emit("submit", ...params);
      }
    },
    async createInvitation(){
      try {
        await this.$store.dispatch('invitations/createItem');
        this.$store.commit("addNotification", {
          content: `Une invitations pour rejoindre ${this.community.name}
          a été envoyée à ${this.invitation.email}.`,
          title: "Invitation envoyée !",
          variant: "success",
          type: "community",
        })
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: "Erreur de sauvegarde",
                variant: "danger",
                type: "form",
              });
              break;
            default:
              throw e;
          }
        }
        throw e;
      }
      await this.$store.dispatch('communities/retrieveOne', {
        id: this.community.id,
        params: this.$route.meta.params,
      });
      await this.$store.dispatch('invitations/loadEmpty');
      this.$store.state.invitations.item.community_id = this.community.id;
      // force component re-render to avoid the "empty field" error message
      this.refreshKey = Math.random();
    }
  },
}
</script>
<style lang="scss">
.collapsible-fieldset {
  border: solid 1px $gray-400;
  background-color: $white;
  padding: 20px;
  margin-bottom: 20px;
  .toggle {
    position: relative;
    text-align: left;
    margin-top: 0;
    font-size: 22px;
    line-height: 24px;
    &:hover {
      text-decoration: underline;
    }
    .b-icon {
      transition: 0.3s;
      fill: $locomotion-grey;
      position: absolute;
      right: 0;
      transform: rotate(90deg);
    }
    &.not-collapsed .b-icon {
      transform: rotate(270deg);
    }
  }
}
#collapse-invitations {
  .forms-builder {
    flex-grow: 1;
  }
}
</style>
