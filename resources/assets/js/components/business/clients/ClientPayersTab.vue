<template>
    <b-card
        header="Client Payers"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn variant="info" class="mb-2" @click="showModal()">Add Payer</b-btn>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     ref="table"
            >
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        props: {
            'client': {},
            'payers': Array,
        },

        mixins: [ FormatsDates ],

        data() {
            return {
                items: [],
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
            }
        },

        computed: {
        },

        methods: {
        },

        mounted() {
        },
    }
</script>
<style>
</style>