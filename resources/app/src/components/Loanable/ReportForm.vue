<template>
  <b-form class="form" @submit.prevent="submit">

    <forms-validated-input
      type="checkboxes"
      :name="`damage${_uid}`"
      label="Y a t il les dommages suivants : "
      :value="selectedDamage"
      @input="checkDamages"
      :options="damageList"
      :switches="false"
      :stacked="false"
    />
    <forms-validated-input
      type="textarea"
      :name="`details${_uid}`"
      label="Détails des dommages : "
      placeholder="Décrivez les dommages présents"
      :value="report.details"
      @input="setDetails"
    />
    <label for="report_picture">
      {{ $i18n.t("location.pictures."+report.location) }}
    </label>
    <multiple-images-uploader
      :field="`report_picture${_uid}`"
      label=""
      preview-aspect-ratio="1"
      :value="report.pictures"
      @input="setPicture"
      @delete="removePicture"
    />

    <div class="text-end">
      <b-button
        variant="primary"
        size="sm"
        @click="save"
        :disabled="!changed"
      >
        Enregistrer
      </b-button>
    </div>

  </b-form>
</template>
<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import MultipleImagesUploader from "@/components/Forms/MultipleImagesUploader.vue";

import locales from "@/locales";

export default {
  name: "ReportForm",
  props: {
    report: { type: Object, required: true },
  },
  data() {
    return {
      selectedDamage: [], // Must be an array reference!
      damageList: [
        { text: 'rayure', value: 'scratch' },
        { text: 'chocs', value: 'bumps' },
        { text: 'tache', value: 'stain' },
      ],
      nextPicture: null,
      changed: false,
    }
  },
  methods: {
    async save() {
      await this.$store.dispatch("loanables/saveReport", this.report);
      this.$store.commit("addNotification", {
        title: "État du véhicule mis à jour",
        content: this.$t(`reports.location.label.${this.report.location}`),
        variant: "success",
        type: "loanable",
      });
      this.changed = false;
    },
    update(report) {
      let reports = [...this.$store.state.loanables.item.reports]
      if( this.report.id ){
        // update existing report
        reports = reports.map(r => r.id == this.report.id ? ({...r, ...report}) : r)
      } else {
        // create a new report
        reports.push({
          ...this.report,
          ...report,
        })
      }
      this.$store.commit("loanables/patchItem", {reports});
      this.changed = true;
    },
    checkDamages(list) {
      this.update({
        scratch: list.includes('scratch'),
        bumps: list.includes('bumps'),
        stain: list.includes('stain'),
      })
    },
    setDetails(details) {
      this.update({details})
    },
    setPicture(picture) {
      this.update({pictures: [...this.report.pictures, picture]})
    },
    removePicture(index) {
      this.update({pictures: this.report.pictures.toSpliced(index, 1)})
    }
  },
  mounted() {
    this.$store.state.loanables.form.types.car.damages.forEach(type => {
      if( this.report[type] ) this.selectedDamage.push(type)
    })
  },
  components: {FormsValidatedInput, MultipleImagesUploader},
  i18n: {
    messages: {
      fr: {
        ...locales.fr.reports,
      },
    },
  },
}</script>
