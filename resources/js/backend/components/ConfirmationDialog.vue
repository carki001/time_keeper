<template>
  <v-dialog v-model="dialog" persistent max-width="350">
    <v-card>
      <v-card-title class="headline">{{ $t('general.delete_item') }}</v-card-title>
      <v-card-text>
        {{ $t(text) }}
        <strong>{{ name }}</strong>?
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="error" dark @click="confirm">{{ $t("general.yes") }}</v-btn>
        <v-btn @click="cancel">{{ $t("general.no") }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>


<script>
export default {
  name: "ConfirmationDialog",
  props: {
    name: "",
    text: "",
  },

  data() {
    return {
      dialog: true,
    };
  },

  methods: {
    cancel() {
      this.$emit("cancel");
    },
    confirm() {
      this.$emit("confirm");
    },
  },
};
</script>
