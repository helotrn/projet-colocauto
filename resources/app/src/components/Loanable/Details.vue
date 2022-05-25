<template>
  <div class="loanable-details">
    <header class="loanable-details__header">
      <img class="loanable-details-image" :src="loanableImage" :alt="loanableTitle" />
      <user-avatar :user="ownerUser" class="loanable-details__avatar" />
    </header>
    <main class="loanable-details__content">
      <h4 class="loanable-details__loanable-title">{{ loanableTitle }}</h4>
      <div class="loanable-card__tags">
        <div>
          <span class="badge badge-secondary"
            ><svg width="34" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" class="">
              <path
                d="M30.02.698a.664.664 0 00-.576-.334L13.328.413a.666.666 0 00-.562.316L8.97 6.979a25.29 25.29 0 00-1.981.347c-.321.07-.646.137-.967.203-1.168.241-2.275.465-3.176.82C1.705 8.799.96 9.818.797 11.145.668 12.202.51 14.28.501 14.368a.66.66 0 00.659.711l3.364-.009a3.777 3.777 0 001.02 1.823A3.782 3.782 0 008.227 18h.009a3.768 3.768 0 002.688-1.124 3.747 3.747 0 001.006-1.832l10.56-.03a3.744 3.744 0 001.019 1.818 3.782 3.782 0 002.684 1.107h.009a3.787 3.787 0 002.688-1.125 3.746 3.746 0 001.006-1.831l2.934-.005a.66.66 0 00.659-.628c.004-.14.145-3.496-.773-7.037C31.869 4.001 30.094.834 30.02.698zm1.203 6.136l-9.154.044-.013-5.174 6.997-.022c.422.8 1.476 2.9 2.17 5.152zM13.701 1.726l7.037-.022.013 5.174-10.208.049 3.158-5.201zM9.998 15.949c-.47.47-1.093.734-1.757.734h-.008c-.664 0-1.287-.26-1.757-.725a2.473 2.473 0 01-.734-1.757c0-.664.255-1.292.725-1.762a2.473 2.473 0 011.757-.733h.009a2.493 2.493 0 012.49 2.486 2.48 2.48 0 01-.725 1.757zm16.213.676h-.008c-.664 0-1.287-.259-1.757-.724a2.473 2.473 0 01-.734-1.757c0-.664.255-1.292.725-1.762a2.473 2.473 0 011.757-.733h.009a2.493 2.493 0 011.765 4.247c-.465.47-1.093.73-1.757.73zm3.77-2.951a3.812 3.812 0 00-3.778-3.347h-.01c-1.018.004-1.972.4-2.688 1.124a3.785 3.785 0 00-1.08 2.249l-10.415.026a3.812 3.812 0 00-3.777-3.342h-.01a3.786 3.786 0 00-2.687 1.124 3.773 3.773 0 00-1.08 2.245l-2.58.008c.058-.715.154-1.8.233-2.455.101-.843.536-1.458 1.226-1.73.795-.312 1.845-.528 2.956-.756.325-.066.655-.132.98-.207 1.146-.246 2.007-.342 2.139-.36l22.17-.105c.53 2.297.614 4.485.618 5.521l-2.218.005z"
                fill="#fff"
              ></path>
              <path
                d="M8.233 12.66c-.005 0-.005 0 0 0-.413 0-.8.162-1.085.452a1.53 1.53 0 001.085 2.614h.004c.409 0 .795-.163 1.085-.453.29-.29.448-.677.448-1.085a1.543 1.543 0 00-1.537-1.529zm0 2.406a.877.877 0 01-.879-.874.874.874 0 111.748-.004.874.874 0 01-.87.878zM26.202 12.602h-.004a1.53 1.53 0 00-1.085.453c-.29.29-.448.676-.448 1.084.004.844.69 1.53 1.533 1.53h.004a1.53 1.53 0 001.085-.453 1.53 1.53 0 00-1.085-2.614zm.005 2.407a.88.88 0 01-.879-.874.88.88 0 01.874-.878h.005c.233 0 .452.092.615.254.167.163.259.383.259.62 0 .487-.391.878-.874.878z"
                fill="#fff"
              ></path>
            </svg>
            Auto
          </span>
        </div>
        <!---->
      </div>
      <div class="loanable-card__estimated-fare">
        <i title="Recherchez pour valider la disponibilité et le coût" class="muted"
          >Coût estimé: N/A
        </i>
      </div>
    </main>
    <footer class="loanable-details__footer">
      <button type="button" class="btn btn-outline-primary">Demande d'emprunt</button>
    </footer>
  </div>
</template>

<script>
import UserAvatar from "@/components/User/Avatar.vue";

export default {
  name: "LoanableDetails",
  components: {
    UserAvatar,
  },
  props: {
    loanable: {
      type: Object,
      required: false,
      default: null,
    },
  },
  computed: {
    loanableTitle() {
      return this?.loanable?.name;
    },
    loanableImage() {
      return this?.loanable?.image?.sizes?.thumbnail;
    },
    ownerUser() {
      return this?.loanable?.owner?.user;
    },
  },
};
</script>

<style lang="scss">
.loanable-details {
  // Fixed width for the moment. We'll deal with resizing later.
  width: 16rem;

  &__header {
    position: relative;
    // At the moment, thumbnails are 256px x 160px -> 16rem x 10rem.
    height: 10rem;
    width: 100%;
  }
  &__avatar {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
  }
  &__content {
    max-height: 12rem;
    overflow-y: auto;
    padding: 0.5rem;
  }
  &__footer {
    height: 3.5rem;
    padding: 0.5rem;
    // To get past the arrow that display over the button.
    padding-bottom: 0.75rem;

    display: flex;
    justify-content: space-around;
    align-items: center;
  }
  &__loanable-image {
    position: relative;
    height: 10rem;
    width: 100%;
  }
  &__owner-avatar {
    position: absolute;
    width: 4rem;
    height: 4rem;
    bottom: 0;
    right: 0;
  }
  /* Temporary element until we create sections. */
  &__loanable-title {
    text-align: center;
  }
}
</style>
