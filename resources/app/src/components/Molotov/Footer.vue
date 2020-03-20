<template>
  <b-container fluid class="molotov-footer">
    <b-row>
      <b-col>
        <div class="molotov-footer__inner">
          <a href="https://molotov.ca" target="_blank">
            <span>Fait avec</span>
            <span class="love"><svg-coeur /></span>
            <span>par Molotov</span>
          </a>
        </div>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import Coeur from '@/assets/svg/coeur.svg';

export default {
  name: 'MolotovFooter',
  components: {
    'svg-coeur': Coeur,
  },
};
</script>

<style lang="scss">
$molotov-footer-height: 44px !default;
$molotov-footer-color: #fff;
$molotov-footer-hover-color: #fff;
$molotov-footer-background-color: #000;
$molotov-footer-heart-size: 20px !default;
$molotov-footer-heart-fill: $molotov-footer-background-color !default;
$molotov-footer-heart-stroke: #fc4e4e !default;
$molotov-footer-heart-stroke-width: 2px !default;
$molotov-footer-heart-fill-hover: $molotov-footer-heart-fill !default;

@keyframes heartbeat {
  0% {
    transform: scale(1);
  }

  25% {
    transform: scale(1.1);
  }

  40% {
    transform: scale(1);
  }

  60% {
    transform: scale(1.1);
  }

  100% {
    transform: scale(1);
  }
}

.molotov-footer {
  background-color: $molotov-footer-background-color;
  height: $molotov-footer-height;

  font-size: 14px;
  line-height: 16px;
  text-align: center;

  &__inner {
    margin-top: 12px;
    margin-bottom: 12px;

    a {
      color: $molotov-footer-color;
      fill: $molotov-footer-heart-fill;
      stroke: $molotov-footer-heart-stroke;
      transition: color $one-tick ease-in-out;
      text-decoration: none;

      .love {
        display: inline-block;
        transition: fill $one-tick ease-in-out;
        width: $molotov-footer-heart-size + 2 * $molotov-footer-heart-stroke-width * 2;
      }

      &:hover {
        text-decoration: none;
        color: $molotov-footer-hover-color;

        svg {
          top: -2px;
        }

        .love {
          fill: $molotov-footer-heart-fill-hover;
          animation: 0.8s infinite heartbeat;
        }
      }
    }

    svg {
      position: relative;
      top: -3px;

      overflow: visible;
      display: inline;
      height: $molotov-footer-heart-size;
      width: $molotov-footer-heart-size;
      stroke-width: $molotov-footer-heart-stroke-width;
    }
  }
}
</style>
