<template>
    <b-card>
        <b-row>
            <b-table :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :tbody-tr-class="rowClass"
            >
            </b-table>
        </b-row>
    </b-card>
</template>

<script>
    import FormatsListData from "../../../mixins/FormatsListData";

    export default {
        mixins: [FormatsListData],

        props: ['caregivers'],

        data() {
            return {
                sortBy: 'has_amount_owed',
                sortDesc: true,
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
            items() {
                return this.caregivers.map(item => {
                    let chain = item.business_chains.length ? item.business_chains[0] : null;
                    item.chain_name = chain ? chain.name : "";
                    if (item.has_amount_owed) item._rowVariant = "warning";
                    return item;
                })
            }
        },

        methods: {

        }
    }
</script>