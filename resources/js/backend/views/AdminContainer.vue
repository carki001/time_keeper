<template>
    <v-app id="app-content">
        <v-navigation-drawer
            :mini-variant="showMenu || isMini"
            app
            clipped
            :permanent="showMenu"
        >
            <v-list>
                <router-link
                    class="d-block text-decoration-none"
                    :to="{ name: 'worktimes' }"
                >
                    <v-tooltip right v-if="showMenu || isMini">
                        <template v-slot:activator="{ on, attrs }">
                            <v-list-item link v-bind="attrs" v-on="on">
                                <div class="ap-list-button">
                                    <v-icon>mdi-alarm-check</v-icon>
                                </div>
                            </v-list-item>
                        </template>
                        <span>{{ $t("title.worktimes") }}</span>
                    </v-tooltip>
                    <v-list-item link v-else>
                        <v-list-item-action>
                            <v-icon>mdi-alarm-check</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                $t("title.worktimes")
                            }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </router-link>
                <router-link
                    class="d-block text-decoration-none"
                    :to="{ name: 'teams' }"
                >
                    <v-tooltip right v-if="showMenu || isMini">
                        <template v-slot:activator="{ on, attrs }">
                            <v-list-item link v-bind="attrs" v-on="on">
                                <div class="ap-list-button">
                                    <v-icon>mdi-account-group</v-icon>
                                </div>
                            </v-list-item>
                        </template>
                        <span>{{ $t("title.teams") }}</span>
                    </v-tooltip>
                    <v-list-item link v-else>
                        <v-list-item-action>
                            <v-icon>mdi-account-group</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                $t("title.teams")
                            }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </router-link>
                <router-link
                    class="d-block text-decoration-none"
                    :to="{ name: 'projects' }"
                >
                    <v-tooltip right v-if="showMenu || isMini">
                        <template v-slot:activator="{ on, attrs }">
                            <v-list-item link v-bind="attrs" v-on="on">
                                <div class="ap-list-button">
                                    <v-icon>mdi-briefcase</v-icon>
                                </div>
                            </v-list-item>
                        </template>
                        <span>{{ $t("title.projects") }}</span>
                    </v-tooltip>
                    <v-list-item link v-else>
                        <v-list-item-action>
                            <v-icon>mdi-briefcase</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                $t("title.projects")
                            }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </router-link>

                <label
                    class="py-2 pl-4 d-block itb-drawer-label mt-3"
                    v-if="$store.state.profile.role === 'admin'"
                >
                    <v-icon v-if="showMenu || isMini">mdi-cog-outline</v-icon>
                    <span v-else>{{ $t("title.settings") }}</span>
                </label>

                <router-link
                    class="d-block text-decoration-none"
                    :to="{ name: 'users' }"
                    v-if="$store.state.profile.role === 'admin'"
                >
                    <v-tooltip right v-if="showMenu || isMini">
                        <template v-slot:activator="{ on, attrs }">
                            <v-list-item link v-bind="attrs" v-on="on">
                                <div class="ap-list-button">
                                    <v-icon>mdi-card-account-details</v-icon>
                                </div>
                            </v-list-item>
                        </template>
                        <span>{{ $t("title.users") }}</span>
                    </v-tooltip>
                    <v-list-item link v-else>
                        <v-list-item-action>
                            <v-icon>mdi-card-account-details</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                $t("title.users")
                            }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </router-link>
                <router-link
                    class="d-block text-decoration-none"
                    :to="{ name: 'settings' }"
                    v-if="$store.state.profile.role === 'admin'"
                >
                    <v-tooltip right v-if="showMenu || isMini">
                        <template v-slot:activator="{ on, attrs }">
                            <v-list-item link v-bind="attrs" v-on="on">
                                <div class="ap-list-button">
                                    <v-icon
                                        >mdi-order-bool-ascending-variant</v-icon
                                    >
                                </div>
                            </v-list-item>
                        </template>
                        <span>{{ $t("title.settings_general") }}</span>
                    </v-tooltip>
                    <v-list-item link v-else>
                        <v-list-item-action>
                            <v-icon>mdi-order-bool-ascending-variant</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                $t("title.settings_general")
                            }}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </router-link>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app clipped-left dark color="primary">
            <v-app-bar-nav-icon @click.stop="switchMini" />
            <v-toolbar-title class="itb-logo">
                <img align="top" src="/images/logo.png" height="25" />
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-tooltip bottom v-if="$store.state.profile.role === 'admin'">
                <template v-slot:activator="{ on, attrs }">
                    <v-icon
                        v-bind="attrs"
                        v-on="on"
                        color="yellow"
                        class="mr-2 itb-icon-tooltip"
                        >mdi-crown-outline</v-icon
                    >
                </template>
                <span>{{ $t("general.wau_you_are_admin") }}</span>
            </v-tooltip>
            {{ $store.state.profile.email }}
            <v-spacer></v-spacer>
            <div>
                <v-menu
                    v-model="isUserInfoVisible"
                    :close-on-content-click="false"
                    :nudge-width="200"
                    offset-x
                >
                    <template
                        v-slot:activator="{ on: onMenu, attrs: attrsMenu }"
                    >
                        <v-tooltip bottom>
                            <template
                                v-slot:activator="{
                                    on: onTooltip,
                                    attrs: attrsTooltip,
                                }"
                            >
                                <v-btn
                                    v-bind="{ ...attrsMenu, ...attrsTooltip }"
                                    v-on="{ ...onMenu, ...onTooltip }"
                                    icon
                                    color="grey darken-1"
                                    class="text-decoration-none"
                                >
                                    <v-avatar color="grey darken-1" size="40">
                                        <img
                                            v-if="$store.state.profile.avatar"
                                            :src="
                                                '/uploads/' +
                                                $store.state.profile.avatar
                                            "
                                            :alt="
                                                $store.state.profile.name +
                                                '-avatar'
                                            "
                                        />
                                        <span
                                            v-else
                                            class="white--text text-body"
                                            >{{ userInitials }}</span
                                        >
                                    </v-avatar>
                                </v-btn>
                            </template>
                            <span>{{ $t("general.account_settings") }}</span>
                        </v-tooltip>
                    </template>

                    <v-card>
                        <v-card-title>
                            {{ $t("title.account_details") }}
                        </v-card-title>
                        <v-card-text>
                            <v-list two-line>
                                <v-list-item>
                                    <v-list-item-action></v-list-item-action>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.name
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.name") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item>
                                    <v-list-item-icon>
                                        <v-icon> mdi-email </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.email
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{
                                                $t("users.email")
                                            }}</v-list-item-subtitle
                                        >
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item>
                                    <v-list-item-action></v-list-item-action>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $t(
                                                "constants.user_role." +
                                                    $store.state.profile.role
                                            )
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.role") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item>
                                    <v-list-item-icon>
                                        <v-icon> mdi-map-clock </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile
                                                .preferred_timezone
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.preferred_timezone") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item
                                    v-if="$store.state.profile.country"
                                >
                                    <v-list-item-action></v-list-item-action>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.country
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.country") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item v-if="$store.state.profile.city">
                                    <v-list-item-icon>
                                        <v-icon> mdi-city </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.city
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.city") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item
                                    v-if="$store.state.profile.postalcode"
                                >
                                    <v-list-item-icon>
                                        <v-icon> mdi-mailbox </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.postalcode
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.postalcode") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item
                                    v-if="$store.state.profile.address"
                                >
                                    <v-list-item-icon>
                                        <v-icon> mdi-map-marker </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.address
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.address") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item v-if="$store.state.profile.phone">
                                    <v-list-item-icon>
                                        <v-icon> mdi-phone </v-icon>
                                    </v-list-item-icon>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.phone
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.phone") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item
                                    v-if="$store.state.profile.company"
                                >
                                    <v-list-item-action></v-list-item-action>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.company
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.company") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>

                                <v-list-item v-if="$store.state.profile.vat">
                                    <v-list-item-action></v-list-item-action>

                                    <v-list-item-content>
                                        <v-list-item-title>{{
                                            $store.state.profile.vat
                                        }}</v-list-item-title>
                                        <v-list-item-subtitle>
                                            {{ $t("users.vat") }}
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-card-text>
                        <v-card-actions class="d-flex justify-end">
                            <v-btn color="success" @click="editUser">{{
                                $t("general.edit")
                            }}</v-btn>
                            <v-btn @click="isUserInfoVisible = false">{{
                                $t("general.cancel")
                            }}</v-btn>
                        </v-card-actions>
                    </v-card></v-menu
                >
            </div>
            <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn
                        icon
                        v-bind="attrs"
                        v-on="on"
                        @click="logout"
                        color="deep-orange accent-3"
                    >
                        <v-icon>mdi-logout-variant</v-icon>
                    </v-btn>
                </template>
                <span>{{ $t("general.sign_out") }}</span>
            </v-tooltip>
        </v-app-bar>

        <v-main class="blue lighten-5">
            <router-view></router-view>
        </v-main>

        <v-footer app color="white">
            <span>
                {{
                    new Date().getFullYear() != 2022
                        ? "&copy; 2022-" +
                          String(new Date().getFullYear()) +
                          " "
                        : "&copy; 2022" + ""
                }}
                <a href="https://www.google.com" target="_blank">company.inc</a>
            </span>
        </v-footer>
        <OwnUserForm
            :user="selectedUser"
            :timezones="timezones"
            :languages="languages"
            :visible="dialog.ownUserForm"
            @updateUser="updateUser"
            @close="closeUserDialog"
            @snackMessage="snackMessage"
        />
        <SnackMessage ref="SnackMessage" />
    </v-app>
