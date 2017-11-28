<template>
    <b-card
        :header-bg-variant="headerVariant"
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
                        <b-form-select v-model="form.type" :options="types" size="sm" @input="typeChange" style="background-color: white;"></b-form-select>
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
                        <b-button id="save-profile" variant="success" type="submit" v-if="buttonVisible">Save Number</b-button>
                    </b-form-group>
                    <b-form-group v-if="!isFixedType(type)">
                        <b-button variant="danger"
                                  v-if="this.phone.id"
                                  @click="destroy"
                                  title="Delete Number"
                                  class="mt-2">
                            <i class="fa fa-times"></i>
                        </b-button>
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
                    type: this.type
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
                ]
            }
        },

        computed: {
            headerVariant() {
                return this.isFixedType(this.type) ? 'info' : 'secondary';
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

            saveNumber() {
                if (this.phone.id) {
                    this.form.put('/profile/phone/' + this.phone.id)
                        .then(response => {
                            this.buttonVisible = false;
                            this.$emit('updated');
                        });
                } else {
                    this.form.post('/profile/phone')
                        .then(response => {
                            this.buttonVisible = false;
                            this.$emit('created');
                        });
                }
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
            }
        },
    }
</script>
