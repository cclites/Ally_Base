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
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.all_expirations_types = data;
                    })
                    .catch(e => {
                    });
            },

            updateSelected(filter) {
                this.filterBy = filter;
                this.$emit('updateSelectedType', filter);
                this.expiration_types = [];
                this.isVisible = this.expiration_types.length > 0;
            },

            updateFilterBy() {
                if (! this.filterBy) {
                    return;
                }

                this.$emit('updateSelectedType', this.filterBy);

                this.expiration_types = [];
                this.isVisible = false;
                if (this.filterBy.length > 1) {
                    this.expiration_types = this.all_expirations_types.filter((item) => {
                        return item.type.toLowerCase().startsWith(this.filterBy.toLowerCase())
                            && item.type !== this.filterBy;
                    });
                }

                this.isVisible = this.expiration_types.length > 0;
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