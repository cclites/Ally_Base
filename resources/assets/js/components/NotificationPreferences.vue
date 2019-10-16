<template>
    <div>
        <b-card header="Notification Options"
            header-bg-variant="info"
            header-text-variant="white"
        >
            <form @submit.prevent="saveOptions()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="6">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="allow_sms_notifications"
                                    v-model="form.allow_sms_notifications"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Text Message</span>
                            </label>
                        </div>
                        <b-form-input
                            name="notification_phone"
                            type="text"
                            v-model="form.notification_phone"
                            :readonly="authInactive"
                            class="ml-4 mb-3"
                        >
                        </b-form-input>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="allow_email_notifications"
                                    v-model="form.allow_email_notifications"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Email</span>
                            </label>
                        </div>
                        <b-form-input
                            name="notification_email"
                            type="text"
                            v-model="form.notification_email"
                            :readonly="authInactive"
                            class="ml-4 mb-3"
                        >
                        </b-form-input>
                    </b-col>
                </b-row>
                <b-row v-if="user.role_type == 'office_user'">
                    <b-col lg="6" class="mb-3">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="allow_system_notifications"
                                    v-model="form.allow_system_notifications"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">System Notification</span>
                            </label>
                        </div>
                        <input-help :form="form" field="system" text="This will appear as a notification at app.ally.ms.  You will need to click into Notification at the top-right corner of the dashboard."></input-help>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-button variant="success" type="submit" :disabled="authInactive || busy">Save Notification Options</b-button>
                    </b-col>
                </b-row>
            </form>
        </b-card>

        <b-card header="System Notifications"
            header-bg-variant="info"
            header-text-variant="white"
            v-if="user.role_type === 'caregiver'"
        >
            <form v-if="! loading" @submit.prevent="savePreferences()">
                <b-row v-for="item in notifications" :key="item.key">
                    <b-col lg="6">
                        <div class="mb-2" :class="{ 'text-muted': item.disabled }">
                            {{ item.title }}
                            <span v-if="item.disabled"> (Coming Soon)</span>
                        </div>
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="sms"
                                    v-model="preferences[item.key].email"
                                    :disabled="item.disabled"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Email</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="sms"
                                    v-model="preferences[item.key].sms"
                                    :disabled="item.disabled"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Text Message</span>
                            </label>
                            <label class="custom-control custom-checkbox" v-if="user.role_type == 'office_user'">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="sms"
                                    v-model="preferences[item.key].system"
                                    :disabled="item.disabled"
                                    :true-value="1"
                                    :false-value="0">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">System Notification</span>
                            </label>
                        </div>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-button variant="success" type="submit" :disabled="authInactive || busy">Save Notification Options</b-button>
                    </b-col>
                </b-row>
            </form>
        </b-card>

        <b-card v-if="user.role_type == 'office_user'"
            header="Custom Reminders & Notifications"
            header-bg-variant="info"
            header-text-variant="white"
        >
            <center><b>Feature Coming Soon</b></center>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../mixins/FormatsDates';
    import AuthUser from '../mixins/AuthUser';

    export default {
        props: {
            'user': {},
            'notifications': {},
            'admin': false,
        },

        mixins: [FormatsDates, AuthUser],

        data() {
            return {
                loading: true,
                busy: false,
                form: new Form({
                    allow_sms_notifications: this.user.allow_sms_notifications,
                    allow_email_notifications: this.user.allow_email_notifications,
                    allow_system_notifications: this.user.allow_system_notifications,
                    notification_phone: this.user.notification_phone,
                    notification_email: this.user.notification_email,
                }),
                preferences: {},
            }
        },

        computed: {
            urlPrefix() {
                if (this.admin) {
                    switch (this.user.role_type) {
                        case 'client':
                            return `/business/clients/${this.user.id}`;
                        case 'caregiver':
                            return `/business/caregivers/${this.user.id}`;
                    }
                }
                return '/profile';
            }
        },

        methods: {
            saveOptions() {
                this.busy = true;
                this.form.patch(`${this.urlPrefix}/notification-options`)
                    .then(response => {
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    })
            },

            savePreferences() {
                this.busy = true;
                let form = new Form(this.preferences);
                form.post(`${this.urlPrefix}/notification-preferences`)
                    .then(response => {
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy=  false;
                    })
            },
        },

        mounted() {
            this.notifications.forEach(n => {
                let pref = this.user.notification_preferences.find(x => x.key == n.key);
                if (! pref) {
                    pref = {
                        sms: 0,
                        email: 0,
                        system: 1,
                    };
                }
                this.preferences[n.key] = pref;
            });
            this.loading = false;
        }
    }
</script>
