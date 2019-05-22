<template>
    <div>
        <div class="d-flex">
            <h3 class="f-1">Contracted Rates With Payer</h3>
            <div class="ml-auto">
                <b-btn variant="info" size="sm" @click="add()">Add Rate</b-btn>
            </div>
        </div>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
            >
                <template slot="service_id" scope="row">
                    <b-select v-model="row.item.service_id" size="sm">
                        <option value="">None</option>
                        <option v-for="service in services" :value="service.id" :key="service.id">{{ service.code }} {{ service.name }}</option>
                    </b-select>
                </template>
                <template slot="effective_start" scope="row">
                    <mask-input v-model="row.item.effective_start" type="date" class="date-input"></mask-input>
                </template>
                <template slot="effective_end" scope="row">
                    <mask-input v-model="row.item.effective_end" type="date" class="date-input"></mask-input>
                </template>
                <template slot="hourly_rate" scope="row">
                    <b-form-input name="hourly_rate"
                        class="money-input"
                        type="number"
                        step="any"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.hourly_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="fixed_rate" scope="row">
                    <b-form-input name="fixed_rate"
                        class="money-input"
                        type="number"
                        step="any"
                        min="0"
                        max="999.99"
                        required
                        v-model="row.item.fixed_rate"
                        size="sm"
                    ></b-form-input>
                </template>
                <template slot="actions" scope="data">
                    <b-btn size="sm" @click="remove(data.index)">
                        <i class="fa fa-trash"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            rates: Array,
            services: Array,
        },

        data() {
            return {
                items: [],
                payer: {},
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'service.name',
                sortDesc: false,
                fields: [
                    {
                        key: 'service_id',
                        label: 'Service',
                        sortable: true
                    },
                    {
                        key: 'effective_start',
                        label: 'Effective Start',
                        sortable: true,
                    },
                    {
                        key: 'effective_end',
                        label: 'Effective End',
                        sortable: true,
                    },
                    {
                        key: 'hourly_rate',
                        label: 'Hourly Rate',
                        sortable: true,
                    },
                    {
                        key: 'fixed_rate',
                        label: 'Fixed Rate',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        computed: {
        },

        methods: {
            add() {
                this.items.push({
                    service_id: '',
                    effective_start: moment().format('MM/DD/YYYY'),
                    effective_end: moment('9999-12-31').format('MM/DD/YYYY'),
                    hourly_rate: '0.00',
                    fixed_rate: '0.00',
                })
            },

            remove(index) {
                if (index >= 0) {
                    this.items.splice(index, 1);
                }
            },

            setItems(data) {
                if (data) {
                    this.items = data.map(x => {
                        x.hourly_rate = parseFloat(x.hourly_rate).toFixed(2);
                        x.fixed_rate = parseFloat(x.fixed_rate).toFixed(2);
                        x.effective_start = moment(x.effective_start).format('MM/DD/YYYY');
                        x.effective_end = moment(x.effective_end).format('MM/DD/YYYY');
                        return x;
                    });
                } else {
                    this.items = [];
                }
            },
        },

        watch: {
            rates(newVal) {
                this.setItems(newVal);
            },
        },
        
        mounted() {
            this.loaded = false;
            this.setItems(this.rates);
        }
    }
</script>

<style scoped>
    .money-input { width: 85px!important }
    .date-input { max-width: 120px!important }
</style>
