<template>
    <div>
        <div class="alert alert-warning" v-if="businesses.length === 0">
            Please contact Ally to enable text messages on your account.
        </div>
        <b-card v-else
                header="Sent Text History"
                header-text-variant="white"
                header-bg-variant="info"
        >
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
                    <b-form-group class="form-check mb-3" label="&nbsp;">
                        <b-form-checkbox v-model="repliesOnly" value="1" unchecked-value="0">Show only threads with replies</b-form-checkbox>
                    </b-form-group>
                    <b-form-group label="&nbsp;">
                        <b-btn variant="info" @click="fetch()" :disabled="busy">Generate</b-btn>
                    </b-form-group>
                </div>
                <div class="ml-auto">
                    <b-btn variant="success" href="/business/communication/sms-other-replies" class="ml-auto">View Other Replies</b-btn>
                </div>
            </div>

            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :busy="busy"
                     empty-text="Select dates and press Generate"
            >
                <template slot="message" scope="row">
                    {{ messagePreview(row.item.message) }}
                </template>
                <template slot="recipients_count" scope="row">
                    {{ row.item.unique_recipient_count }} Users
                </template>
                <template slot="replies_count" scope="data">
                    <span>{{ data.value }}</span>
                    <span v-if="data.item.unread_replies_count > 0" class="ml-2 text-danger">New</span>
                </template>
                <template slot="sent_at" scope="row">
                    {{ formatDateTimeFromUTC(row.item.sent_at) }}
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" variant="secondary" @click.stop="openThread(row.item)" :disabled="busy">View Details</b-btn>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsListData from "../../mixins/FormatsListData";
    import BusinessLocationFormGroup from "./BusinessLocationFormGroup";
    import BusinessLocationSelect from "./BusinessLocationSelect";

    export default {
        name: "BusinessSmsThreadList",

        mixins: [FormatsDates, FormatsListData],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        data: () => ({
            busy: false,
            items: [],
            perPage: 25,
            currentPage: 1,
            sortBy: 'sent_at',
            sortDesc: true,
            fields: [
                {
                    key: 'message',
                    sortable: true,
                },
                {
                    label: 'To',
                    key: 'recipients_count',
                    sortable: true,
                },
                {
                    label: 'Replies',
                    key: 'replies_count',
                    sortable: true,
                },
                {
                    label: 'Sent On',
                    key: 'sent_at',
                    sortable: true,
                },
                // {  // For some reason this isn't working, need to debug
                //     label: 'Office Location',
                //     key: 'business_id',
                //     sortable: true,
                //     formatter: this.showBusinessName,
                // },
                {
                    key: 'actions',
                    class: 'hidden-print'
                },
            ],
            start_date: moment().subtract(7, 'days').format('MM/DD/YYYY'),
            end_date: moment().format('MM/DD/YYYY'),
            repliesOnly: 0,
            business_id: '',
        }),

        computed: {
            businesses() {
                return this.$store.state.business.businesses.filter(item => item.outgoing_sms_number);
            }
        },

        methods: {
            fetch() {
                this.busy = true;
                axios.get(`/business/communication/sms-threads?json=1&start_date=${this.start_date}&end_date=${this.end_date}&reply_only=${this.repliesOnly}&business_id=${this.business_id}`)
                    .then( ({ data }) => {
                        this.items = data;
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    })
            },

            messagePreview(message) {
                if (message.length <= 70) {
                    return message;
                }

                return message.substr(0, 70) + '...';
            },

            openThread(thread) {
                window.location = `/business/communication/sms-threads/${thread.id}`;
            }
        },
    }
</script>
