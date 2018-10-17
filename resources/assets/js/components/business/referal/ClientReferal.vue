<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="2">
                <a href="add/client-referal" class="btn btn-secondary ">Add Referral Sources</a>
            </b-col>
            <b-col lg="3">
                <b-form-group horizontal label="Search:" class="mb-0">
                    <b-input-group>
                        <b-form-input v-model="search"/>
                    </b-input-group>
                </b-form-group>
            </b-col>
        </b-row>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="referralSources"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                    <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                    <b-btn size="sm" :href="'/business/clients/' + row.item.id">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <b-row>
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
    export default {
        props: ['referralSources'],

        data() {
            return {
                active: 'active',
                totalRows: 0,
                currentPage: 1,
                perPage: 15,
                filter: null,
                search: null,
                sortBy: 'organization',
                sortDesc: false,
                fields: [
                    {
                        key: 'organization',
                        label: 'Organization',
                        sortable: true
                    },
                    {
                        key: 'contact_name',
                        label: 'Name',
                        sortable: true
                    },
                    {
                        key: 'phone',
                        label: 'Phone',
                        sortable: true
                    },
                    {
                        key: 'created_at',
                        label: 'Created At',
                        sortable: true
                    }
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items() {
                let component = this;
                let referralSources = this.referralSources.map(function(referralSource) {
                    return {
                        organization: referralSource.organization,
                        contact_name: referralSource.contact_name,
                        phone: referralSource.phone,
                        created_at: referralSource.created_at,

                    }
                });

                return _.filter(referralSources, (client) => {
                    switch (this.active) {
                        case 'all':
                            return true;
                        case 'active':
                            return client.active;
                        case 'inactive':
                            return !client.active;
                    }
                })
            },
        },
    }
</script>

<style scoped>

</style>