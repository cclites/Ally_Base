export default {
    computed: {
        usingIE() {
            if  (navigator.userAgent.indexOf('MSIE')!==-1
                || navigator.appVersion.indexOf('Trident/') > 0) {
                console.log('Internet Explorer Detected');
                return true;
            }
            return false;
        }
    }
}