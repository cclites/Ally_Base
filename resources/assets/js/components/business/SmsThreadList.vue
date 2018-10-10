<template>
    <b-card
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
</template>

<script>
import FormatsDates from '../../mixins/FormatsDates';

export default {
    name: "BusinessSmsThreadList",

    mixins: [FormatsDates],

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
            {
                key: 'actions',
                class: 'hidden-print'
            },
        ],
    }),

    computed: {
    },

    methods: {
        messagePreview(message) {
            if (message.length <= 20) {
                return message;
            }

            return message.substr(0, 20) + '...';
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
