<template>
  <b-card class="bordered-card">
    <div class="mb-3 font-weight-bold">{{ coowner.user.full_name }}</div>
    <b-form>
      <forms-validated-input
        v-model="receive_notifications"
        label="Reçoit les notifications"
        name="title"
        type="checkbox"
        :disabled="disabled || loading"
        description="Si cette personne reçoit également les notifications par email lors d'un emprunt"
      ></forms-validated-input>
      <forms-validated-input
        v-model="title"
        label="Titre"
        name="title"
        description="Ce titre clarifie la responsabilité de cette personne pour le véhicule. Ex. dépositaire des clés, copropriétaire, etc."
        type="text"
        :disabled="disabled || loading"
      ></forms-validated-input>
    </b-form>
    <!-- form with two fields: show on contact and title -->

    <icon-button role="save" :loading="loading" :disabled="disabled" @click.prevent="save">
      Enregistrer
    </icon-button>
    &nbsp;
    <icon-button role="cancel" :disabled="disabled || loading" @click.prevent="cancel">
      Annuler
    </icon-button>
    <layout-loading v-if="loading" with-button />
  </b-card>
</template>
<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LayoutLoading from "@/components/Layout/Loading.vue";
import IconButton from "@/components/shared/IconButton.vue";
import Vue from "vue";

export default {
  name: "CoownerForm",
  components: { IconButton, LayoutLoading, FormsValidatedInput },
  props: {
    coowner: {
      type: Object,
      required: true,
    },
    loanable: {
      type: Object,
      required: true,
    },
    disabled: { type: Boolean, default: false },
  },
  data() {
    return {
      receive_notifications: null,
      title: null,
      loading: false,
    };
  },
  mounted() {
    this.receive_notifications = this.coowner.receive_notifications;
    this.title = this.coowner.title;
  },
  methods: {
    cancel() {
      this.$emit("done", this.coowner.id);
    },
    async save() {
      this.loading = true;

      await Vue.axios.put(
        `/loanables/${this.loanable.id}/coowners/${this.coowner.id}`,
        {
          id: this.coowner.id,
          receive_notifications: this.receive_notifications,
          title: this.title,
        }
      ).then(() => {
        this.coowner.receive_notifications = this.receive_notifications;
        this.coowner.title = this.title;
        this.loading = false
        this.$emit("done", this.coowner.id);

        this.$store.commit("addNotification", {
          content: this.coowner.user.full_name,
          title: "Changements sauvegardés !",
          variant: "success",
          type: "loanable",
        })
      }).catch(error => {
        let message = error.request?.response
        if(message) {
          message = JSON.parse(message);
          if( message.errors ){
            message = Object.values(message.errors).join("\n ");
          }
        } else {
          message = error.message;
        }
        this.loading = false

        this.$store.commit("addNotification", {
          content: message,
          title: "Erreur de sauvegarde",
          variant: "danger",
          type: "loanable",
        })
      });

    },
  },
};
</script>
<style scoped lang="scss">
.bordered-card {
  border: 1px solid $light-grey;
}
</style>
