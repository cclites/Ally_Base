<template>
    <b-card :header="title"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveNumber()" @keydown="handleKeyDown($event.target.name)">
            <b-row>
                <b-col lg="6" sm="5" xs="12">
                    <b-form-group label="Phone Number" label-for="number">
                        <b-form-input
                                id="number"
                                name="number"
                                type="text"
                                v-model="form.number"
                        >
                        </b-form-input>
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
            'user': {}
        },

        data() {
            return {
                form: new Form({
                    number: this.phone.number,
                    extension: this.phone.extension,
                }),
                buttonVisible: false,
            }
        },

        mounted() {

        },

        methods: {

            saveNumber() {
                if (this.user && this.user.id) {
                    // Update another's phone number
                }
                else {
                    // Update auth'd phone number
                    this.form.post('/profile/phone/' + this.type);
                }
            },

            handleKeyDown(target) {
                this.form.clearError(target);
                this.buttonVisible = true;
            }

        }


    }
</script>
