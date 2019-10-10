<template>
    <b-card
        header="Text Message Details"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row class="mb-4">
            <b-col md="6">
                <h4>Message:</h4>
                <p class="message-body">
                    {{ thread.message }}
                </p>
            </b-col>
            <b-col md="6" class="d-flex">
                <div class="ml-auto">
                    {{ formatDateTimeFromUTC(thread.sent_at) }}
                </div>
            </b-col>
        </b-row>
        <b-row class="mb-4">
            <b-col>
                <h4>Recipients: {{ thread.unique_recipient_count }} <b-btn size="sm" variant="success" class="ml-3" @click.prevent="toggleRecipients()">{{ showRecipients ? 'Hide' : 'Show' }}</b-btn></h4>

                <div class="mt-2 user-pills" v-show="showRecipients">
                    <b-badge pill
                        v-for="recipient in uniqueRecipients"
                        :key="recipient.id"
                        :href="`/business/${recipient.user.role_type}s/${recipient.user.id}`"
                        target="_blank"
                        variant="light"
                        class="mr-2"
                    >
                        {{ recipient.user.name }}
                    </b-badge>
                </div>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <h4>Replies</h4>
                <business-sms-reply-table :replies="thread.replies" />
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
import FormatsDates from '../../mixins/FormatsDates';

export default {
    name: "BusinessSmsThread",

    mixins: [FormatsDates],

    props: {
        thread: {
            type: Object,
            default: () => { {} },
        }
    },

    data: () => ({
        busy: true,
        showRecipients: false,

        perPage: 25,
        currentPage: 1,
        sortBy: 'created_at',
        sortDesc: true,
        replyFields: [
            {
                label: 'From',
                key: 'from_number',
                sortable: true,
            },
            {
                label: 'User',
                key: 'user',
                sortable: true,
            },
            {
                key: 'message',
                sortable: true,
            },
            {
                label: 'Received',
                key: 'created_at',
                sortable: true,
            },
        ],
    }),

    computed: {
        uniqueRecipients() {
            if (! this.thread.recipients) {
                return [];
            }

            let unique = [];
            this.thread.recipients.forEach(item => {
                console.log(item);
                if (unique.findIndex(x => x.user_id == item.user_id) < 0) {
                    unique.push(item);
                }
            });

            return unique.sort( (a, b) => a.name > b.name ? 1 : -1);
        },
    },

    methods: {
        formatPhone(phone) {
            if (! phone) {
                return '';
            }

            if (phone.length != 10) {
                return phone;
            }

            return '(' + phone.substr(0, 3) + ') ' + phone.substr(3, 3) + '-' + phone.substr(6);
        },

        userLink(user) {
            if (user.role == 'client') {
                return `/business/clients/${user.id}`;
            }
            return `/business/caregivers/${user.id}`;
        },

        toggleRecipients() {
            this.showRecipients = ! this.showRecipients;
        },
    },

    mounted() {
        this.busy = false;
    },
}
</script>

<style>
    .message-body {
        white-space: pre-line;
    }
</style>