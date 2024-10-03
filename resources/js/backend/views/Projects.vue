<template>
    <div>
        <header>
            <v-container>
                <v-row no-gutters>
                    <h1 class="font-weight-light headline">
                        {{ $t("title.projects") }}
                    </h1>
                    <v-spacer></v-spacer>
                    <v-lazy
                        v-if="isProjectsLoaded"
                        transition="fade-transition"
                    >
                        <v-text-field
                            v-model="search"
                            append-icon="mdi-magnify"
                            :label="searchLabel"
                            single-line
                            dense
                            hide-details
                            style="max-width: 200px"
                            class="mr-5 pr-5"
                        ></v-text-field>
                    </v-lazy>
                    <v-lazy
                        v-if="
                            isProjectsLoaded &&
                            $store.state.profile.role === 'admin'
                        "
                        transition="fade-transition"
                    >
                        <v-btn
                            color="success"
                            dark
                            small
                            @click.stop="prepareProject"
                            >{{ $t("general.add") }}</v-btn
                        >
                    </v-lazy>
                </v-row>
            </v-container>
        </header>
        <section>
            <v-container class="pt-0">
                <v-sheet class="px-3 pt-3 pb-3" v-if="!projects.length">
                    <v-skeleton-loader
                        class="mx-auto"
                        type="table"
                    ></v-skeleton-loader>
                </v-sheet>
                <v-card v-else>
                    <v-data-table
                        :headers="headers"
                        :items="projects"
                        :search="search"
                        :items-per-page="15"
                        dense
                    >
                        <template v-slot:item.action="{ item }">
                            <div style="display: flex">
                                <v-tooltip
                                    v-if="$store.state.profile.role === 'admin'"
                                    top
                                >
                                    <template v-slot:activator="{ on }">
                                        <v-icon
                                            v-tr-class-on-hover="'blue'"
                                            size="20px"
                                            color="blue"
                                            class="mr-2"
                                            @click="editProject(item)"
                                            v-on="on"
                                            >mdi-pencil-outline</v-icon
                                        >
                                    </template>
                                    <span>{{ $t("general.edit") }}</span>
                                </v-tooltip>
                                <v-tooltip
                                    v-if="$store.state.profile.role !== 'admin'"
                                    top
                                >
                                    <template v-slot:activator="{ on }">
                                        <v-icon
                                            v-tr-class-on-hover="'blue'"
                                            size="20px"
                                            color="blue"
                                            class="mr-2"
                                            @click="editProject(item)"
                                            v-on="on"
                                            >mdi-eye-outline</v-icon
                                        >
                                    </template>
                                    <span>{{ $t("general.view") }}</span>
                                </v-tooltip>
                                <v-tooltip
                                    v-if="$store.state.profile.role === 'admin'"
                                    top
                                >
                                    <template v-slot:activator="{ on }">
                                        <v-icon
                                            size="22px"
                                            color="red"
                                            v-tr-class-on-hover="'red'"
                                            class="mr-2"
                                            @click="confirmDelete(item)"
                                            v-on="on"
                                            >mdi-delete-outline</v-icon
                                        >
                                    </template>
                                    <span>{{ $t("general.delete") }}</span>
                                </v-tooltip>
                                <span class="ps-3" v-else>-</span>
                            </div>
                        </template>
                        <template v-slot:item.is_active="{ item }">
                            <v-switch
                                dense
                                :hide-details="true"
                                color="success"
                                v-model="item.is_active"
                                class="my-0 py-0 itb-project-switch"
                                @change="changeActivationStatus(item)"
                                :label="
                                    item.is_active
                                        ? $t('general.yes')
                                        : $t('general.no')
                                "
                            ></v-switch>
                        </template>
                    </v-data-table>
                </v-card>

                <ProjectForm
                    :project="selectedProject"
                    :visible="dialog.projectForm"
                    :isEdited="isProjectEdited"
                    :teams="teams"
                    @addProject="addProject"
                    @updateProject="updateProject"
                    @close="dialog.projectForm = false"
                    @snackMessage="snackMessage"
                />

                <ConfirmationDialog
                    :name="selectedProject.name"
                    :text="'projects.delete_confirmation'"
                    v-if="dialog.confirmation"
                    @confirm="deleteProject"
                    @cancel="cancelDelete"
                />

                <SnackMessage ref="SnackMessage" />
            </v-container>
        </section>
    </div>
