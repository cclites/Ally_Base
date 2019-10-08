import store from '../store/index';

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
                    if (val.toLowerCase() === 'null') return null;
                    if (val.toLowerCase() === 'false') return false;
                    if (val.toLowerCase() === 'true') return true;
                }

                if( item == 'business_id' && Object.values( store.getters.getBusiness( val ) ).length == 0 ){
                    // local storage saved a business id that we do not have as this user..
                    // this can be extended for more values other than business id in the future

                    // clear the storage value
                    this.setLocalStorage( item, null );

                    // return null
                    return null;
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