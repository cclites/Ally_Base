<template>
    <b-card title="Pending Shifts">
        <loading-card v-if="loading" />
        <div class="table-responsive" v-else>
            <b-table show-empty :items="items" :fields="fields">
                <template scope="data" slot="actions">
                    <div v-if="data.item.confirmed" class="text-muted">
                        <b-button @click="unconfirm(data.item)" variant="primary" :disabled="authInactive">Un-confirm</b-button>
                    </div>
                    <div v-else>
                        <b-button @click="confirm(data.item)" variant="info" :disabled="authInactive">Confirm</b-button>
                        <b-button @click="modify(data.item)" variant="danger" :disabled="authInactive">Modify</b-button>
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
    import FormatsNumbers from '../../mixins/FormatsNumbers';
    export default {
        props: ['shifts', 'activities'],

        mixins: [ FormatsDates, FormatsNumbers ],

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
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
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
                        key: 'confirmed',
                        formatter: (value) => value ? '<i class="fa fa-check" style="color: green"></i>' : '',
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        computed: {
            authInactive() {
                return window.AuthUser && window.AuthUser.active == 0;
            },
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

            unconfirm(shift) {
                return this.confirm(shift, false);
            },

            confirm(shift, confirmed=true) {

                let slug = confirmed ? 'confirm' : 'unconfirm';

                if (!confirm('Are you sure you want to ' + slug + ' this shift?')) {
                    return;
                }

                axios.post(`/unconfirmed-shifts/${shift.id}/confirm`, { confirmed })
                    .then( ({ data }) => {
                        let index = this.items.findIndex(obj => obj.id == shift.id);
                        if (index >= 0) {
                            Vue.set(this.items, index, {
                                ...this.items[index],
                                confirmed
                            });
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