</template>

<script>
import ProjectForm from "../components/ProjectForm";
import ConfirmationDialog from "../components/ConfirmationDialog";
import SnackMessage from "../components/SnackMessage";

export default {
    data() {
        return {
            projects: [],
            teams: [],
            isProjectsLoaded: false,
            search: "",
            dialog: {
                confirmation: false,
                projectForm: false,
            },

            selectedProject: {
                name: null,
                team_id: null,
                is_active: false,
            },
            defaultProject: {
                name: null,
                team_id: null,
                is_active: false,
            },
            isProjectEdited: false,
            sessionT: null,
        };
    },

    components: {
        ProjectForm,
        ConfirmationDialog,
        SnackMessage,
    },

    computed: {
        headers() {
            return [
                {
                    text: this.$t("general.actions"),
                    value: "action",
                    sortable: false,
                },
                {
                    text: this.$t("projects.name"),
                    align: "left",
                    sortable: false,
                    value: "name",
                },
                {
                    text: this.$t("projects.is_active"),
                    align: "left",
                    sortable: false,
                    value: "is_active",
                },
                {
                    text: this.$t("projects.team_name"),
                    align: "left",
                    sortable: false,
                    value: "team_name",
                },
                {
                    text: this.$t("general.created_at"),
                    align: "left",
                    sortable: false,
                    value: "formattedDatetime",
                },
            ];
        },
        searchLabel() {
            return this.$t("general.search");
        },
    },

    created() {
        this.getProjects();
    },

    mounted() {
        this.$eventBus.$on("refreshCurrentUser", (data) => {
            this.getProjects();
        });
    },

    methods: {
        snackMessage(msg, type) {
            this.$refs.SnackMessage.showMessage(msg, type);
        },

        async getProjects() {
            await axios
                .get("/api/projectList/" + this.$store.state.profile.id, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.projects = response.data.projects
                        ? response.data.projects
                        : [];
                    this.teams = response.data.teams ? response.data.teams : [];
                    this.sessionT = response.data.sessionT;
                    this.isProjectsLoaded = true;
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
        },

        prepareProject() {
            var newObject = JSON.stringify(this.defaultProject);
            this.selectedProject = JSON.parse(newObject);

            this.dialog.projectForm = true;
            this.isProjectEdited = false;
        },
        addProject(project) {
            this.projects.push(project);
        },
        editProject(item) {
            this.selectedProject = JSON.parse(JSON.stringify(item));
            this.dialog.projectForm = true;
            this.isProjectEdited = true;
        },
        updateProject(project) {
            let index = this.projects.findIndex(
                (item) => item.id == project.id
            );
            this.projects.splice(index, 1, project);
        },
        confirmDelete(item) {
            this.selectedProject = item;
            this.dialog.confirmation = true;
        },
        cancelDelete() {
            this.dialog.confirmation = false;
        },
        async deleteProject() {
            this.dialog.confirmation = false;
            let index = this.projects.indexOf(this.selectedProject);

            await axios
                .delete(`/api/deleteProject/${this.selectedProject.id}`, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    var newObject = JSON.stringify(this.defaultProject);
                    this.selectedProject = JSON.parse(newObject);

                    this.projects.splice(index, 1);

                    this.$refs.SnackMessage.showMessage(
                        "projects.deleted_successfully",
                        "success"
                    );
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
        changeActivationStatus(project) {
            axios
                .get(
                    "/api/changeProjectActivationStatus/" +
                        project.id +
                        "/" +
                        Number(project.is_active),
                    {
                        headers: {
                            Authorization:
                                "Bearer " +
                                this.$store.state.tokenData.user.access_token,
                        },
                    }
                )
                .then((response) => {
                    this.$refs.SnackMessage.showMessage(
                        "projects.project_saved",
                        "success"
                    );
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
    },
};
</script>
