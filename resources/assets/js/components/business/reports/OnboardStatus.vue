<template>
    <b-card>
        <div class="d-flex mb-3">
            <div class="f-1">
                <h4>{{ typeTitle }}s Onboarded Status</h4>
            </div>
            <div class="ml-auto">
                <b-button-group>
                    <b-button variant="info" :class="{ disabled: typeExceptWeAreeNotMutatingPropsThisTime == 'caregiver' }" @click="switchType('client')">Generate Clients Report</b-button>
                    <b-button variant="info" :class="{ disabled: typeExceptWeAreeNotMutatingPropsThisTime == 'client' }" @click="switchType('caregiver')">Generate Caregivers Report</b-button>
                </b-button-group>
            </div>
        </div>

        <loading-card v-show="loading" />

        <div v-show="! loading" class="table-responsive">
            <b-table :items="items"
                show-empty
                :fields="fields">
                <template slot="name" scope="data">
                    <a :href="`/business/${typeExceptWeAreeNotMutatingPropsThisTime}s/${data.item.id}`">{{ data.item.name }}</a>
                </template>>
                <template slot="email_sent_at" scope="data">
                    <span v-if="data.item.email_sent_at">{{ formatDateTime(data.item.email_sent_at) }}</span>
                    <span v-else>Not Sent</span>
                </template>>
            </b-table>
        </div>
    </b-card>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        mixins: [ FormatsDates ],

        props: ['type'],

        data() {

            return {

                items: [],
                loading: false,
                typeExceptWeAreeNotMutatingPropsThisTime : null,
                fields: [
                    { key: 'name' },
                    { key: 'email_sent_at', label: 'Email Sent' },
                    { key: 'onboard_status', formatter: (val) => _.startCase(val) },
                ],
            };
        },

        computed: {
            typeTitle() {
                return this.typeExceptWeAreeNotMutatingPropsThisTime == 'client' ? 'Client' : 'Caregiver';
            },
        },

        methods: {
            fetch() {
                this.loading = true;
                axios.get(`/business/reports/onboard-status?fetch=1&type=${this.typeExceptWeAreeNotMutatingPropsThisTime}`)
                    .then(response => {
                        this.items = _.sortBy(response.data, 'name');
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                });
            },

            switchType(val) {

                this.typeExceptWeAreeNotMutatingPropsThisTime = val;
                this.fetch();
            },
        },

        mounted() {

            this.typeExceptWeAreeNotMutatingPropsThisTime = this.type;
        },
    }
</script>
