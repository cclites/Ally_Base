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
        >
            <form @submit.prevent="save()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="6">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    name="sms"
                                    v-model="form.sms"
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
                            required
                            :readonly="authInactive"
                            class="ml-4 mb-3"
                        >
                        </b-form-input>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-button variant="success" type="submit" :disabled="authInactive || busy">Save Notification Options</b-button>
                    </b-col>
                </b-row>
            </form>
        </b-card>

        <b-card header="Custom Reminders & Notifications"
            header-bg-variant="info"
            header-text-variant="white"
            v-if="user.role_type == 'office_user'"
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
        },

        mixins: [FormatsDates, AuthUser],

        data() {
            return {
                busy: false,
                form: new Form({
                    allow_sms_notifications: this.user.allow_sms_notifications,
                    allow_email_notifications: this.user.allow_email_notifications,
                    allow_system_notifications: this.user.allow_system_notifications,
                    notification_phone: this.user.notification_phone,
                    notification_email: this.user.notification_email,
                })
            }
        },

        methods: {
            saveOptions() {
                this.busy = true;
                this.form.patch('/profile/notification-options')
                    .then(response => {
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                    })
            }
        }
    }
</script>
