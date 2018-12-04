<template>
    <b-card title="Unconfirmed Shifts">
        <loading-card v-if="loading" />
        <div class="table-responsive" v-else>
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
            :activities="activities" 
            @shift-updated="shiftWasUpdated()"
        />
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import BusinessSettings from "../../mixins/BusinessSettings";
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    export default {
        props: ['shifts', 'activities'],

        mixins: [ FormatsDates, FormatsNumbers, BusinessSettings ],

        data() {
            return {
                loading: false,
                showModifyModal: false,
                current: {caregiver: {}},
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
            shiftWasUpdated() {
                this.showModifyModal = false;
                this.fetch();
            },

            modify(shift) {
                this.current = shift;
                this.showModifyModal = true;
            },

            confirm(shift) {
                if (this.businessSettings().ask_on_confirm && ! confirm('Are you sure you want to confirm this shift?')) {
                    return;
                }

                axios.post(`/unconfirmed-shifts/${shift.id}/confirm`, { confirmed: true })
                    .then( ({ data }) => {
                        let index = this.items.findIndex(obj => obj.id == shift.id);
                        if (index >= 0) {
                            let updated = this.items[index];
                            updated.is_confirmed = true;
                            this.items.splice(index, 1, updated);
                        }
                    })
                    .catch(e => {
                    })
            },

            fetch() {
                this.loading = true;
                axios.get('/unconfirmed-shifts?json=1')
                    .then( ({ data }) => {
                        this.items = data;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    })
            },
        },

        mounted() {
            this.items = this.shifts;
        },
    }
</script>
