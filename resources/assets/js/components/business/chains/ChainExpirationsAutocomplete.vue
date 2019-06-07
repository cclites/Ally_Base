<template>
    <b-select name="chain_expirations"
              id="chain_expirations"
              v-model="chain_expiration"
              :disabled="loading"
              v-if="this.chain_expirations.length"
              @change="emitSelected($event)">
        <option v-for="item in chain_expirations" :key="item.id" :value="item.type">{{ item.type }}</option>
    </b-select>
</template>

<script>
    export default {

        props: {
            value: {
                type: [String, String],

                default: function () {
                    return "0";
                },
            },
            showInactive: {
                type: Boolean,
                required: false,
                default: false,
            },

            caregiverId: {},
            selectedItem: {},
            //tempItems: [],

            filterBy: {
                type: String,
                default: '',
            },
        },

        data() {
            return {
                chain_expirations: [],
                chain_expiration: {},
                loading: false,
                selected_type: '',
                all_expirations_types: [],
                doFilter: false,
            }
        },

        methods: {
            async fetchChainExpirations() {

                let businessId = this.officeUserSettings.default_business_id;
                let url='/business/chains/chain-expirations/' + this.caregiverId + '?business_id=' + businessId;

                await axios.get(url)
                    .then( ({ data }) => {
                        this.all_expirations_types = data;
                    })
                    .catch(e => {});
            },
            emitSelected(value){

                this.chain_expirations = [];
                this.selected_type = '';
                this.doFilter = false;

                if(value){
                    this.$emit('updateSelectedItem', value);
                }
            },
        },

        watch:{
            filterBy(){

                if(this.doFilter){
                    const typeObj = this.all_expirations_types;
                    let filter = this.filterBy;

                    this.chain_expirations = [];

                    if(filter.length > 2){

                        for (let [key, value] of Object.entries(typeObj)) {
                            //console.log(`${key}: ${value.type}`);

                            if(value.type.startsWith(filter) && value.type !== this.selectedItem.name){
                                console.log(value.type);
                                this.chain_expirations.push(value);
                            }

                        }
                    }
                }else{
                    this.doFilter=true;
                }





            }

        },


        async mounted() {
            this.loading = true;
            await this.fetchChainExpirations();
            this.chain_expiration = this.value;
            this.loading = false;
        },
    }
</script>

<style scoped>

</style>