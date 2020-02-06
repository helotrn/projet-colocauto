<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('communauté', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations générales</h2>
            <admin-form :definition="form" :item="item" entity="communities" />
          </div>

          <div class="form__section">
            <h2>Zone géographique</h2>
            <b-form-group
              :description="`Zone géographique sous la forme d'une liste de tuples ` +
                '(latitude, longitude), un par ligne.'"
              label-for="area">
              <b-form-textarea
                id="area" name="area"
                v-model="area"
                rows="6" max-rows="12" />
            </b-form-group>
          </div>

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed">
                Sauvegarder
              </b-button>
              <b-button type="reset" :disabled="!changed" @click="reset">
                Réinitialiser
              </b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminForm from '@/components/Admin/Form.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'AdminCommunity',
  mixins: [FormMixin],
  components: {
    AdminForm,
  },
  computed: {
    area: {
      get() {
        return this.item.area.join('\n');
      },
      set(area) {
        const newItem = {
          ...this.item,
          area: area.split('\n'),
        };
        this.$store.commit(`${this.slug}/item`, newItem);
      },
    },
  },
};
</script>

<style lang="scss">
</style>
