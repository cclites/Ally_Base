export default {
    computed: {
        computedPrefix() {
            if (this.localStoragePrefix) {
                return this.localStoragePrefix;
            }
            return 'ally_';
        }
    },

    methods: {
        getLocalStorage(item) {
            try {
                let val = JSON.parse(localStorage.getItem(this.computedPrefix + item));
                if (typeof(val) === 'string') {
                    if (val.toLowerCase() === 'null' || val.toLowerCase() === '') return null;
                    if (val.toLowerCase() === 'false') return false;
                    if (val.toLowerCase() === 'true') return true;
                }
                return val;
            }
            catch (e) {
                console.log(e);
                return null;
            }
        },

        setLocalStorage(item, value) {
            if (typeof(Storage) === "undefined") {
                return;
            }
            localStorage.setItem(this.computedPrefix + item, JSON.stringify(value));
        },
    },
}