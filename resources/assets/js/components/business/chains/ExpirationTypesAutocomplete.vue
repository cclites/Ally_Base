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
             name="expiration_types"
             id="expiration_types"
             v-model="expiration_types"
        >
            <div class="row"
                 v-for="item in expiration_types"
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
                expiration_types: [],
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

                let url='/business/chains/expiration-types/';

                await axios.get(url)
                    .then( ({ data }) => {
                        this.all_expirations_types = data;
                    })
                    .catch(e => {});
            },

            updateSelected(filter){
                this.filterBy = filter;
                this.$emit('updateSelectedType', filter);
                this.expiration_types=[];
                if(this.expiration_types.length > 0){
                    this.isVisible = true;
                }else{
                    this.isVisible = false;
                }

            },

            updateFilterBy(){

                const typeObj = this.all_expirations_types;
                let filter = this.filterBy;

                if(filter === undefined || filter === null){
                    return;
                }

                this.$emit('updateSelectedType', filter);

                this.expiration_types = [];
                this.isVisible = false;

                if(filter.length > 2){

                    for (let [key, value] of Object.entries(typeObj)) {

                        let value_lc = value.type.toLowerCase();
                        let filter_lc = filter.toLowerCase();

                        if(value_lc.startsWith(filter_lc) && value.type !== this.filterBy){
                            this.expiration_types.push(value);
                        }
                    }
                }

                if(this.expiration_types.length > 0){
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
    #expiration_types div.row{
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