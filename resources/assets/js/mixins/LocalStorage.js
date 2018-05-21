export default {
    computed: {
        computedPrefix() {
            if (this.localStoragePrefix) {
                return this.localStoragePrefix;
            }
            return 'ally_';
        }
    },

    mounted() {
        axios.get('/business/settings?json=1')
            .then(response => {
                this.businessSettingsStore = response.data;
            })
            .catch(err => {
                this.businessSettingsStore = {};
            });
    },

    methods: {
        getLocalStorage(item) {
            let val = localStorage.getItem(this.computedPrefix + item);
            if (typeof(val) === 'string') {
                if (val.toLowerCase() === 'null' || val.toLowerCase() === '') return null;
                if (val.toLowerCase() === 'false') return false;
                if (val.toLowerCase() === 'true') return true;
            }
            return val;
        },

        setLocalStorage(item, value) {
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem(this.computedPrefix + item, value);
            }
        },
    },
}