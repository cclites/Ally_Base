<template>
    <b-card header="Profile"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <b-row>
            <b-col>
                <b-alert show v-if="!hasBusiness" variant="info">
                    You must choose a business location to continue.
                </b-alert>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="6">
                <business-location-form-group
                        v-model="business"
                        label="Office Location"
                        class="mr-2"
                        :allow-all="false"
                />
            </b-col>
            <b-col md="6">
                <b-form-group label="&nbsp;" class="float-right">
                    <b-button-group>
                        <b-button @click="fetch()" variant="info" :disabled="!hasBusiness || loading"
                                title="You must select a Registry to continue"><i class="fa fa-file-pdf-o mr-1"></i>Generate
                            Report
                        </b-button>
                    </b-button-group>
                </b-form-group>
            </b-col>
        </b-row>

        <loading-card v-if="loading" text="Loading report..."></loading-card>
        <div v-else class="table-responsive">
            <b-table bordered striped hover show-empty
                :items="items"
                :fields="fields"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                :empty-text="emptyText"
            >
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsListData from "../../../mixins/FormatsListData";
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        mixins: [FormatsListData],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        data() {
            return {
                sortBy: 'has_amount_owed',
                sortDesc: true,
                business: '',
                loading: false,
                items: [],
                emptyText: "No results to display",
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name',
                        sortable: true,
                        formatter: (val, index, item) => {
                            return val + (item.has_amount_owed ? '*' : '');
                        }
                    },
                    {
                        key: 'email',
                        sortable: true,
                    },
                    {
                        key: 'chain_name',
                        label: "Business Chain",
                        sortable: true,
                    },
                    {
                        key: 'has_amount_owed',
                        sortable: true,
                        formatter: val => this.formatYesNo(val),
                    }
                ]
            }
        },

        computed: {
            hasBusiness() {
                return !!this.business;
            },

            url() {
                return "/admin/reports/caregivers/deposits-missing-bank-account?business=" + this.business;
            }
        },

        methods: {
            fetch() {
                this.loading = true;
                axios.get(this.url)
                    .then(({data}) => {
                        this.items = data.map(item => {
                            let chain = item.business_chains.length ? item.business_chains[0] : null;
                            item.chain_name = chain ? chain.name : "";
                            if (item.has_amount_owed) item._rowVariant = "warning";
                            return item;
                        })
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        },
    }
</script>