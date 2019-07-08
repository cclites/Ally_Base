<!-- NO LONGER USED -->

<template>
    <div v-if="selectedItem" :selectedItem="selectedItem" :caregiverId="caregiverId">
        <b-form-input
                id="searchInput"
                name="searchInput"
                v-model="selectedItem.name"
        >
        </b-form-input>
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

            caregiverId: {},
            selectedItem: {},
        },

        async mounted() {
            this.loading = true;
            await this.fetchChainExpirations();
            this.loading = false;
        },

        data() {
            return {
                expiration_types: '',
                loading: false,
                //selected_type: '',
                selected: '',
                searchInput: '',
                isVisible: true,
                currentType: '',
            }
        },

        methods: {
            async fetchChainExpirations() {
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.expiration_types = data;
                    })
                    .catch(e => {
                    });
            },

            updateSelected(){
                this.$emit('updateSelectedType', this.selected);
            }

        },

        watch:{
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