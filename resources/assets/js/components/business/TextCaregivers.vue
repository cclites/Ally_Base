<template>
    <div>
        <div class="alert alert-warning" v-if="businesses.length === 0">
            Please contact Ally to enable text messages on your account.
        </div>
        <b-card v-else
                header="Create Message"
                header-text-variant="white"
                header-bg-variant="info"
        >
            <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col md="6" class="d-flex align-items-baseline">
                        <div class="form-check">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="all" v-model="form.all" value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Send to all active Caregivers for</span>
                            </label>
                            <input-help :form="form" field="accepted_terms" text=""></input-help>
                        </div>
                        <business-location-select class=" f-1" v-model="form.businesses" :allow-all="true" name="businesses" :disabled="! form.all"/>
                    </b-col>
                    <b-col md="6" class="d-flex">
                        <b-form-group class="ml-auto">
                            <div class="form-check">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="can_reply" v-model="form.can_reply" value="1">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Accept Replies</span>
                                </label>
                                <input-help :form="form" field="accepted_terms" text=""></input-help>
                            </div>
                            <div class="form-check" v-if="isAdmin">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="debug" v-model="form.debug" value="1">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Debug Mode</span>
                                </label>
                                <input-help :form="form" field="accepted_terms" text=""></input-help>
                            </div>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-form-group v-if="! form.all">
                    <div class="pb-2">
                        <label>Recipients</label>
                        <b-btn variant="success" class="ml-3" href="/business/care-match">Select Caregivers via CareMatch</b-btn>
                    </div>
                    <user-search-dropdown placeholder="Add Recipient"
                                          icon="fa-plus"
                                          :formatter="searchDisplay"
                                          @selectUser="addUser"
                                          type="sms"
                                          role="caregiver"
                                          :disabled="submitting" />

                    <div class="mt-2 user-pills">
                        <b-badge pill
                                 :href="`/business/${user.role_type}s/${user.id}`"
                                 target="_blank"
                                 v-for="user in selectedUsers"
                                 :key="user.id"
                                 variant="light">
                            {{ searchDisplay(user) }}

                            <a href="#" class="delete-btn" @click.stop="removeUser(user.id)"><i class="fa fa-times"></i></a>
                        </b-badge>
                    </div>
                    <input-help :form="form" field="recipients" text=""></input-help>
                </b-form-group>
                <business-location-form-group label="From Number (This is the number Caregivers will receive the text message from and reply to)"
                                              v-model="form.business_id"
                                              :form="form"
                                              field="business_id">
                </business-location-form-group>
                <b-form-group
                    label="Message"
                    label-class="required"
                    :valid-feedback="validFeedback"
                >
                    <b-textarea
                        id="message-text"
                        :rows="6"
                        v-model="form.message"
                        required
                        :disabled="submitting"
                        :state="state"
                    ></b-textarea>
                    <input-help :form="form" field="message" :text=" `${form.message.length}/${maxLength} character limit` " :class=" textWarningClass( form.message.length ) "></input-help>
                    <b-form-invalid-feedback id="message-text-feedback" :class=" textWarningClass( form.message.length ) ">{{ invalidFeedback }}</b-form-invalid-feedback>
                </b-form-group>
                <b-form-group>
                    <b-button variant="info" type="submit" :disabled="submitting || form.message.length >= maxLength">
                        <i class="fa fa-spin fa-spinner" v-if="submitting"></i> Send Message
                    </b-button>
                </b-form-group>
                <b-alert variant="info" show>
                    This will only send messages to Caregivers who have a phone number selected for text messages.
                </b-alert>
            </form>
        </b-card>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from "./BusinessLocationFormGroup";
    import BusinessLocationSelect from "./BusinessLocationSelect";

export default {
    name: "BusinessTextCaregivers",

    components: {BusinessLocationFormGroup, BusinessLocationSelect},

    props: {
        subject: false,
        fillMessage: '',
        fillRecipients: {
            type: Array,
            default: [],
        },
    },

    data() {
        return {

            maxLength : 290,
            warningLength: 155,
            'selectedUsers': [],
            'form': new Form({
                can_reply: 1,
                all: 0,
                message: '',
                recipients: [],
                business_id: "",
                businesses: '',
                debug: false,
            }),
            'numOfSets': 3,
            submitting: false,
        }
    },

    computed: {

        state(){

            return this.form.message.length >= 0 && this.form.message.length <= this.warningLength;
        },
        validFeedback(){

            return this.state === true ? '' : '';
        },
        invalidFeedback() {

            if ( this.form.message.length >= this.maxLength ) {

                return `This message exceeds the ${this.maxLength} character limit.`;
            }
            if ( this.form.message.length >= this.warningLength ) {

                return `YOUR CAREGIVERS MAY NOT RECEIVE THIS MESSAGE: Please consider limiting your SMS message to ${this.warningLength} characters or less.  You are above that limit and it may make your text message not readable.  The message will be split by many cell phone carriers and run the risk of not being received at all.  This is not an Ally limitation; We just want to ensure your messages are readable and received by all.`;
            }

            return '';
        },
        businesses() {
            return this.$store.state.business.businesses.filter(item => item.outgoing_sms_number);
        }
    },

    mounted() {
        if (this.fillMessage) {
            this.form.message = this.fillMessage;
        }
        if (this.fillRecipients) {
            this.selectedUsers = this.fillRecipients;
        }
    },

    methods: {

        textWarningClass( length ){

            if( length <= this.warningLength ) return 'text-success';

            if( length <= this.maxLength ) return 'text-warning';

            return 'text-danger';
        },
        addUser(user) {
            this.removeUser(user.id);   // prevent duplicates
            this.selectedUsers.push(user);
        },

        removeUser(id) {
            let index = this.selectedUsers.findIndex(item => item.id === id);
            if (index !== -1) this.selectedUsers.splice(index, 1);
        },

        searchDisplay(user) {
            return `${user.name} ${user.phone}`;
        },

        async submit()
        {
            this.form.clearError();

            if (this.selectedUsers.length == 0 && ! this.form.all) {
                this.form.addError('recipients', 'You must add at least one recipient.');
                return;
            }

            let confirmMessage = 'Are you sure you wish to send this text message to the ' + this.selectedUsers.length + ' selected recipients?';
            if (this.form.all) {
                confirmMessage = 'Are you sure you wish to send this text message to all active Caregivers?';
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            this.submitting = true;
            try {
                this.form.recipients = this.getRecipients();
                await this.form.post(`/business/communication/text-caregivers`);
                this.resetForm();
            }
            catch (e) {
                if (e.response.status == 418) {
                    // Message was sent but there were errors
                    this.resetForm();
                }

            }
            this.submitting = false;
        },

        getRecipients()
        {
            return this.selectedUsers.map(item => item.id);
        },

        resetForm()
        {
            this.form.can_reply = 1;
            this.form.all = 0;
            this.form.message = '';
            this.form.recipients = [];
            this.selectedUsers = [];
        },
    }
}
</script>
