<template>
    <v-dialog v-model="show" max-width="600px">
        <v-card>
            <v-card-title>
                <span class="headline">{{ $t("users.edit_user") }}</span>
            </v-card-title>

            <v-card-text>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <v-container>
                        <v-row>
                            <v-col cols="12" sm="12">
                                <v-text-field
                                    v-model="user.name"
                                    :label="$t('users.name')"
                                    autofocus
                                    :rules="requiredRule"
                                ></v-text-field>
                                <v-file-input
                                    chips
                                    prepend-icon="mdi-camera"
                                    accept="image/*"
                                    ref="attachImageInput"
                                    v-model="avatar"
                                    :label="$t('users.avatar')"
                                    @click:clear="deleteExistingAvatar"
                                ></v-file-input>
                                <v-text-field
                                    ref="email"
                                    v-model="user.email"
                                    :rules="emailRules"
                                    :label="$t('users.email')"
                                    autocomplete="itb"
                                ></v-text-field>
                                <v-text-field
                                    v-model="user.password"
                                    :append-icon="
                                        showEye ? 'mdi-eye' : 'mdi-eye-off'
                                    "
                                    :rules="passwordRules"
                                    :type="showEye ? 'text' : 'password'"
                                    name="input-10-2"
                                    :label="$t('users.password')"
                                    :hint="
                                        $t(
                                            'general.at_least_8_characters_and_number'
                                        )
                                    "
                                    value="wqfasds"
                                    class="input-group--focused"
                                    @click:append="showEye = !showEye"
                                    autocomplete="itb"
                                ></v-text-field>
                                <v-autocomplete
                                    hide-details="auto"
                                    :items="timezones"
                                    v-model="user.preferred_timezone"
                                    item-value="utc[0]"
                                    item-text="text"
                                    :label="$t('users.preferred_timezone')"
                                    :rules="requiredRule"
                                >
                                </v-autocomplete>
                                <v-select
                                    :items="languages"
                                    class="mt-7"
                                    v-model="user.preferred_language"
                                    item-value="key"
                                    item-text="translated_name"
                                    :label="$t('users.preferred_language')"
                                >
                                    <template
                                        v-slot:selection="{ item, index }"
                                    >
                                        <img
                                            width="20"
                                            class="mr-2"
                                            :src="item.flag_image_path"
                                        /><span>{{
                                            item.translated_name
                                        }}</span>
                                    </template>
                                    <template v-slot:item="{ item }">
                                        <img
                                            width="20"
                                            class="mr-2"
                                            :src="item.flag_image_path"
                                        /><span>{{
                                            item.translated_name
                                        }}</span>
                                    </template>
                                </v-select>
                                <v-text-field
                                    ref="country"
                                    v-model="user.country"
                                    :label="$t('users.country')"
                                ></v-text-field>
                                <v-text-field
                                    ref="city"
                                    v-model="user.city"
                                    :label="$t('users.city')"
                                ></v-text-field>
                                <v-text-field
                                    ref="postalcode"
                                    v-model="user.postalcode"
                                    :label="$t('users.postalcode')"
                                ></v-text-field>
                                <v-text-field
                                    ref="address"
                                    v-model="user.address"
                                    :label="$t('users.address')"
                                ></v-text-field>
                                <v-text-field
                                    ref="phone"
                                    v-model="user.phone"
                                    :rules="phoneRules"
                                    :label="$t('users.phone')"
                                ></v-text-field>
                                <v-text-field
                                    ref="company"
                                    v-model="user.company"
                                    :label="$t('users.company')"
                                ></v-text-field>
                                <v-text-field
                                    ref="vat"
                                    v-model="user.vat"
                                    :label="$t('users.vat')"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-form>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark @click="submit">{{
                    $t("general.save")
                }}</v-btn>
                <v-btn @click.stop="show = false">{{
                    $t("general.cancel")
                }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "OwnUserForm",
    props: {
        form: {
            type: Object,
        },
        user: Object,
        timezones: Array,
        languages: Array,
        visible: Boolean,
    },

    data() {
        return {
            valid: true,
            showEye: false,
            requiredRule: [(value) => !!value || this.$t("general.required")],
            emailRules: [
                (value) => !!value || this.$t("general.required"),
                (value) =>
                    /.+@.+/.test(value) || this.$t("general.email_not_valid"),
            ],
            passwordRules: [
                (value) =>
                    !value ||
                    (value.length >= 8 &&
                        /\d/.test(value) &&
                        /[!@#\$%\^\&*\)\(+=._-]/.test(value)) ||
                    this.$t("general.at_least_8_characters_and_number"),
            ],
            phoneRules: [
                (value) =>
                    !value ||
                    /^([+]?[\s]?[0-9]+)?([\s]?[0-9])+$/.test(value) ||
                    this.$t("general.phone_must_be_numeric"),
            ],
            userImage: null,
        };
    },

    watch: {
        show(val) {
            !val && this.$refs.form.reset();
        },
    },

    computed: {
        show: {
            get() {
                return this.visible;
            },
            set(value) {
                if (!value) {
                    this.$emit("close");
                }
            },
        },

        avatar: {
            get: function () {
                if (!this.userImage) {
                    if (this.user.avatar == null) {
                        return null;
                    } else {
                        return { name: this.user.avatar.split("/").pop() };
                    }
                } else {
                    return this.userImage;
                }
            },
            set: function (val) {
                this.userImage = val;
            },
        },
    },

    methods: {
        async submit() {
            this.user.currentUserId = this.$store.state.profile.id;

            if (this.$refs.form.validate()) {
                var formData = new FormData();
                Object.entries(this.user).forEach(([key, value]) => {
                    if (value) {
                        formData.append(key, value);
                    } else {
                        formData.append(key, "");
                    }
                });

                if (this.avatar instanceof File) {
                    formData.append("avatar", this.avatar);
                    formData.append("createAvatar", "true");
                }
                if (this.avatar == null) {
                    formData.append("deleteAvatar", "true");
                }

                await axios
                    .post("/api/updateOwnUser", formData, {
                        headers: {
                            Authorization:
                                "Bearer " +
                                this.$store.state.tokenData.user.access_token,
                        },
                    })
                    .then((response) => {
                        if (response.data.isError) {
                            this.$emit(
                                "snackMessage",
                                response.data.message,
                                "warning"
                            );
                            this.$refs.email.focus();
                        } else {
                            this.$emit("updateUser", response.data);
                            this.$emit(
                                "snackMessage",
                                "users.user_saved",
                                "success"
                            );
                            this.$emit("close");
                            if (
                                this.$route.name == "users" ||
                                this.$route.name == "teams" ||
                                this.$route.name == "worktimes" ||
                                this.$route.name == "projects"
                            ) {
                                this.$eventBus.$emit("refreshCurrentUser");
                            }
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        this.$emit("snackMessage", "general.error", "error");
                    });
            }
        },
        deleteExistingAvatar() {
            this.user.avatar = null;
        },
    },
};
</script>
