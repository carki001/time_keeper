<template>
    <v-dialog v-model="show" max-width="800px">
        <v-card>
            <v-card-title>
                <span class="headline" v-if="isEdited && !isViewOnly">{{
                    $t("teams.edit_team")
                }}</span>
                <span class="headline" v-if="!isEdited && !isViewOnly">{{
                    $t("teams.add_team")
                }}</span>
                <span class="headline" v-if="isViewOnly">{{
                    $t("teams.view_team")
                }}</span>
            </v-card-title>

            <v-card-text>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <v-container>
                        <v-row>
                            <v-col cols="12" sm="12">
                                <v-text-field
                                    v-model="team.name"
                                    :label="$t('teams.name')"
                                    autofocus
                                    :rules="rules"
                                    :readonly="isViewOnly"
                                ></v-text-field>
                                <h4
                                    class="font-weight-light itb-subheadline mt-2"
                                >
                                    {{ $t("teams.team") }}

                                    <v-btn
                                        v-if="
                                            $store.state.profile.role ===
                                            'admin'
                                        "
                                        small
                                        color="success"
                                        @click="addUser"
                                        :loading="false"
                                    >
                                        {{ $t("general.add_to") }}
                                    </v-btn>
                                </h4>
                                <v-card
                                    class="d-flex align-center flex-wrap mt-2 pl-2"
                                    v-for="(team_user, index) in team.team"
                                    :key="team_user.email"
                                >
                                    <v-autocomplete
                                        class="itb-select-no-line"
                                        :items="users"
                                        v-model="team_user.pivot.user_id"
                                        item-value="id"
                                        item-text="name"
                                        @input="
                                            checkDuplicate(
                                                team_user.pivot.user_id
                                            )
                                        "
                                        :rules="userRules"
                                        :label="$t('teams.user')"
                                        :readonly="isViewOnly"
                                    ></v-autocomplete>
                                    <div style="width: 130px" class="ml-3">
                                        <v-select
                                            class="itb-select-no-line"
                                            hide-details="auto"
                                            :items="workRoles"
                                            v-model="team_user.pivot.work_role"
                                            item-value="key"
                                            item-text="translated_name"
                                            :label="$t('teams.work_role')"
                                            :readonly="isViewOnly"
                                        >
                                        </v-select>
                                    </div>
                                    <v-switch
                                        color="green"
                                        v-model="team_user.pivot.is_user_active"
                                        class="ml-3"
                                        :label="
                                            team_user.pivot.is_user_active
                                                ? $t('teams.user_is_active')
                                                : $t('teams.user_is_not_active')
                                        "
                                        :disabled="isViewOnly"
                                    ></v-switch>

                                    <v-btn
                                        v-if="
                                            $store.state.profile.role ===
                                            'admin'
                                        "
                                        icon
                                        color="red"
                                        class="ml-3"
                                        @click="
                                            removeUser(
                                                index,
                                                team_user.pivot.user_id
                                            )
                                        "
                                    >
                                        <v-icon>mdi-delete-outline</v-icon>
                                    </v-btn>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-form>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    v-if="$store.state.profile.role === 'admin'"
                    color="primary"
                    dark
                    @click="submit"
                    >{{ $t("general.save") }}</v-btn
                >
                <v-btn @click.stop="show = false">{{
                    $t("general.cancel")
                }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "TeamForm",
    props: {
        team: Object,
        visible: Boolean,
        isEdited: Boolean,
        users: Array,
        workRoles: Array,
    },

    data() {
        return {
            valid: true,
            showEye: false,
            rules: [(value) => !!value || this.$t("general.required")],
            userRules: [
                (value) =>
                    (this.duplicateErrors[value]
                        ? this.duplicateErrors[value].length <= 1
                        : true) || this.$t("general.user_already_assigned"),
                (value) => !!value || this.$t("general.required"),
            ],

            duplicateErrors: {},
        };
    },

    watch: {
        show(val) {
            !val && this.$refs.form.reset();
        },
    },

    computed: {
        isViewOnly() {
            if (this.$store.state.profile.role === "admin") {
                return false;
            } else {
                return true;
            }
        },
        show: {
            get() {
                return this.visible;
            },
            set(value) {
                if (!value) {
                    this.clearDuplicateErrors();
                    this.$emit("close");
                }
            },
        },
    },

    methods: {
        async submit() {
            this.team.currentUserId = this.$store.state.profile.id;
            if (this.$refs.form.validate()) {
                if (this.isEdited) {
                    await axios
                        .put(`/api/updateTeam/${this.team.id}`, this.team, {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
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
                                this.$emit("updateTeam", response.data);
                                this.$emit(
                                    "snackMessage",
                                    "teams.team_saved",
                                    "success"
                                );
                                this.clearDuplicateErrors();
                                this.$emit("close");
                            }
                        })
                        .catch((error) => {
                            console.log(error);
                            this.$emit(
                                "snackMessage",
                                "general.error",
                                "error"
                            );
                        });
                } else {
                    await axios
                        .post(`/api/storeTeam`, this.team, {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
                            },
                        })
                        .then((response) => {
                            if (response.data.isError) {
                                this.$refs.email.focus();
                                this.$emit(
                                    "snackMessage",
                                    response.data.message,
                                    "warning"
                                );
                            } else {
                                this.$emit("addTeam", response.data);
                                this.$emit(
                                    "snackMessage",
                                    "teams.team_added",
                                    "success"
                                );
                                this.clearDuplicateErrors();
                                this.$emit("close");
                            }
                        })
                        .catch((error) => {
                            console.log(error);
                            this.$emit(
                                "snackMessage",
                                "general.error",
                                "error"
                            );
                        });
                }
            }
        },
        addUser() {
            this.team.team.push({
                pivot: {
                    is_user_active: false,
                    user_id: null,
                    work_role: this.workRoles[0].key,
                },
            });
        },
        removeUser(index, id) {
            this.team.team.splice(index, 1);
            if (this.duplicateErrors[id]) {
                this.duplicateErrors[id].pop();
            }
        },
        checkDuplicate(id) {
            //condition with show avoid execution after closing dialog
            if (this.show) {
                let selectedUsers = [];
                this.team.team.forEach((user) => {
                    selectedUsers.push(user.pivot.user_id);

                    this.duplicateErrors[user.pivot.user_id] =
                        selectedUsers.filter((usr) => {
                            return usr == user.pivot.user_id;
                        });
                });
            }
        },
        clearDuplicateErrors() {
            this.duplicateErrors = JSON.parse(JSON.stringify({}));
        },
    },
};
</script>
