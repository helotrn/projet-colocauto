<template>
  <b-form-group class="forms-files-uploader" :label="label" :label-for="field">
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
      <li v-if="loading">
        <layout-loading with-button />
      </li>
    </ul>
    <div>
      <validation-provider
        ref="validator"
        :rules="{ required }"
        name="fichier"
        v-slot="{ validated, valid, errors }"
        :detect-input="false"
      >
        <b-form-file
          :state="validated ? valid : null"
          :id="field"
          :ref="`${field}fileinput`"
          :placeholder="placeholder"
          :disabled="disabled || loading || value.length >= max"
          :name="field"
          :accept="accept.join(',')"
          browse-text="Sélectionner"
          drop-placeholder="Déposer le fichier ici..."
          @change="handleChange"
        />
        <div class="invalid-feedback" v-if="errors">
          {{ errors[0] }}
        </div>
      </validation-provider>
      <b-alert variant="warning" show v-if="value.length >= max" class="mt-2">
        Vous avez attein le nombre maximum de fichiers pour ce champs. Supprimez d'anciens fichiers
        pour en ajouter des différents.
      </b-alert>
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: "FormsFilesUploader",
  mounted() {
    this.$refs.validator.initialValue = this.value;
  },
  watch: {
    value(newValue) {
      this.$refs.validator.syncValue(newValue);
      this.$refs.validator.validate();
    },
  },
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
      default: () => [],
    },
    max: {
      type: Number,
      require: false,
      default: 5,
    },
  },
  data: () => ({
    loading: false,
  }),
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

      this.loading = true;

      Array.from(Array(fileList.length).keys()).map((x) =>
        formData.append(fieldName, fileList[x], fileList[x].name)
      );

      formData.append("field", fieldName);

      const file = await this.$store.dispatch("files/upload", formData);

      this.$emit("input", [...this.value, file]);

      if (this.$refs[`${this.field}fileinput`]) {
        this.$refs[`${this.field}fileinput`].reset();
      }

      this.loading = false;

      return file;
    },
  },
};
</script>

<style lang="scss">
.forms-files-uploader {
  .custom-file-label {
    overflow: hidden;
  }
}
</style>
