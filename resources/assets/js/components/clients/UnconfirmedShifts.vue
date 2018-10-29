<template>
    <b-card title="Unconfirmed Shifts">
        <div class="table-responsive">
            <b-table show-empty :items="items" :fields="fields">
                <template scope="data" slot="actions">
                    <div v-if="data.item.is_confirmed" class="text-muted">
                        Confirmed
                    </div>
                    <div v-else>
                        <b-button @click="modify(data.item)" variant="success">Modify</b-button>
                        <b-button @click="confirm(data.item)" variant="warning">Confirm</b-button>
                    </div>
                </template>
            </b-table>
        </div>

        <client-modify-shift-modal v-model="showModifyModal" 
            :shift_id="current.id"
            :activities="activities" />
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    export default {
        props: ['shifts', 'activities'],

        mixins: [ FormatsDates, FormatsNumbers ],

        data() {
            return {
                showModifyModal: false,
                current: {},
                items: [],
                fields: [
                    {
                        key: 'date',
                        label: 'Start',
                        sortable: true,
                        formatter: (value) => { return this.formatDateTimeFromUTC(value.date); },
                    },
                    {
                        key: 'caregiver',
                        label: 'Caregiver'
                    },
                    {
                        key: 'hours',
                        label: 'Duration'
                    },
                    {
                        key: 'rate',
                        label: 'Hourly Rate',
                        formatter: (value) => { return this.moneyFormat(value); }
                    },
                    {
                        key: 'total',
                        label: 'Total',
                        formatter: (value) => { return this.moneyFormat(value); }

                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {
            modify(shift) {
                this.showModifyModal = true;
            },

            confirm(shift) {
                axios.post(`/unconfirmed-shifts/${shift.id}/confirm`, { confirmed: true })
                    .then( ({ data }) => {
                        console.log('id: ', shift.id);
                        let index = this.items.findIndex(obj => obj.id == shift.id);
                        console.log('index: ', index);
                        if (index >= 0) {
                            console.log('item: ', this.items[index]);
                            this.items[index].is_confirmed = true;
                        }
                    })
                    .catch(e => {
                    })
            },
        },

        computed: {
        },

        mounted() {
            this.items = this.shifts;
        },
    }
</script>
