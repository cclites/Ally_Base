<template>
    <div class="autocomplete">
        <b-form-input
                id="searchInput"
                name="searchInput"
                v-model="filterBy"
        >
        </b-form-input>
        <div class="form-control"
             :class="[this.isVisible ? 'showBlock' : 'hideBlock']"
             name="chain_expirations"
             id="chain_expirations"
             v-model="chain_expirations"
        >
            <div class="row"
                 v-for="item in chain_expirations"
                 @click="updateSelected(item.type)"
            >
                    {{ item.type }}
            </div>
        </div>
    </div>
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
        },

        data() {
            return {
                chain_expirations: [],
                loading: false,
                selected_type: '',
                all_expirations_types: [],
                searchInput: '',
                filterBy: '',
                isVisible: false,
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

            updateSelected(filter){
                this.filterBy = filter;
                this.$emit('updateSelectedType', filter);
                document.getElementById('chain_expirations').innerHTML='';
                this.chain_expirations=[];
                if(this.chain_expirations.length > 0){
                    this.isVisible = true;
                }else{
                    this.isVisible = false;
                }
            },

            updateFilterBy(){

                const typeObj = this.all_expirations_types;
                let filter = this.filterBy;
                this.$emit('updateSelectedType', filter);

                this.chain_expirations = [];
                this.isVisible = false;

                if(filter.length > 2){

                    for (let [key, value] of Object.entries(typeObj)) {
                        if(value.type.startsWith(filter) && value.type !== this.filterBy){
                            this.chain_expirations.push(value);
                        }
                    }
                }

                if(this.chain_expirations.length > 0){
                    this.isVisible = true;
                }else{
                    this.isVisible = false;
                }
            }
        },

        watch:{

            selectedItem(){
                this.filterBy = this.selectedItem.name;
            },

            filterBy(){
              this.updateFilterBy();
            },
        },

        async mounted() {
            this.loading = true;
            await this.fetchChainExpirations();
            this.loading = false;
        },
    }
</script>

<style>
    #chain_expirations div.row{
        cursor: pointer;
        padding: 0 6px;
        margin: 0;
    }

    .hideBlock{
        display: none;
    }

    .showBlock{
        display: block;
    }
</style>