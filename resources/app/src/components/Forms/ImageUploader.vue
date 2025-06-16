<template>
  <b-form-group
    :class="`forms-image-uploader ${validationStateClass}`"
    :label="label"
    :label-for="field"
    :description="description"
  >
    <div v-if="loading">
      <img src="/loading.svg" />
    </div>
    <div class="mb-3" v-else-if="!value">
      <b-form-file
        :value="value"
        :state="validationState"
        :id="field"
        :ref="`${field}imageinput`"
        :placeholder="placeholder"
        :name="field"
        :accept="accept.join(',')"
        browse-text="Sélectionner"
        drop-placeholder="Déposer l'image ici..."
        @change="handleChange"
      />
      <div class="invalid-feedback" v-if="errors">
        {{ errors.message }}
      </div>
    </div>
    <div v-else>
      <figure class="preview">
        <img v-if="value.sizes" :src="value.sizes.thumbnail" :style="[this.aspectRatioStyle]" />
        <img src="/loading.svg" v-else />

        <figcaption>
          <a :href="value.url" target="_blank">
            {{ value.original_filename }}
          </a>
        </figcaption>
      </figure>
      <b-button variant="outline-primary" @click="removeImage">
        <small>{{ removeImageText }}</small>
      </b-button>
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: "FormsImageUploader",
  props: {
    accept: {
      default: () => ["*.png", "*.jpg", "*.jpeg", "image/png", "image/jpg", "image/jpeg"],
      type: Array,
    },
    description: {
      type: String,
      required: false,
      default: "",
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
    removeImageText: {
      default: "Retirer l'image",
      type: String,
    },
    required: {
      type: Boolean,
      required: false,
      default: false,
    },
    state: {
      type: Boolean,
      required: false,
      default: null,
    },
    value: {
      type: Object,
      require: false,
      default: null,
    },
    previewAspectRatio: {
      type: String,
      required: false,
      default: null,
    },
  },
  computed: {
    aspectRatioStyle() {
      if (!this.previewAspectRatio) {
        return {};
      }
      return {
        aspectRatio: this.previewAspectRatio,
        objectFit: "cover",
      };
    },
    errors() {
      return this.$store.state.images.errors;
    },
    loading() {
      return !!this.$store.state.images.cancelToken;
    },
    validationState() {
      return !this.errors && ((this.required && !!this.value) || this.state);
    },
    validationStateClass() {
      switch (this.validationState) {
        case true:
          return "is-valid";
        case false:
          return "is-invalid";
        default:
          return "";
      }
    },
  },
  methods: {
    handleChange(event) {
      switch (event.type) {
        case "drop":
          this.uploadImage(event.target.getAttribute("for"), event.dataTransfer.files);
          break;
        default:
          this.uploadImage(event.target.name, event.target.files);
          break;
      }
    },
    removeImage() {
      this.$emit("input", null);

      if (this.$refs[`${this.field}imageinput`]) {
        this.$refs[`${this.field}imageinput`].reset();
      }
    },
    async uploadImage(fieldName, fileList) {
      const formData = new FormData();

      if (!fileList.length) {
        return null;
      }

      Array.from(Array(fileList.length).keys()).map((x) =>
        formData.append(fieldName, fileList[x], fileList[x].name)
      );

      formData.append("field", fieldName);

      const image = await this.$store.dispatch("images/upload", formData);

      this.$emit("input", image);

      return image;
    },
  },
};
</script>

<style lang="scss">
.forms-image-uploader {
  width: 100%;
  min-height: 200px;

  .custom-file {
    min-height: 180px;
  }

  .custom-file-label {
    overflow: hidden;
    height: calc(200px - 20px);
    text-align: center;

    border: 1px dashed $light-grey;

    background-image: url("/cloud.svg");
    background-repeat: no-repeat;
    background-size: auto 50%;
    background-position: top 40px center;

    &.dragging {
      background-image: url("/cloud-active.svg");
      border: 1px dashed $primary;
    }

    &::after {
      border-left: 0;
      border-radius: 0.25rem;
      position: absolute;
      bottom: 0.5rem;
      left: 0;
      right: 0;
      top: auto;
      width: 50%;
      min-width: 200px;
      margin: 0 auto;
    }
  }

  + .invalid-feedback {
    margin-top: -20px;
  }
}

.preview img {
  max-width: 100%;
}
</style>
