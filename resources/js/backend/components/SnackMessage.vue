<template>
    <v-snackbar
        content-class="d-flex justify-space-between align-center pe-0"
        v-model="show"
        :timeout="timeout"
        :color="color"
        top
    >
        <span>{{ text }}</span>
        <v-btn text @click.stop="show = false">{{ $t("general.close") }}</v-btn>
    </v-snackbar>
</template>

<script>
export default {
    name: "SnackMessage",

    data() {
        return {
            timeout: 3000,
            color: "",
            text: "",
            show: false,
        };
    },

    methods: {
        showMessage(message, status, isCustomMessage = false, timeout = 3000) {
            this.timeout = timeout;
            this.color = status;

            if (isCustomMessage) {
                this.text = message;
            } else {
                this.text = this.$t(message);
            }

            this.show = true;
        },
    },
};
</script>
