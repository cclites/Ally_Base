<template>
    <b-card
        header="Create Message"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
            <b-form-group>
                <div class="form-check">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="all" v-model="form.all" value="1">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Send to all active Caregivers</span>
                    </label>
                    <input-help :form="form" field="accepted_terms" text=""></input-help>
                </div>
            </b-form-group>
            <b-form-group label="Recipients" v-if="! form.all">
                <user-search-dropdown placeholder="Add Recipient" icon="fa-plus" :formatter="searchDisplay" @selectUser="addUser" type="sms" role="caregiver" :disabled="submitting" />

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
            <b-form-group label="Message">
                <b-textarea :rows="6" v-model="form.message" required :disabled="submitting"></b-textarea>
                <input-help :form="form" field="message" text=""></input-help>
            </b-form-group>
            <b-form-group>
                <b-button variant="info" type="submit" :disabled="submitting">
                    <i class="fa fa-spin fa-spinner" v-if="submitting"></i> Send
                </b-button>
            </b-form-group>
        </form>
    </b-card>
</template>

<script>
export default {
    name: "BusinessSmsCaregivers",

    props: {
        'subject': Boolean,
    },

    data() {
        return {
            'selectedUsers': [],
            'form': new Form({}),
            'numOfSets': 3,
            submitting: false,
        }
    },

    mounted() {
        this.resetForm();
    },

    computed: {
        countPerSet() {
            return Math.ceil(this.selectedUsers.length / this.numOfSets);
        },

        userSets() {
            let userSets = [];
            for (let i = 0; i < 3; i++) {
                let set = this.selectedUsers.filter((item, index) => {
                    return index % 3 === i;
                });
                userSets.push(set);
            }
            return userSets;
        },
    },

    methods: {
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
            if (this.selectedUsers.length == 0 && ! this.form.all) {
                this.form.addError('recipients', 'You must add at least one recipient.');
                return;
            }
            
            let confirmMessage = 'Are you sure you wish to send this SMS to the ' + this.selectedUsers.length + ' selected recipients?';
            if (this.form.all) {
                confirmMessage = 'Are you sure you wish to send this SMS to all active Caregivers?';
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            this.submitting = true;
            try {
                this.form.recipients = this.getRecipients();
                await this.form.post(`/business/communication/sms-caregivers`);
                this.resetForm();
            }
            catch (e) {}
            this.submitting = false;
        },

        getRecipients()
        {
            return this.selectedUsers.map(item => item.id);
        },

        resetForm()
        {
            this.form = new Form({
                all: 0,
                message: '',
                recipients: [],
            });
            this.selectedUsers = [];
        }
    }
}
</script>
