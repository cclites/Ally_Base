import Vue from 'vue';

Vue.mixin({
    computed: {
        authUser() {
            return window.AuthUser;
        },

        authRole() {
            return window.AuthUser ? window.AuthUser.role_type : null;
        },

        authInactive() {
            return window.AuthUser && window.AuthUser.active == 0;
        },

        authActive() {
            return window.AuthUser && window.AuthUser.active == 1;
        },

        isLoggedIn() {
            return window.AuthUser && window.AuthUser.id ? true : false;
        },

        officeUserSettings() {
            return window.OfficeUserSettings || {};
        },

        isOfficeUser() {
            return ['office_user'].includes(window.AuthUser.role_type);
        },

        isOfficeUserOrAdmin() {
            return ['office_user', 'admin'].includes(window.AuthUser.role_type);
        },

        isAdmin() {
            return window.AuthUser.role_type === "admin"
            || (window.AuthUser.impersonator && window.AuthUser.impersonator.role_type === "admin")
        },
    }
})
