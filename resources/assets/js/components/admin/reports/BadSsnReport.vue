<template>
    <b-card>
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
                        <template slot="name" scope="row">
                            <a :href="`/business/${row.item.type}s/${row.item.id}`" target="_blank">{{ row.item.name }}</a>
                        </template>
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
    </b-card>
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
                        label: 'Office Location',
                        sortable: true
                    },
                    // {
                    //     key: 'type',
                    //     label: 'Type',
                    // },
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