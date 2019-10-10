<template>
    <b-card>
        <h4>Audit Log</h4>
        <b-row>
            <b-col md-12>
                <b-table
                        class="log-table"
                        :items="trail"
                        :fields="fields"
                        :sort-by="sortBy"
                        :empty-text="emptyText"
                        :current-page="currentPage"
                        :per-page="perPage"
                >
                    <template slot="user" scope="row">
                        {{ row.item.user.nameLastFirst }}
                    </template>
                </b-table>
            </b-col>
        </b-row>

        <b-row v-if="this.trail.length > 0">
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import FormatsStrings from "../mixins/FormatsStrings";
    import FormatsDates from "../mixins/FormatsDates";

    export default {
        name: "AuditsTable",
        mixins: [ FormatsDates, FormatsStrings],

        props: ['trail'],

        data(){
            return{
                sortBy: 'user',
                emptyText: "No records",
                perPage: 25,
                currentPage: 1,
                fields: [
                    { label: 'Type', key: 'auditable_title', sortable: true },
                    { label: 'Event', key: 'event', sortable: true, formatter: (val) => this.stringFormat(val) },
                    { label: 'By', key: 'user', sortable: true },
                    { label: 'Date', key: 'updated_at', sortable: true, formatter: (val) => this.formatDateTimeFromUTC(val) },
                    { label: 'Old Values', key: 'old_values', formatter: (val) => this.formatRawJson(val) },
                    { label: 'New Values', key: 'new_values', formatter: (val) => this.formatRawJson(val) },
                ],
            };
        },

        computed: {
            totalRows(){
                return this.trail.length;
            }
        },

        methods: {

            formatRawJson(val)
            {
                let self = this;
                let audit = '';
                let keys = Object.keys(val);

                if(keys.length == 0){
                    return '';
                }

                keys.forEach(function(key){
                    audit += self.stringFormat(key) + ":  " + val[key] + "<br>";
                });

                return audit;
            },
        },
    }

</script>

<style scoped>

</style>