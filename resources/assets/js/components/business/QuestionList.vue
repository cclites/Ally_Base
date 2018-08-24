<template>
    <div>
        <div class="d-flex mb-3">
            <b-btn variant="info" @click="create()" class="ml-auto">Add Question</b-btn>
        </div>
        <b-table bordered striped hover show-empty
            :items="items"
            :fields="fields"
            :current-page="currentPage"
            :per-page="perPage"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
        >
            <template slot="actions" scope="row">
                <b-btn size="sm" @click.stop="editActivity(row.item)" v-if="row.item.business_id || row.item.new">Edit</b-btn>
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        props: ['business_id', 'value'],

        data: () => ({
            question: {},

            items: [],
            perPage: 25,
            currentPage: 1,
            sortBy: 'question',
            sortDesc: false,
            formModal: false,
            fields: [
                {
                    key: 'client_type',
                    sortable: true,
                    formatter: x => x[0].toUpperCase() + x.slice(1),
                },
                {
                    key: 'question',
                    sortable: true,
                },
                {
                    key: 'required',
                    sortable: true,
                    formatter: x => x == 1 ? 'Yes' : 'Np',
                },
                {
                    key: 'actions',
                    class: 'hidden-print'
                },
            ],

        }),

        methods: {
        },

        mounted() {
            axios.get('/business/questions')
                .then( ({ data }) => {
                    this.items = data;
                })
                .catch(e => {
                    console.log(e);
                })
        },
    }
</script>