</template>

<script>
import OwnUserForm from "../components/OwnUserForm";
import SnackMessage from "../components/SnackMessage";
import { loadLanguageAsync, setDefaultLanguage } from "../../i18n";
export default {
    data: () => ({
        isMini: false,
        isUserInfoVisible: false,
        dialog: {
            ownUserForm: false,
        },

        timezones: [],
        selectedUser: {},
        languages: [],
        isMobile: false,
        showMenu: false,
    }),

    computed: {
        userInitials() {
            let name = this.$store.state.profile.name;
            let nameSlug = name
                .toString() // Cast to string (optional)
                .normalize("NFKD") // The normalize() using NFKD method returns the Unicode Normalization Form of a given string.
                .toUpperCase() // Convert the string to lowercase letters
                .trim() // Remove whitespace from both sides of a string (optional)
                .replace(/\s+/g, "-") // Replace spaces with -
                .replace(/_/g, "-") // Replace _ with -
                .replace(/[^\w\-]+/g, "") // Remove all non-word chars
                .replace(/\-\-+/g, "-"); // Replace multiple - with single -
            let nameArray = nameSlug.split("-");
            var initials = "";
            if (nameArray.length > 1) {
                initials =
                    nameArray.shift().slice(0, 1) + nameArray.pop().slice(0, 1);
            } else {
                initials = nameArray.shift().slice(0, 2);
            }
            return initials;
        },
    },

    components: {
        OwnUserForm,
        SnackMessage,
    },

    created() {
        this.getTimezones();
        this.getLanguages();
        this.setUserlanguage(this.$store.state.profile.preferred_language);

        this.onResize();
        window.addEventListener("resize", this.onResize);
    },

    beforeDestroy() {
        window.removeEventListener("resize", this.onResize);
    },

    methods: {
        snackMessage(msg, type) {
            this.$refs.SnackMessage.showMessage(msg, type);
        },
        logout() {
            axios
                .get("/api/auth/logout", {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then(() => {
                    localStorage.removeItem("itb-spa-token");
                    this.$store.dispatch("resetState");
                    this.$router.push("/login");
                });
        },
        editUser() {
            var newObject = JSON.stringify(this.$store.state.profile);
            this.selectedUser = JSON.parse(newObject);
            this.dialog.ownUserForm = true;
            this.isUserInfoVisible = false;
        },
        updateUser(user) {
            if (
                this.$store.state.profile.preferred_language !=
                user.preferred_language
            ) {
                this.setUserlanguage(user.preferred_language);
            }

            this.$store.dispatch("updateUser", user);
            this.getLanguages();
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
        closeUserDialog() {
            this.dialog.ownUserForm = false;
            this.selectedUser = JSON.parse(JSON.stringify({}));
        },
        setUserlanguage(lang) {
            if (!!lang) {
                loadLanguageAsync(lang);
            } else {
                setDefaultLanguage();
            }
        },
        async getLanguages() {
            await axios
                .get("/getLanguages")
                .then((response) => {
                    this.languages = response.data;
                    this.languages.forEach((language) => {
                        language.translated_name = this.$t(
                            language.name_for_translation
                        );
                    });
                })
                .catch((error) => {
                    console.log(error);
                    this.snackMessage("general.error", "error");
                });
        },
        switchMini() {
            this.isMini = !this.isMini;
            if (this.isMobile) {
                this.showMenu = !this.showMenu;
            } else {
                this.showMenu = false;
            }
        },
        onResize() {
            if (this.$vuetify.breakpoint.mobile) {
                this.isMobile = true;
            } else {
                this.isMobile = false;
                this.showMenu = false;
            }
        },
    },
};
</script>
