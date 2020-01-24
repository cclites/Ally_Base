<template>
    <div>
        <div class="alert alert-warning" v-if="businesses.length === 0">
            Please contact Ally to enable text messages on your account.
        </div>
        <b-card v-else
                header="Unsorted Incoming Text Message Replies"
                header-text-variant="white"
                header-bg-variant="info"
        >
            <b-row>

                <b-col>

                    <p>Unsorted replies occur when text messages are received but unable to be matched to any recent thread. These replies are still created but - as the name suggests - unable to be matched to any specific thread and thus appear here</p>
                </b-col>
            </b-row>
            <div class="d-flex mb-2">
                <div class="f-1 d-flex align-items-baseline flex-col">
                    <business-location-form-group
                            v-model="business_id"
                            :allow-all="true"
                            class="mr-2 location_select"
                            label="Location"
                    />
                    <b-form-group class="mr-2" label="Start Date">
                        <date-picker ref="startDate"
                                     v-model="start_date"
                                     placeholder="Start Date">
                        </date-picker>
                    </b-form-group>
                    <b-form-group class="mr-2" label="End Date">
                        <date-picker ref="endDate"
                                     v-model="end_date"
                                     placeholder="End Date">
                        </date-picker>
                    </b-form-group>
                    <b-form-group label="&nbsp;">
                        <b-btn variant="info" @click="fetch()" :disabled="busy">Generate</b-btn>
                    </b-form-group>
                </div>
            </div>

            <business-sms-reply-table :replies="items" />
        </b-card>
    </div>
</template>

<script>
import BusinessLocationFormGroup from "./BusinessLocationFormGroup";

export default {
    components: {BusinessLocationFormGroup},

    data() {
        return {
            busy: false,
            items: [],
            start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
            end_date: moment().format('MM/DD/YYYY'),
            repliesOnly: 0,
            business_id: '',
        }
    },

    computed: {
        businesses() {
            return this.$store.state.business.businesses.filter(item => item.outgoing_sms_number);
        }
    },

    methods: {
        fetch() {
            this.busy = true;
            axios.get(`/business/communication/sms-other-replies?json=1&start_date=${this.start_date}&end_date=${this.end_date}&businesses=${this.business_id}`)
                .then( ({ data }) => {
                    this.items = data;
                })
                .catch(e => {
                })
                .finally(() => {
                    this.busy = false;
                })
        },
    },
}
</script>
