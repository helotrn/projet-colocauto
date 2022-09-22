<template>
  <b-form-group class="forms-file-uploader" :label="label" :label-for="field">
    <div v-if="loading">
      <img src="/loading.svg" />
    </div>
    <div v-else-if="!value">
      <b-form-file
        :value="value"
        :state="validationState"
        :id="field"
        :ref="`${field}fileinput`"
        :placeholder="placeholder"
        :disabled="disabled"
        :name="field"
        :accept="accept.join(',')"
        browse-text="Sélectionner"
        drop-placeholder="Déposer le fichier ici..."
        @change="handleChange"
      />
      <div class="invalid-feedback" v-if="errors">
        {{ errors.message }}
      </div>
    </div>
    <div v-else>
      <figure class="preview">
        <img v-if="!value" src="/loading.svg" />

        <figcaption>
          <a :href="value.url" target="_blank">
            {{ value.original_filename }}
          </a>
        </figcaption>
      </figure>
      <b-button variant="warning" @click="removeFile" v-if="!disabled">
        <small>{{ removeFileText }}</small>
      </b-button>
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: "FormsFileUploader",
  props: {
    accept: {
      default: () => [
        "*.png",
        "*.jpg",
        "*.jpeg",
        "image/png",
        "image/jpg",
        "image/jpeg",
        "*.pdf",
        "application/pdf",
      ],
      type: Array,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    field: {
      required: true,
      type: String,
    },
    label: {
      required: false,
      type: String,
      default: "",
    },
    placeholder: {
      default: "Téléverser...",
      type: String,
    },
    removeFileText: {
      default: "Retirer le fichier",
      type: String,
    },
    required: {
      type: Boolean,
      required: false,
      default: false,
    },
    value: {
      type: Object,
      require: false,
      default: null,
    },
  },
  computed: {
    errors() {
      return this.$store.state.files.errors;
    },
    loading() {
      return !!this.$store.state.files.cancelToken;
    },
    validationState() {
      if (!this.required && !this.value) {
        return null;
      }

      return !this.errors && (!this.required || !!this.value);
    },
  },
  methods: {
    handleChange(event) {
      switch (event.type) {
        case "drop":
          this.uploadFile(event.target.getAttribute("for"), event.dataTransfer.files);
          break;
        default:
          this.uploadFile(event.target.name, event.target.files);
          break;
      }
    },
    removeFile() {
      this.$emit("input", null);

      if (this.$refs[`${this.field}fileinput`]) {
        this.$refs[`${this.field}fileinput`].reset();
      }
    },
    async uploadFile(fieldName, fileList) {
      const formData = new FormData();

      if (!fileList.length) {
        return null;
      }

      Array.from(Array(fileList.length).keys()).map((x) =>
        formData.append(fieldName, fileList[x], fileList[x].name)
      );

      formData.append("field", fieldName);

      const file = await this.$store.dispatch("files/upload", formData);

      this.$emit("input", file);

      return file;
    },
  },
};
</script>

<style lang="scss">
.forms-file-uploader {
  .preview img {
    max-width: 100%;
  }

  .custom-file-label {
    overflow: hidden;
  }
}
</style>
