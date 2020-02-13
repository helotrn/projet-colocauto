<template>
  <b-form-group class="forms-file-uploader" :label="label" :label-for="field">
    <div class="mb-3" v-if="!value">
      <b-form-file v-bind:value="value" :state="Boolean(value)" :id="field"
        :ref="`${field}fileinput`" :placeholder="placeholder"
        :name="field" :accept="accept.join(',')"
        @change="uploadFile($event.target.name, $event.target.files)"/>
    </div>
    <div v-else>
      <figure class="preview">
        <img v-if="!value" src="/loading.gif" >

        <figcaption>{{ value.original_filename }}</figcaption>
      </figure>
      <b-button variant="warning" @click="removeFile">
        <small>{{ removeFileText }}</small>
      </b-button>
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: 'FormsFileUploader',
  data() {
    return {
      errors: [],
    };
  },
  props: {
    accept: {
      default: () => ['application/pdf'],
      type: Array,
    },
    field: {
      required: true,
      type: String,
    },
    label: {
      required: false,
      type: String,
      default: '',
    },
    placeholder: {
      default: 'Envoyer un fichier...',
      type: String,
    },
    removeFileText: {
      default: 'Retirer le fichier',
      type: String,
    },
    value: {
      type: Object,
      require: false,
      default: null,
    },
  },
  methods: {
    removeFile() {
      this.$emit('input', null);

      if (this.$refs[`${this.field}fileinput`]) {
        this.$refs[`${this.field}fileinput`].reset();
      }
    },
    async uploadFile(fieldName, fileList) {
      const formData = new FormData();

      if (!fileList.length) {
        return null;
      }

      Array
        .from(Array(fileList.length).keys())
        .map(x => formData.append(fieldName, fileList[x], fileList[x].name));

      formData.append('field', fieldName);

      const file = await this.$store.dispatch('files/upload', formData);

      this.$emit('input', file);

      return file;
    },
  },
};
</script>

<style>
.preview img {
  max-width: 100%;
}

.custom-file-label {
  overflow: hidden;
}
</style>
