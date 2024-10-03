import Vue from "vue";
import VueI18n from "vue-i18n";

Vue.use(VueI18n);

//if changed, also modify LANGUAGES in config\constants.php
export var languagesAvailable = ["es", "en", "cs"];
export var tagsBCP47 = { es: "es-ES", en: "en-US", cs: "cs-CZ" };

function getDefaultLanguage() {
    let browserLanguage;
    if (!!navigator.language) {
        browserLanguage = navigator.language.slice(0, 2);
    } else {
        browserLanguage = "missing";
    }

    let fallbackLanguage = "en";

    let languageFoundIndex = languagesAvailable.findIndex(
        (lang) => lang == browserLanguage
    );

    let defaultLanguage;
    if (languageFoundIndex > -1) {
        defaultLanguage = languagesAvailable[languageFoundIndex];
    } else {
        defaultLanguage = fallbackLanguage;
    }
    return defaultLanguage;
}

export var defaultLanguage = getDefaultLanguage();
var loadedLanguages = [defaultLanguage];

function loadLocalMessages() {
    let messages = Object;
    let defaultLanguageMessages = require("../../src/locales/backend/" +
        defaultLanguage +
        ".json");
    messages[defaultLanguage] = defaultLanguageMessages;
    return messages;
}

export const i18n = new VueI18n({
    locale: defaultLanguage, //Set locale
    fallbackLocale: "en", //Set fallback locale
    messages: loadLocalMessages(), //Set locale messages
});

function setI18nLanguage(lang) {
    i18n.locale = lang;
    // document.querySelector("html").setAttribute("lang", lang);
    return lang;
}

export function loadLanguageAsync(lang) {
    if (i18n.locale !== lang) {
        if (!loadedLanguages.includes(lang)) {
            return import("../../src/locales/backend/" + lang + ".json").then(
                (msgs) => {
                    i18n.setLocaleMessage(lang, msgs.default);
                    loadedLanguages.push(lang);
                    return setI18nLanguage(lang);
                }
            );
        }
        return Promise.resolve(setI18nLanguage(lang));
    }
    return Promise.resolve(lang);
}

export function setDefaultLanguage() {
    if (i18n.locale != defaultLanguage) {
        setI18nLanguage(defaultLanguage);
    }
}
