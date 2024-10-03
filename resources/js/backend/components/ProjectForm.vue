<template>
    <v-dialog v-model="show" max-width="600px">
        <v-card>
            <v-card-title>
                <span class="headline" v-if="isEdited && !isViewOnly">{{
                    $t("projects.edit_project")
                }}</span>
                <span class="headline" v-if="!isEdited && !isViewOnly">{{
                    $t("projects.add_project")
                }}</span>
                <span class="headline" v-if="isViewOnly">{{
                    $t("projects.view_project")
                }}</span>
            </v-card-title>

            <v-card-text>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <v-container>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="project.name"
                                    :label="$t('projects.name')"
                                    autofocus
                                    :rules="rules"
                                    :readonly="isViewOnly"
                                ></v-text-field> </v-col
                            ><v-col cols="12" md="6">
                                <v-autocomplete
                                    :items="teams"
                                    v-model="project.team_id"
                                    item-value="id"
                                    item-text="name"
                                    :rules="rules"
                                    :label="$t('projects.teams')"
                                    :readonly="isViewOnly"
                                ></v-autocomplete
                            ></v-col>
                        </v-row>
                        <v-row>
                            <v-switch
                                color="green"
                                v-model="project.is_active"
                                :label="
                                    project.is_active
                                        ? $t('projects.project_is_active')
                                        : $t('projects.project_is_not_active')
                                "
                                :readonly="isViewOnly"
                            ></v-switch>
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
    name: "ProjectForm",
    props: {
        project: Object,
        visible: Boolean,
        isEdited: Boolean,
        teams: Array,
    },

    data() {
        return {
            valid: true,
            showEye: false,
            rules: [(value) => !!value || this.$t("general.required")],
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
                    this.$emit("close");
                }
            },
        },
    },

    methods: {
        async submit() {
            this.project.currentUserId = this.$store.state.profile.id;
            if (this.$refs.form.validate()) {
                if (this.isEdited) {
                    await axios
                        .put(
                            `/api/updateProject/${this.project.id}`,
                            this.project,
                            {
                                headers: {
                                    Authorization:
                                        "Bearer " +
                                        this.$store.state.tokenData.user
                                            .access_token,
                                },
                            }
                        )
                        .then((response) => {
                            if (response.data.isError) {
                                this.$emit(
                                    "snackMessage",
                                    response.data.message,
                                    "warning"
                                );
                                this.$refs.email.focus();
                            } else {
                                this.$emit("updateProject", response.data);
                                this.$emit(
                                    "snackMessage",
                                    "projects.project_saved",
                                    "success"
                                );

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
                        .post(`/api/storeProject`, this.project, {
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
                                this.$emit("addProject", response.data);
                                this.$emit(
                                    "snackMessage",
                                    "projects.project_added",
                                    "success"
                                );

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
    },
};
</script>
