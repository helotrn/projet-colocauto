<template>
  <b-form-group
    :class="`multiple-images-uploader ${validationStateClass}`"
    :label="label"
    :label-for="field"
    :description="description"
  >
    <b-row>
      <b-col lg="3" v-for="(picture, index) in value" :key="picture.id">
        <figure class="preview">
          <a v-if="picture.sizes" :href="picture.url" target="_blank" :style="{aspectRatio: previewAspectRatio}">
            <img :src="picture.sizes.thumbnail" :style="[aspectRatioStyle]" />
          </a>
          <img v-else src="/loading.svg" />
          <b-button variant="link" @click="$emit('delete', index)">
            {{ removeImageText }} <icons-trash />
          </b-button>
        </figure>
      </b-col>
      <b-col lg="3">
        <div v-if="loading">
          <img src="/loading.svg" />
        </div>
        <b-form-file v-else
          :value="nextPicture"
          :state="validationState"
          :id="field"
          :ref="`${field}imageinput`"
          :placeholder="placeholder"
          :name="field"
          :accept="accept.join(',')"
          browse-text="Téléverser"
          drop-placeholder="Déposer l'image ici..."
          @change="handleChange"
        />
        <div class="invalid-feedback" v-if="errors">
          {{ errors.message }}
        </div>
      </b-col>
    </b-row>
  </b-form-group>
</template>

<script>
import IconsTrash from "@/assets/icons/trash.svg";
export default {
  name: "MultipleImagesUploader",
  components: {IconsTrash},
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
      type: Array,
      require: false,
      default: [],
    },
    previewAspectRatio: {
      type: String,
      required: false,
      default: null,
    },
  },
  data() {
    return ({
      loading: false,
      errors: null,
      nextPicture: null,
    })
  },
  computed: {
    aspectRatioStyle() {
      if (!this.previewAspectRatio) {
        return {};
      }
      return {
        objectFit: "cover",
        height: "100%",
        width: "100%",
      };
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
    async uploadImage(fieldName, fileList) {
      this.loading = true;
      try {
        const formData = new FormData();
        // remove any disabiguity digits at the end
        fieldName = fieldName.replace(/(.*?)\d+/, '$1')

        if (!fileList.length) {
          return null;
        }

        Array.from(Array(fileList.length).keys()).map((x) =>
          formData.append(fieldName, fileList[x], fileList[x].name)
        );

        formData.append("field", fieldName);

        const image = await this.$store.dispatch("images/upload", formData);

        this.$emit("input", image);
      } catch (e) {
        this.errors = e;
      }

      if( this.$store.state.images.errors ) {
        this.errors = this.$store.state.images.errors;
      }

      this.loading = false;
    },
  },
};
</script>

<style lang="scss">
.multiple-images-uploader {
  width: 100%;
  min-height: 160px;

  .custom-file {
    min-height: 160px;
    height: 100%;
  }

  .custom-file-label {
    height: calc(100% - 1.95rem);

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
      position: absolute;
      bottom: -1.95rem;
      left: 0;
      right: 19px;
      top: auto;
      background: transparent;
      text-align: right;
      border-left: none;
    }
    &::before {
      content: '';
      position: absolute;
      height: 19px;
      width: 19px;
      bottom: -1.575rem;
      right: 0;
      background: url('/upload.svg') no-repeat;
    }
  }

  + .invalid-feedback {
    margin-top: -20px;
  }
}

.preview {
  margin-bottom: 0;
  a {
    display: block;
    
  }
  img {
    max-width: 100%;
  }
  button {
    width: 100%;
    text-align: right;
    color: black;
    padding-right: 0;
  }
}
</style>
