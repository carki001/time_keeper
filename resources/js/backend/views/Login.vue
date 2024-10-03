<template>
    <v-container class="fill-height blue lighten-5" fluid>
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="4" xl="3">
                <div class="blue lighten-5 pb-2">
                    <div class="text-center mb-4">
                        <img
                            align="middle"
                            src="/images/logo.png"
                            height="62"
                        />
                    </div>
                    <p class="itb-slogan text-center m-0">MEASURE YOUR WORK</p>
                </div>
                <v-card
                    class="elevation-12"
                    :loading="loading"
                    :disabled="loading"
                >
                    <v-form ref="form" v-model="valid">
                        <v-toolbar color="primary" dark flat>
                            <v-toolbar-title>{{
                                $t("title.login")
                            }}</v-toolbar-title>
                        </v-toolbar>
                        <v-card-text>
                            <v-text-field
                                v-model="user.email"
                                :label="$t('general.e_mail')"
                                placeholder="..."
                                name="email"
                                prepend-icon="mdi-account"
                                type="text"
                                :rules="emailRules"
                                required
                            ></v-text-field>
                            <v-text-field
                                v-model="user.password"
                                :label="$t('general.password')"
                                placeholder="..."
                                name="password"
                                prepend-icon="mdi-lock"
                                type="password"
                                :rules="[(v) => !!v || $t('general.required')]"
                                required
                            ></v-text-field>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="primary"
                                @click="validateAndLogin"
                                :loading="loading"
                                class="mr-2"
                                >{{ $t("general.login") }}</v-btn
                            >
                        </v-card-actions>
                    </v-form>
                </v-card>
            </v-col>
        </v-row>

        <SnackMessage ref="SnackMessage" />
    </v-container>
</template>
<script>
// import SnackMessage from "@/backend/components/SnackMessage.vue";
import SnackMessage from "../components/SnackMessage";

import Jwt from "jsonwebtoken";

export default {
    data() {
        return {
            user: {
                email: "",
                password: "",
                remember_me: false,
            },
            valid: true,
            loading: false,
            emailRules: [
                (v) => !!v || this.$t("general.required"),
                (v) =>
                    /.+@.+\..+/.test(v) || this.$t("general.email_not_valid"),
            ],
        };
    },

    components: {
        SnackMessage,
    },

    methods: {
        snackMessage(msg, type) {
            this.$refs.SnackMessage.showMessage(msg, type);
        },
        validateAndLogin() {
            if (this.$refs.form.validate()) {
                this.loading = true;
                axios
                    .post("/api/auth/login", this.user)
                    .then((response) => {
                        if (response.data.status_code == 201) {
                            this.$refs.SnackMessage.showMessage(
                                "general.error_login",
                                "error",
                                5000
                            );
                            this.loading = false;
                        } else {
                            localStorage.setItem(
                                "itb-spa-token",
                                Jwt.sign({ user: response.data }, "itbjwttoken")
                            );

                            this.$store.dispatch(
                                "authenticate",
                                response.data.user
                            );
                            this.$router.push("/admin");
                        }
                    })
                    .catch((error) => {
                        this.$refs.SnackMessage.showMessage(
                            "general.error_message",
                            "error"
                        );
                        this.loading = false;
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.log(error.response.data);
                            console.log(error.response.status);
                            console.log(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.log(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.log("Error", error.message);
                        }
                        console.log(error.config);
                    });
            }
        },
    },
};
</script>
