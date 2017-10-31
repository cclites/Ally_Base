<template>
    <b-card
        header="Care Plans"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn variant="info" href="/business/care_plans/create">Add Care Plan</b-btn>
        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/business/care_plans/' + row.item.id">Edit</b-btn>
                    <b-btn size="sm" @click="deletePlan(row.item)" variant="danger">X</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregiverId': {},
            'plans': {},
        },

        data() {
            return {
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'activity_count',
                        label: 'Number of ADLs',
                        sortable: true,
                    },
                    {
                        key: 'updated_at',
                        label: 'Last Updated',
                        sortable: true,
                    },
                    'actions'
                ],
                planItems: this.plans, // store to avoid mutating prop
                planModal: false,
                selectedPlan: null,
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items: {
                get() {
                    return this.planItems.map(function(plan) {
                        plan.updated_at = moment.utc(plan.updated_at).local().format('MM/DD/YYYY h:mm A');
                        plan.activity_count = plan.activities.length;
                        return plan;
                    });
                },
                set(value) {
                    this.planItems = value;
                }
            }
        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            deletePlan(plan) {
                let component = this;
                let form = new Form();
                if (confirm('Are you sure you wish to delete this care plan?')) {
                    form.submit('delete', '/business/care_plans/' + plan.id)
                        .then(function(response) {
                            let index = component.planItems.findIndex(item => item.id === plan.id);
                            Vue.delete(component.planItems, index);
                        });
                }
            }
        }
    }
</script>
