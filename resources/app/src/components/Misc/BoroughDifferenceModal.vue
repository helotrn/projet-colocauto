<template>
  <b-modal size="lg" class="borough-difference-modal"
    title="Voisinage, quartier: quelle différence?"
    id="borough-difference-modal" footer-class="d-none">
    <p>
      Un voisinage LocoMotion est créé à l'initiative des gens qui y habitent, qui veulent
      s'impliquer dans ce système de partage et le faire évoluer. Être proche de ses voisin-e-s,
      c'est la base de LocoMotion. C'est pour cette raison qu'un voisinage correspond à une zone
      d'environ 500 mètres de diamètre. Comment favoriser les liens dans le voisinage?
      Est-ce qu'on veut ajouter à notre flotte collective un vélo électrique ou des remorques?
      Solon vous accompagne dans toutes ces questions! Du financement peut aussi être rendu
      disponible.
    </p>

    <p>
      Le quartier est plus grand et suit les limites administratives. Il peut donc contenir
      plusieurs voisinages LocoMotion. Des personnes peuvent s'inscrire à LocoMotion dans leur
      quartier sans appartenir à un voisinage.
    </p>

    <p>
      Et les véhicules sont pour le voisinage, le quartier? L'ensemble des participant-e-s du
      quartier peut profiter des véhicules collectifs de Solon, l'organisme derrière LocoMotion.
      Les propriétaires d'un véhicule (auto, vélo-cargo ou autre) peuvent décider de le mettre à
      disposition de leur voisinage ou du quartier au complet.
    </p>

    <b-button variant="light" size="lg" @click="$bvModal.hide('borough-difference-modal')">
      OK!
    </b-button>
  </b-modal>
</template>

<script>
export default {
  name: 'BoroughDifferenceModal',
};
</script>

<style lang="scss">
#borough-difference-modal {
  text-align: center;
}

</style>
