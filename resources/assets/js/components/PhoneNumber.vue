<template>
    <b-card
        header-bg-variant="info"
        header-text-variant="white"
        header-tag="header"
        >
        <div slot="header">
            <b-row>
                <b-col>
                    <div class="mb-2 mt-1">{{ title }}</div>
                </b-col>
                <b-col>
                    <b-form-group horizontal label="Type" v-if="!isFixedType(type)" class="mb-0">
                        <b-form-select v-model="form.type" :options="types" size="sm" @input="typeChange" style="background-color: white;" :readonly="authInactive"></b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
        <form @submit.prevent="saveNumber()" @keydown="handleKeyDown($event.target.name)">
            <b-row>
                <b-col lg="6" sm="5" xs="12">
                    <b-form-group label="Phone Number" label-for="number">
                        <mask-input v-model="form.number" name="number" :readonly="authInactive"></mask-input>
                        <input-help :form="form" field="number" text="Enter full phone number."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="3" sm="3" xs="12">
                    <b-form-group label="Extension" label-for="extension">
                        <b-form-input
                                name="extension"
                                type="number"
                                maxlength="5"
                                v-model="form.extension"
                                class="input-sm"
                                :readonly="authInactive"
                        >
                        </b-form-input>
                        <input-help :form="form" field="extension" text="Enter an extension (Optional)."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="12">
                    <b-form-group label="Notes" label-for="notes">
                        <b-textarea
                                id="notes"
                                name="notes"
                                :rows="3"
                                v-model="form.notes"
                        >
                        </b-textarea>
                    </b-form-group>
                </b-col>
                <b-col lg="3" sm="4" xs="12">
                    <b-form-group>
                        <b-button variant="success"
                                  type="submit"
                                  :disabled="authInactive || submitting"
                                  v-if="buttonVisible"
                        >
                            <i class="fa fa-spinner fa-spin" v-show="submitting"></i> Save Number
                        </b-button>
                    </b-form-group>
                    <b-form-group>
                        <b-button variant="danger"
                                  v-if="this.phone.id"
                                  @click="destroy"
                                  title="Delete Number"
                                  :disabled="authInactive || submitting"
                                  class="mt-2">
                            <i class="fa fa-times"></i>
                        </b-button>
                    </b-form-group>
                </b-col>
            </b-row>
        </form>
        <div v-if="allowSms && this.phone.id">
            <div v-if="phone.receives_sms == 1" class="alert alert-info">You are receiving text messages at this number</div>
            <b-btn v-else variant="success" @click="setSmsNumber()">Receive text messages at this number</b-btn>
        </div>
    </b-card>
</template>

<script>
    import AuthUser from '../mixins/AuthUser';

    export default {
        mixins: [ AuthUser ],

        props: {
            'title': null,
            'type': null,
            'phone': {},
            'user': {},
            'action': {},
            'allowSms': false,
        },

        data() {
            return {
                form: new Form({
                    number: this.phone.number,
                    extension: this.phone.extension,
                    type: this.type,
                    user_id: _.isEmpty(this.user) ? undefined : this.user.id,
                    notes: this.phone.notes
                }),
                deleteForm: new Form({
                    id: this.phone.id,
                    _method: 'DELETE'
                }),
                buttonVisible: false,
                selectedType: this.type,
                types: [
                    { text: 'Home', value: 'home' },
                    { text: 'Work', value: 'work' },
                    { text: 'Mobile', value: 'mobile' },
                    { text: 'Other 1', value: 'other_1' },
                    { text: 'Other 2', value: 'other_2' },
                    { text: 'Other 3', value: 'other_3' }
                ],
                submitting: false,
            }
        },

        methods: {
            isFixedType(type) {
                let fixedTypes = ['primary', 'billing'];

                return fixedTypes.indexOf(type) === -1 ? false : true;
            },

            typeChange(value) {
                this.selectedType = value;
                this.buttonVisible = true;
            },

            async saveNumber() {
                this.submitting = true;
                try {
                    if (this.phone.id) {
                        const response = await this.form.put('/profile/phone/' + this.phone.id);
                        this.buttonVisible = false;
                        this.$emit('updated');
                    }
                    else {
                        const response = await this.form.post('/profile/phone');
                        this.buttonVisible = false;
                        this.$emit('created');
                    }
                }
                catch (e) {}
                this.submitting = false;
            },

            destroy() {
                this.deleteForm.post('/profile/phone/' + this.phone.id)
                    .then(response => {
                        this.$emit('deleted', this.phone.id);
                    })
            },

            handleKeyDown(target) {
                this.form.clearError(target);
                this.buttonVisible = true;
            },

            setSmsNumber() {
                axios.patch(`/profile/phone/${this.phone.id}/sms`)
                    .then(response => {
                        this.$emit('updated');
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
        },
    }
</script>
