<template>
    <div>
        <header>
            <v-container>
                <v-row no-gutters>
                    <h1 class="font-weight-light headline">
                        {{ $t("title.teams") }}
                    </h1>
                    <v-spacer></v-spacer>
                    <v-lazy v-if="isTeamsLoaded" transition="fade-transition">
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
                            isTeamsLoaded &&
                            $store.state.profile.role === 'admin'
                        "
                        transition="fade-transition"
                    >
                        <v-btn
                            color="success"
                            dark
                            small
                            @click.stop="prepareTeam"
                            >{{ $t("general.add") }}</v-btn
                        >
                    </v-lazy>
                </v-row>
            </v-container>
        </header>
        <section>
            <v-container class="pt-0">
                <v-sheet class="px-3 pt-3 pb-3" v-if="!teams.length">
                    <v-skeleton-loader
                        class="mx-auto"
                        type="table"
                    ></v-skeleton-loader>
                </v-sheet>
                <v-card v-else>
                    <v-data-table
                        :headers="headers"
                        :items="teams"
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
                                            @click="editTeam(item)"
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
                                            @click="editTeam(item)"
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
                    </v-data-table>
                </v-card>

                <TeamForm
                    :team="selectedTeam"
                    :visible="dialog.teamForm"
                    :isEdited="isTeamEdited"
                    :users="users"
                    :workRoles="workRoles"
                    @addTeam="addTeam"
                    @updateTeam="updateTeam"
                    @close="dialog.teamForm = false"
                    @snackMessage="snackMessage"
                />

                <ConfirmationDialog
                    :name="selectedTeam.name"
                    :text="'teams.delete_confirmation'"
                    v-if="dialog.confirmation"
                    @confirm="deleteTeam"
                    @cancel="cancelDelete"
                />

                <SnackMessage ref="SnackMessage" />
            </v-container>
        </section>
    </div>
</template>

<script>
import TeamForm from "../components/TeamForm";
import ConfirmationDialog from "../components/ConfirmationDialog";
import SnackMessage from "../components/SnackMessage";

export default {
    data() {
        return {
            teams: [],
            users: [],
            isTeamsLoaded: false,
            search: "",
            dialog: {
                confirmation: false,
                teamForm: false,
            },

            selectedTeam: {
                name: null,
                team: [],
            },
            defaultTeam: {
                name: null,
                team: [],
            },
            isTeamEdited: false,
            workRoles: [],
        };
    },

    components: {
        TeamForm,
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
                    text: this.$t("teams.name"),
                    align: "left",
                    sortable: false,
                    value: "name",
                },
                {
                    text: this.$t("teams.first_leader"),
                    align: "left",
                    sortable: false,
                    value: "first_leader",
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
        this.getTeams();
    },

    mounted() {
        this.$eventBus.$on("refreshCurrentUser", (data) => {
            this.getTeams();
        });
    },

    methods: {
        snackMessage(msg, type) {
            this.$refs.SnackMessage.showMessage(msg, type);
        },

        async getTeams() {
            await axios
                .get("/api/teamList/" + this.$store.state.profile.id, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.teams = response.data.teams ? response.data.teams : [];
                    this.users = response.data.users ? response.data.users : [];
                    this.workRoles = response.data.workRoles;
                    this.workRoles.forEach((role) => {
                        role.translated_name = this.$t(
                            "constants." + role.name
                        );
                    });
                    this.isTeamsLoaded = true;
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
        },

        prepareTeam() {
            var newObject = JSON.stringify(this.defaultTeam);
            this.selectedTeam = JSON.parse(newObject);

            this.dialog.teamForm = true;
            this.isTeamEdited = false;
        },
        addTeam(team) {
            this.teams.push(team);
        },
        editTeam(item) {
            this.selectedTeam = JSON.parse(JSON.stringify(item));
            this.dialog.teamForm = true;
            this.isTeamEdited = true;
        },
        updateTeam(team) {
            let index = this.teams.findIndex((item) => item.id == team.id);
            this.teams.splice(index, 1, team);
        },
        confirmDelete(item) {
            this.selectedTeam = item;
            this.dialog.confirmation = true;
        },
        cancelDelete() {
            this.dialog.confirmation = false;
        },
        async deleteTeam() {
            this.dialog.confirmation = false;
            let index = this.teams.indexOf(this.selectedTeam);

            await axios
                .delete(`/api/deleteTeam/${this.selectedTeam.id}`, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    var newObject = JSON.stringify(this.defaultTeam);
                    this.selectedTeam = JSON.parse(newObject);

                    this.teams.splice(index, 1);

                    this.$refs.SnackMessage.showMessage(
                        "teams.deleted_successfully",
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
