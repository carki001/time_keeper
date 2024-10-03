import "./bootstrap";
import "@mdi/font/css/materialdesignicons.css";
import "vuetify/dist/vuetify.min.css";
import { i18n } from "./i18n";
// import cs from "../../src/locales/backend/cs.json";

import Vue from "vue";
import Vuex from "vuex";
import Vuetify from "vuetify";
import VueRouter from "vue-router";
import VueI18n from "vue-i18n";
import Jwt from "jsonwebtoken";

window.Vue = Vue;

Vue.use(Vuex);
Vue.use(VueRouter);
Vue.use(VueI18n);
Vue.use(Vuetify);

const getDefaultState = () => {
    var token = localStorage.getItem("itb-spa-token");
    var tokenData = token ? Jwt.decode(token) : null;
    var updatedProfile = JSON.parse(localStorage.getItem("updatedProfile"));

    if (!updatedProfile) {
        return {
            isLoggedIn: false,
            tokenData: tokenData,
            profile: token ? tokenData.user.user : {},
        };
    } else {
        return {
            isLoggedIn: false,
            tokenData: tokenData,
            profile: JSON.parse(JSON.stringify(updatedProfile)),
        };
    }
};

const store = new Vuex.Store({
    state: getDefaultState(),
    mutations: {
        authenticate(state, payload) {
            state.isLoggedIn = localStorage.getItem("itb-spa-token")
                ? true
                : false;

            if (state.isLoggedIn) {
                state.profile = payload;
                let token = localStorage.getItem("itb-spa-token");
                state.tokenData = token ? Jwt.decode(token) : null;
            } else {
                state.profile = {};
            }
        },
        resetState(state) {
            state.isLoggedIn = false;
            state.tokenData = null;
            state.profile = {};
            localStorage.removeItem("updatedProfile");
        },
        updateUser(state, payload) {
            state.profile = JSON.parse(JSON.stringify(payload));
            localStorage.setItem(
                "updatedProfile",
                JSON.stringify(state.profile)
            );
        },
    },
    actions: {
        authenticate(context, payload) {
            context.commit("authenticate", payload);
        },
        resetState(context) {
            context.commit("resetState");
        },

        updateUser(context, payload) {
            context.commit("updateUser", payload);
        },
    },
});

import AppContainer from "./backend/views/AppContainer.vue";

Vue.component("app-container", AppContainer);

import Login from "./backend/views/Login.vue";
import ResetPassword from "./backend/views/ResetPassword.vue";
import AdminContainer from "./backend/views/AdminContainer.vue";
import Users from "./backend/views/Users.vue";
import Settings from "./backend/views/Settings.vue";
import Teams from "./backend/views/Teams.vue";
import Projects from "./backend/views/Projects.vue";
import Worktimes from "./backend/views/Worktimes.vue";

const routes = [
    { path: "/login", name: "login", component: Login },
    {
        path: "/reset-password",
        name: "reset-password",
        component: ResetPassword,
    },
    {
        path: "/admin",
        component: AdminContainer,
        children: [
            { path: "", redirect: "worktimes" },
            {
                path: "teams",
                name: "teams",
                component: Teams,
            },
            {
                path: "projects",
                name: "projects",
                component: Projects,
            },
            {
                path: "worktimes",
                name: "worktimes",
                component: Worktimes,
            },
            {
                path: "users",
                name: "users",
                component: Users,
                beforeEnter(to, from, next) {
                    const token = localStorage.getItem("itb-spa-token");

                    if (!token) {
                        return;
                    }
                    const tokenData = Jwt.decode(token);

                    if (tokenData.user.user.role === "admin") {
                        next();
                    } else {
                        next("/404");
                    }
                },
            },

            {
                path: "settings",
                name: "settings",
                component: Settings,
                beforeEnter(to, from, next) {
                    const token = localStorage.getItem("itb-spa-token");

                    if (!token) {
                        return;
                    }
                    const tokenData = Jwt.decode(token);

                    if (tokenData.user.user.role === "admin") {
                        next();
                    } else {
                        next("/404");
                    }
                },
            },
        ],
        beforeEnter(to, from, next) {
            const token = localStorage.getItem("itb-spa-token");
            if (token != null) {
                next();
            } else {
                next("/login");
            }
        },
    },
];

const router = new VueRouter({
    mode: "history",
    routes,
});

const opts = {
    lang: {
        t: (key, ...params) => i18n.t(key, params),
    },
};

export default new Vuetify(opts);

// Directives
Vue.directive("tr-class-on-hover", {
    bind(el, binding) {
        const { value = "" } = binding;
        el.addEventListener("mouseenter", () => {
            el.closest("tr").classList.add("actions-hover-" + value);
        });
        el.addEventListener("mouseleave", () => {
            el.closest("tr").classList.remove("actions-hover-" + value);
        });
    },
});

// Allows calling methods from other component with no parent-child relationship
Vue.prototype.$eventBus = new Vue();

const app = new Vue({
    el: "#app",
    vuetify: new Vuetify(),
    i18n,
    router,
    store,
});
