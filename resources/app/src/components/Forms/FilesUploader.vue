<template>
  <b-form-group class="forms-files-uploader" :label="label" :label-for="field">
    <div v-if="loading">
      <img src="/loading.svg" />
    </div>
    <div v-else>
      <ul>
        <li v-if="value" v-for="file in value" class="mb-1">
          <a :href="file.url" target="_blank">
            {{ file.original_filename }}
          </a>
          <b-button
            size="sm"
            class="ml-2"
            variant="warning"
            @click="() => removeFile(file.id)"
            v-if="!disabled"
          >
            {{ removeFileText }}
          </b-button>
        </li>
      </ul>
      <div>
        <b-form-file
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
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: "FormsFilesUploader",
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
      default: "Ajouter un fichier...",
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
      type: Array,
      require: false,
      default: [],
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
    removeFile(fileId) {
      this.$emit(
        "input",
        this.value.filter((f) => f.id !== fileId)
      );
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

      this.$emit("input", [...this.value, file]);

      if (this.$refs[`${this.field}fileinput`]) {
        this.$refs[`${this.field}fileinput`].reset();
      }

      return file;
    },
  },
};
</script>

<style lang="scss">
.forms-files-uploader {
  .preview img {
    max-width: 100%;
  }

  .custom-file-label {
    overflow: hidden;
  }
}
</style>
