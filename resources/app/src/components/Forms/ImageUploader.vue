<template>
  <b-form-group :label="label" :label-for="field">
    <div class="mb-3" v-if="!value">
      <b-form-file v-bind:value="value" :state="Boolean(value)" :id="field"
        :ref="`${field}fileinput`" :placeholder="placeholder"
        :name="field" :accept="accept.join(',')"
        @change="uploadImage($event.target.name, $event.target.files)"/>
    </div>
    <div v-else>
      <figure class="preview">
        <img v-if="value.sizes" :src="value.sizes.thumbnail" >
        <img v-else src="/loading.gif" >

        <figcaption>{{ value.original_filename }}</figcaption>
      </figure>
      <b-button variant="warning" @click="removeImage">
        <small>{{ removeImageText }}</small>
      </b-button>
    </div>
  </b-form-group>
</template>

<script>
export default {
  name: 'FormsImageUploader',
  data() {
    return {
      errors: [],
    };
  },
  props: {
    accept: {
      default: () => ['image/png', 'image/jpg', 'image/jpeg'],
      type: Array,
    },
    field: {
      required: true,
      type: String,
    },
    label: {
      required: true,
      type: String,
    },
    placeholder: {
      default: 'Envoyer une image...',
      type: String,
    },
    removeImageText: {
      default: "Retirer l'image",
      type: String,
    },
    value: {
      type: Object,
      require: false,
      default: null,
    },
  },
  methods: {
    removeImage() {
      this.$emit('input', null);

      if (this.$refs[`${this.field}fileinput`]) {
        this.$refs[`${this.field}fileinput`].reset();
      }
    },
    async uploadImage(fieldName, fileList) {
      const formData = new FormData();

      if (!fileList.length) {
        return null;
      }

      Array
        .from(Array(fileList.length).keys())
        .map(x => formData.append(fieldName, fileList[x], fileList[x].name));

      formData.append('field', fieldName);

      const image = await this.$store.dispatch('images/upload', formData);

      this.$emit('input', image);

      return image;
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
