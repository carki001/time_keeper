<template>
    <v-container class="fill-height admin-bg" fluid>
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="4" xl="3">
                <v-card
                    class="elevation-12"
                    :loading="loading"
                    :disabled="loading"
                >
                    <v-form ref="form" v-model="valid">
                        <v-toolbar color="primary" dark flat>
                            <v-toolbar-title>{{
                                $t("title.forgotten_password")
                            }}</v-toolbar-title>
                        </v-toolbar>
                        <v-card-text>
                            <v-text-field
                                v-model="email"
                                :label="$t('general.e_mail')"
                                name="email"
                                prepend-icon="mdi-account"
                                type="text"
                                :rules="emailRules"
                                required
                            ></v-text-field>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn
                                text
                                to="/login"
                                color="primary"
                                :disabled="loading"
                                >{{ $t("general.login") }}</v-btn
                            >
                            <v-spacer></v-spacer>
                            <v-btn
                                color="primary"
                                @click="validateAndResetPassword"
                                :loading="loading"
                                >{{ $t("general.restore_password") }}</v-btn
                            >
                        </v-card-actions>
                    </v-form>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
import Jwt from "jsonwebtoken";

export default {
    data() {
        return {
            email: "",
            valid: true,
            loading: false,
            emailRules: [
                (v) => !!v || this.$t("general.required"),
                (v) =>
                    /.+@.+\..+/.test(v) || this.$t("general.email_not_valid"),
            ],
        };
    },

    methods: {
        validateAndResetPassword() {
            if (this.$refs.form.validate()) {
                this.loading = true;
            }
        },
    },
};
</script>
