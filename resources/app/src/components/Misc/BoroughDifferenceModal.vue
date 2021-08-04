<template>
  <b-modal
    size="lg"
    class="borough-difference-modal"
    title="Voisinage, quartier: quelle différence?"
    id="borough-difference-modal"
    footer-class="d-none"
  >
    <p>
      Le voisinage est dans le quartier, le quartier est dans l'arrondissement… «L'arbre est dans
      ses feuilles, Marilon Marilé !»
    </p>

    <p>
      Être proche de ses <span class="no-break">voisin-e-s</span>, c'est la base de LocoMotion.
      C'est pour cette raison qu'on privilégie la création de sous-groupes LocoMotion à l'échelle
      très très locale.
    </p>

    <p>
      Un voisinage LocoMotion est créé par des gens qui habitent à 5-10 minutes à pied. Ça prend des
      personnes qui veulent s'impliquer dans le projet et le faire évoluer. Comment favoriser les
      liens dans le voisinage? Est-ce qu'on veut ajouter à notre flotte collective un vélo
      électrique ou des remorques? Solon vous accompagne!
    </p>

    <p>
      Le quartier est plus grand. Il réfère à un territoire urbain reconnu par la population et les
      institutions locales (par exemple, La Petite-Patrie) et peut contenir plusieurs voisinages.
      Participer à LocoMotion dans son quartier, c'est avoir accès à certains véhicules et c'est une
      porte d'entrée vers la création de son voisinage!
    </p>

    <p>
      Les véhicules sont pour le voisinage ou le quartier? L'ensemble des participant-e-s du
      quartier peut profiter des véhicules collectifs (appartiennent à Solon). Les propriétaires
      d'un véhicule (auto, vélo-cargo ou autre) peuvent décider de le rendre disponible à leur
      voisinage ou au quartier au complet.
    </p>

    <b-button variant="light" size="lg" @click="$bvModal.hide('borough-difference-modal')">
      OK!
    </b-button>
  </b-modal>
</template>

<script>
export default {
  name: "BoroughDifferenceModal",
};
</script>

<style lang="scss">
#borough-difference-modal {
  text-align: center;
}
</style>
