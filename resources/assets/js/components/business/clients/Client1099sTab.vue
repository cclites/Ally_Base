<template>
    <b-card header="Tax Documents"
        header-bg-variant="info"
        header-text-variant="white"
    >
        <div v-if="items.length" class="table-responsive">
            <b-table
                bordered striped hover show-empty
                :items="items"
                :fields="fields"
                sort-by="year"
            >
                <template slot="actions" scope="row">
                    <a :href="`/business/client-1099/download/${row.item.id}`">Download 1099</a>
                </template>
            </b-table>
        </div>
        <div v-else>
            There are no records to display.
        </div>

        <hr>
        <b-alert show variant="info">Note: 2018 and prior years would have been mailed to you and are not available electronically.</b-alert>
    </b-card>
</template>

<script>
    export default {
        props: {
            client: {
                type: [Number, String],
                required: true,
            }
        },

        data() {
            return {
                items: [],
                fields: {
                    year: {sortable: true},
                    caregiver: {label: 'Caregiver', sortable: true},
                    actions: {sortable: false},
                },
            }
        },

        mounted(){
            this.fetch();
        },

        methods:{
            fetch(){
                axios.get(`/business/client-1099/${this.client}`)
                    .then(response => {
                        this.items = response.data;
                    })
                    .catch( e => {})
                    .finally(() => {});
            }
        }
    }
</script>
