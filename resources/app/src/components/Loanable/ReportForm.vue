<template>
  <b-form class="form" @submit.prevent>

    <forms-validated-input
      type="checkboxes"
      :name="`damage${_uid}`"
      label="Y a t il les dommages suivants : "
      v-model="selectedDamage"
      :options="damageList"
      :switches="false"
      :stacked="false"
    />
    <forms-validated-input
      type="textarea"
      :name="`details${_uid}`"
      label="Détails des dommages : "
      placeholder="Décrivez les dommages présents"
      v-model="modifiedDetails"
    />
    <label for="report_picture">
      {{ $i18n.t("location.pictures."+report.location) }}
    </label>
    <multiple-images-uploader
      :field="`report_picture${_uid}`"
      label=""
      preview-aspect-ratio="1"
      :value="modifiedPictures"
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
      modifiedDetails: '',
      modifiedPictures: [],
      damageList: [
        { text: 'rayure', value: 'scratch' },
        { text: 'chocs', value: 'bumps' },
        { text: 'tache', value: 'stain' },
      ],
    }
  },
  computed: {
    modifiedReport(){
      return {
        details: this.modifiedDetails,
        pictures: [...this.modifiedPictures],
        scratch: this.selectedDamage.includes('scratch'),
        bumps: this.selectedDamage.includes('bumps'),
        stain: this.selectedDamage.includes('stain'),
      }
    },
    changed() {
      return this.report.details != this.modifiedReport.details
        || this.report.pictures.length != this.modifiedPictures.length
        || this.report.pictures.map(p => p.id).join('-') != this.modifiedPictures.map(p => p.id).join('-')
        || this.report.scratch != this.modifiedReport.scratch
        || this.report.bumps != this.modifiedReport.bumps
        || this.report.stain != this.modifiedReport.stain
    }
  },
  methods: {
    async save() {
      await this.$store.dispatch("loanables/saveReport", { ...this.report, ...this.modifiedReport});
      this.$store.commit("addNotification", {
        title: "État du véhicule mis à jour",
        content: this.$t(`reports.location.label.${this.report.location}`),
        variant: "success",
        type: "loanable",
      });
    },
    setPicture(picture) {
      this.modifiedPictures.push(picture)
    },
    removePicture(index) {
      this.modifiedPictures.splice(index, 1)
    }
  },
  mounted() {
    this.$store.state.loanables.form.types.car.damages.forEach(type => {
      if( this.report[type] ) this.selectedDamage.push(type)
    })
    this.modifiedDetails = this.report.details
    this.modifiedPictures = [...this.report.pictures]
    
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
