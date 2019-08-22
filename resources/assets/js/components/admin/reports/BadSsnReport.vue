<template>
    <div>
        <b-row>
            <b-col lg="12">

                <div class="table-responsive" >
                    <b-table
                            class="bad-ssn-report"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :current-page="currentPage"
                            :per-page="perPage"
                            :show-empty="true"
                    >
                    </b-table>
                </div>

                <b-row v-if="this.items.length > 0">
                    <b-col lg="6" >
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                    </b-col>
                    <b-col lg="6" class="text-right">
                        Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                    </b-col>
                </b-row>

            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        name: "BadSsnReport",

        props: {
            report: {
                default() {
                    return [];
                }
            },
        },
        data(){
            return {
                items: [],
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'name',
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'business',
                        label: 'Business Location',
                        sortable: true
                    },
                    {
                        key: 'type',
                        label: 'Type',
                    },
                ],
            };
        },

        mounted() {
            this.items = this.report;
            this.totalRows = this.items.length;
        },
    }
</script>

<style scoped>

</style>