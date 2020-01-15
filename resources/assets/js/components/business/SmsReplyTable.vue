<template>
    <div>
        <b-table bordered striped hover show-empty
            :items="replies" 
            :fields="fields"
            :current-page="currentPage"
            :per-page="perPage"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            :busy="busy"
        >
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
            <template slot="actions" scope="row">

                <a :href=" `/business/communication/sms-threads/${row.item.continued_thread_id}` " v-if=" row.item.continued_thread_id " target="_blank">continued</a>
                <b-button variant="info" @click=" replyText( row.item ) " v-else>Reply</b-button>
            </template>
        </b-table>
        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>

        <business-sms-reply-modal v-model=" replyModalOpen " :data=" activeReply " @continuedThread=" continuedThread "></business-sms-reply-modal>
    </div>
</template>

<script>
import FormatsDates from '../../mixins/FormatsDates';

export default {
    name: "SmsReplyTable",

    mixins: [FormatsDates],

    props: {
        replies: {
            type: Array,
            default: [],
        },
    },

    data: () => ({

        activeReply : {},
        replyModalOpen : false,
        busy: false,
        perPage: 50,
        currentPage: 1,
        sortBy: 'created_at',
        sortDesc: true,
        fields: [
            {
                label: 'From',
                key: 'from_number',
                sortable: true,
                tdClass: 'text-nowrap',
            },
            {
                label: 'User',
                key: 'user',
                sortable: true,
                tdClass: 'text-nowrap',
            },
            {
                key: 'message',
                sortable: true,
                tdClass: 'pb-4 message-body'
            },
            {
                label: 'Received',
                key: 'created_at',
                sortable: true,
                tdClass: 'text-nowrap',
            },
            {
                label: 'Actions',
                key: 'actions',
                sortable: false,
                tdClass: 'text-nowrap',
            },
        ],
    }),

    computed: {
        totalRows() {
            return this.replies.length;
        },
    },

    methods: {

        continuedThread({ new_thread_id, reply_id }){

            let reply = this.replies.find( r => r.id == reply_id );
            reply.continued_thread_id = new_thread_id;
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
        replyText( reply ){

            this.replyModalOpen = true;
            this.activeReply = reply;
        }
    },

    mounted() {
    },
}
</script>