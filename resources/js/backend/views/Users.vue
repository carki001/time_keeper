<template>
    <div>
        <header>
            <v-container>
                <v-row no-gutters>
                    <h1 class="font-weight-light headline">
                        {{ $t("title.users") }}
                    </h1>
                    <v-spacer></v-spacer>
                    <v-lazy v-if="isUsersLoaded" transition="fade-transition">
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
                    <v-lazy v-if="isUsersLoaded" transition="fade-transition">
                        <v-btn
                            color="success"
                            dark
                            small
                            @click.stop="prepareUser"
                            >{{ $t("general.add") }}</v-btn
                        >
                    </v-lazy>
                </v-row>
            </v-container>
        </header>
        <section>
            <v-container class="pt-0">
                <v-sheet class="px-3 pt-3 pb-3" v-if="!users.length">
                    <v-skeleton-loader
                        class="mx-auto"
                        type="table"
                    ></v-skeleton-loader>
                </v-sheet>
                <v-card v-else>
                    <v-data-table
                        :headers="headers"
                        :items="users"
                        :search="search"
                        :items-per-page="15"
                        dense
                    >
                        <template v-slot:item.action="{ item }">
                            <div style="display: flex">
                                <v-tooltip top>
                                    <template v-slot:activator="{ on }">
                                        <v-icon
                                            v-tr-class-on-hover="'blue'"
                                            size="20px"
                                            color="blue"
                                            class="mr-2"
                                            @click="editUser(item)"
                                            v-on="on"
                                            >mdi-pencil-outline</v-icon
                                        >
                                    </template>
                                    <span>{{ $t("general.edit") }}</span>
                                </v-tooltip>
                                <v-tooltip top>
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
                            </div>
                        </template>
                        <template v-slot:item.role="{ item }">
                            <span>{{
                                $t("constants.user_role." + item.role)
                            }}</span>
                        </template>
                    </v-data-table>
                </v-card>

                <UserForm
                    :user="selectedUser"
                    :userRoles="userRoles"
                    :timezones="timezones"
                    :visible="dialog.userForm"
                    :isEdited="isUserEdited"
                    @addUser="addUser"
                    @updateUser="updateUser"
                    @close="dialog.userForm = false"
                    @snackMessage="snackMessage"
                />

                <ConfirmationDialog
                    :name="selectedUser.name"
                    :text="'users.delete_confirmation'"
                    v-if="dialog.confirmation"
                    @confirm="deleteUser"
                    @cancel="cancelDelete"
                />

                <SnackMessage ref="SnackMessage" />
            </v-container>
        </section>
    </div>
</template>

<script>
import UserForm from "../components/UserForm";
import ConfirmationDialog from "../components/ConfirmationDialog";
import SnackMessage from "../components/SnackMessage";

export default {
    data() {
        return {
            users: [],
            isUsersLoaded: false,
            search: "",
            dialog: {
                confirmation: false,
                userForm: false,
            },

            selectedUser: {
                name: null,
                email: null,
                password: null,
                role: null,
                preferred_timezone: null,
            },
            defaultUser: {
                name: null,
                email: null,
                password: null,
                role: "user",
                preferred_timezone: "UTC",
            },
            isUserEdited: false,
            userRoles: [],
            timezones: [],
        };
    },

    components: {
        UserForm,
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
                    text: this.$t("users.name"),
                    align: "left",
                    sortable: false,
                    value: "name",
                },
                {
                    text: this.$t("users.email"),
                    align: "left",
                    sortable: false,
                    value: "email",
                },
                {
                    text: this.$t("users.role"),
                    align: "left",
                    sortable: false,
                    value: "role",
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
        this.getUserRoles();
        this.getUsers();
        this.getTimezones();
    },

    mounted() {
        this.$eventBus.$on("refreshCurrentUser", (data) => {
            this.getUsers();
        });
    },

    methods: {
        snackMessage(msg, type) {
            this.$refs.SnackMessage.showMessage(msg, type);
        },
        async getUserRoles() {
            await axios
                .get("/api/userRoles", {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.userRoles = response.data ? response.data : [];
                    this.userRoles.forEach((role) => {
                        role.translated_name = this.$t(
                            "constants." + role.name
                        );
                    });
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
        },
        async getUsers() {
            await axios
                .get("/api/userList/" + this.$store.state.profile.id, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.users = response.data;
                    this.isUsersLoaded = true;
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
        },

        prepareUser() {
            var newObject = JSON.stringify(this.defaultUser);
            this.selectedUser = JSON.parse(newObject);

            this.dialog.userForm = true;
            this.isUserEdited = false;
        },
        addUser(user) {
            this.users.push(user);
        },
        editUser(item) {
            this.selectedUser = Object.assign({}, item);
            this.dialog.userForm = true;
            this.isUserEdited = true;
        },
        updateUser(user) {
            let index = this.users.findIndex((item) => item.id == user.id);
            this.users.splice(index, 1, user);
            if (user.id == this.$store.state.profile.id) {
                this.$store.dispatch("updateUser", user);
            }
        },
        confirmDelete(item) {
            this.selectedUser = item;
            this.dialog.confirmation = true;
        },
        cancelDelete() {
            this.dialog.confirmation = false;
        },
        async deleteUser() {
            this.dialog.confirmation = false;
            let index = this.users.indexOf(this.selectedUser);

            await axios
                .delete(`/api/deleteUser/${this.selectedUser.id}`, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    var newObject = JSON.stringify(this.defaultUser);
                    this.selectedUser = JSON.parse(newObject);

                    this.users.splice(index, 1);

                    this.$refs.SnackMessage.showMessage(
                        "users.deleted_successfully",
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
        async getTimezones() {
            await axios
                .get("/api/getTimezones", {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.timezones = response.data ? response.data : [];
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
        },
    },
};
</script>
