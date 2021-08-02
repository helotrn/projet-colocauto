<template>
  <b-alert variant="info" show class="mailchimp-newsletter">
    <form
      :action="mailchimpAction"
      method="post"
      name="mc-embedded-subscribe-form"
      novalidate=""
      target="_blank"
    >
      <div>
        <label for="mce-EMAIL">Je veux recevoir des nouvelles!</label>
        <input
          v-model="item.email"
          name="EMAIL"
          required=""
          type="email"
          placeholder="email address"
          style="display: none"
        />
        <div>
          <div>
            <label>Consentement</label>
            <p>
              Je consens &agrave; recevoir les communications de Solon sur les activit&eacute;s de
              l'organisme par les moyens suivants:
            </p>

            <fieldset name="interestgroup_field">
              <label for="gdpr_28697">
                <input
                  name="gdpr[28697]"
                  type="checkbox"
                  value="Y"
                  v-model="item.opt_in_newsletter"
                  @click="emitOptIn"
                />
                Courriel
              </label>
            </fieldset>

            <p>
              Vous pouvez changer d'avis &agrave; tout moment, en cliquant sur le lien "Me
              d&eacute;sinscrire", pr&eacute;sent dans tous les courriels que vous recevrez de notre
              part.
            </p>
          </div>

          <div>
            <p>
              Nous utilisons Mailchimp pour nos communications. En cliquant pour vous abonner, vous
              reconnaissez que vos informations seront transférées à Mailchimp pour traitement.
              <a href="https://mailchimp.com/legal/" target="_blank">
                En savoir plus sur les pratiques de confidentialité de Mailchimp ici.
              </a>
            </p>
          </div>
        </div>

        <div>
          <input
            tabindex="-1"
            name="b_23711ba00aedd74268f1974a9_0e5ad9b3b1"
            type="hidden"
            value=""
          />
        </div>

        <div>
          <b-button name="subscribe" type="submit">Je m'inscris!</b-button>
        </div>
      </div>
    </form>
  </b-alert>
</template>

<script>
export default {
  name: "MailchimpNewsletter",
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      mailchimpAction:
        "https://solon-collectif.us18.list-manage.com/subscribe/post" +
        "?u=23711ba00aedd74268f1974a9&amp;id=0e5ad9b3b1",
    };
  },
  methods: {
    emitOptIn(val) {
      this.$emit("optin", val.target.checked);
    },
  },
};
</script>

<style lang="scss"></style>
