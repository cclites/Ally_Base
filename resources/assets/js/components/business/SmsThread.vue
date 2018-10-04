<template>
    <b-card
        header="Text Message Details"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <b-row class="mb-4">
            <b-col md="6">
                <h4>Message:</h4>
                {{ thread.message }}
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
                <div v-if="thread.replies.length == 0" class="text-muted">This message has no replies</div>
                <div v-else>
                    <b-table :items="thread.replies" :fields="replyFields">
                        <template slot="created_at" scope="row">
                            {{ formatDateTimeFromUTC(row.item.created_at) }}
                        </template>
                        <template slot="user" scope="row">
                            <span v-if="! row.item.user" class="text-muted">Unknown</span>
                            <span v-else><a :href="userLink(row.item.user)">{{ row.item.user.name }}</a></span>
                        </template>
                        <template slot="from_number" scope="row">
                            {{ formatPhone(row.item.from_number) }}
                        </template>
                    </b-table>
                </div>
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

        items: [],
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
        }
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
        },

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
        this.items = this.threads;
        this.busy = false;
    },
}
</script>
