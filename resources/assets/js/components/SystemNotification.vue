<template>
    <b-card
        :header="notification.title"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-row>
            <b-col lg="12">
                <p><strong>Description</strong></p>
                <p>
                    {{ notification.message | nl2br }}
                </p>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="12" v-if="acknowledger">
                <p>Acknowledged by {{ acknowledger.firstname }} {{ acknowledger.lastname }} at {{ time }}</p>
                <b-button variant="secondary" :href="notification.action_url">{{ referenceUrlTitle }}</b-button>
            </b-col>
            <b-col lg="12" v-else>
                <b-form-group label-for="notes">
                    <b-button v-show="! isTimesheet" variant="info" @click="acknowledge()">Acknowledge Notification</b-button>
                    <b-button v-show="! isTimesheet" variant="info" @click="acknowledgeAllForChain()">Acknowledge Notification For All Users</b-button>
                    <b-button variant="secondary" :href="notification.action_url">{{ referenceUrlTitle }}</b-button>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            'notification': {},
            'acknowledger': {},
        },
        data() {
            return {
                'form': new Form(),
            }
        },
        computed: {
            time() {
                return moment.utc(this.notification.created_at).local().format('L LT');
            },
            referenceUrlTitle() {
                if (this.notification.action) {
                    return this.notification.action || 'Reference Link';
                }
            },
            isTimesheet() {
                return this.notification.reference_type === 'App\\Timesheet';
            },
        },
        methods: {
            acknowledge() {
                this.form.post('/business/notifications/' + this.notification.id + '/acknowledge');
            },

            acknowledgeAllForChain() {
                this.form.post('/business/notifications/' + this.notification.event_id + '/acknowledge-all');
            }
        }
    }
</script>