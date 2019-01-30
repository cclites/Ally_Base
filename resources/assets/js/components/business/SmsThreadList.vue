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
            <div class="d-flex mb-3">
                <b-btn variant="success" href="/business/communication/sms-other-replies" class="ml-auto">View Other Replies</b-btn>
            </div>

            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :busy="busy"
            >
                <template slot="message" scope="row">
                    {{ messagePreview(row.item.message) }}
                </template>
                <template slot="recipients_count" scope="row">
                    {{ row.item.unique_recipient_count }} Users
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

export default {
    name: "BusinessSmsThreadList",

    mixins: [FormatsDates, FormatsListData],

    props: {
        threads: {
            type: Array,
            default: [],
        }
    },

    data: () => ({
        busy: true,
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
    }),

    computed: {
        businesses() {
            return this.$store.state.business.businesses.filter(item => item.outgoing_sms_number);
        }
    },

    methods: {
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

    mounted() {
        this.items = this.threads;
        this.busy = false;
    },
}
</script>
