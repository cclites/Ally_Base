<template>

    <b-modal id="confirmDelete" :title="title" v-model="showModal" size="lg">

        <div>This report is formatted to print on Avery 5160 labels.</div>
        <hr/>

        <div>
            For best results: <br/><br/>
            1 ) Open with Adobe Reader <br /><br />
            2) Select ACTUAL SIZE
        </div>

        <div class="my-4 d-flex">

            <div class="f-1 avery-image-container mx-auto">

                <img src="/images/avery_help.jpg" />
            </div>
            <div class="f-1 px-3">

                <b-form-group label="Top Margin" label-for="topmargin" description="Default is 55px, negative values allowed, too much will push labels onto next page" >
                    <b-form-input
                        id="topmargin"
                        type="number"
                        v-model="topmargin"
                    >
                    </b-form-input>
                </b-form-group>
                <b-form-group label="Left Margin" label-for="leftmargn" description="Default is 0px, negative values allowed, too much will distort alignment" >
                    <b-form-input
                        id="leftmargn"
                        type="number"
                        v-model="leftmargin"
                    >
                    </b-form-input>
                </b-form-group>
            </div>
        </div>
        <hr/>

        <div>

            Notes:<br />
            * This will skip those without an address on file<br/><br />
            * Each line is limited and will only display 30 characters<br/><br />
            <span style="padding-left:25px">If the system truncates a row, you will see an asterisk *</span><br/>
            <span style="padding-left:25px">We recommend that you page through and fix any truncated rows</span><br /><br />
            * We recommend that you print the first 3 pages to test alignment before printing all pages
        </div>

        <div slot="modal-footer">

            <b-btn :variant="cancelVariant" @click.prevent="onCancel()" :disabled="cancelDisabled">{{ cancelButton }}</b-btn>
            <b-btn :variant="noVariant" @click.prevent="onNo()" v-if="noButton" :disabled="noDisabled">{{ noButton }}</b-btn>
            <b-btn :variant="yesVariant" @click.prevent="onYes()" :disabled="yesDisabled">{{ yesButton }}</b-btn>
        </div>
    </b-modal>
</template>

<script>
export default {
    name: 'ConfirmModal',

    props: {
        title: {
            type: String,
            default: 'Avery 5160 Label Printing',
        },
        cancelButton: {
            type: String,
            default: 'Cancel',
        },
        yesButton: {
            type: String,
            default: 'I Understand, Download Labels PDF',
        },
        noButton: {
            type: Boolean,
            default: false,
        },
        yesVariant: {
            type: String,
            default: 'danger',
        },
        noVariant: {
            type: String,
            default: 'success',
        },
        cancelVariant: {
            type: String,
            default: 'secondary',
        },
        yesDisabled: {
            type: Boolean,
            default: false,
        },
        noDisabled: {
            type: Boolean,
            default: false,
        },
        cancelDisabled: {
            type: Boolean,
            default: false,
        },
        callback: {

            type: Function,
            default: () => {}
        },
        value: false,
    },

    data: () => ({

        cancelCallback: null,
        noCallback: null,
        topmargin: 55,
        leftmargin: 0
    }),

    computed: {

        showModal: {

            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        },
    },

    methods: {

        confirm(callback, cancelCallback = null, noCallback = null) {

            this.show = true;
            this.callback = callback;
            this.cancelCallback = cancelCallback;
            this.noCallback = noCallback;
        },

        onNo() {


            this.show = false;
            if (this.noCallback) {
                this.noCallback();
            }
        },

        onYes() {
            this.show = false;
            if (this.callback) {

                const data = {

                    topmargin: this.topmargin,
                    leftmargin: this.leftmargin
                };

                this.callback( data );
            }
        },

        onCancel() {

            this.showModal = false;
            if (this.cancelCallback) {

                this.cancelCallback();
            }
        }
    }
}
</script>

<style scoped>

    .avery-image-container {

        height: 450px;
        width: 450px;
    }

    .avery-image-container > img {

        height: 100%;
        width: 100%;
    }
</style>