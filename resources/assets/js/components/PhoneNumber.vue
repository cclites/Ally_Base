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
                    <b-form-group horizontal label="Type" v-if="type != 'primary'" class="mb-0">
                        <b-form-select v-model="form.type" :options="types" size="sm" @input="typeChange"></b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
        </div>
        <form @submit.prevent="saveNumber()" @keydown="handleKeyDown($event.target.name)">
            <b-row>
                <b-col lg="6" sm="5" xs="12">
                    <b-form-group label="Phone Number" label-for="number">
                        <mask-input v-model="form.number" name="number" id="number"></mask-input>
                        <input-help :form="form" field="number" text="Enter full phone number."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="3" sm="3" xs="12">
                    <b-form-group label="Extension" label-for="extension">
                        <b-form-input
                                id="extension"
                                name="extension"
                                type="number"
                                maxlength="5"
                                v-model="form.extension"
                                class="input-sm"
                        >
                        </b-form-input>
                        <input-help :form="form" field="extension" text="Enter an extension (optional)."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="3" sm="4" xs="12">
                    <b-form-group>
                        <label class="col-form-label col-12 hidden-xs-down"><span>&nbsp;</span></label>
                        <b-button id="save-profile" variant="success" type="submit" v-if="buttonVisible">Save Number</b-button>
                    </b-form-group>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'title': null,
            'type': null,
            'phone': {},
            'user': {},
            'action': {}
        },

        data() {
            return {
                form: new Form({
                    number: this.phone.number,
                    extension: this.phone.extension,
                }),
                buttonVisible: false,
                types: [
                    { text: 'Primary', value: 'primary' },
                    { text: 'Home', value: 'home' },
                    { text: 'Work', value: 'work' },
                    { text: 'Mobile', value: 'mobile' },
                    { text: 'Other 1', value: 'other_1' },
                    { text: 'Other 2', value: 'other_2' },
                    { text: 'Other 3', value: 'other_3' }
                ]
            }
        },

        mounted() {

        },

        methods: {
            typeChange(value) {
                console.log('Type' + value);
                this.buttonVisible = true;
            },

            saveNumber() {
                let action = (this.action) ? this.action : '/profile/phone/' + this.type;
                this.form.post(action);
            },

            handleKeyDown(target) {
                this.form.clearError(target);
                this.buttonVisible = true;
            }
        },
    }
</script>
