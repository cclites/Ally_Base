export default {
    computed: {
        isMobileApp() {
            return window.navigator.userAgent.includes(' AllyMS Mobile ');
        },
    }
}