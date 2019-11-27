<template>
    <b-modal :title="action" v-model="show">
        <div class="text-center mb-5">
            <h3>You must verify a PIN to continue</h3>
        </div>
        <div class="form-inline d-flex mb-5">
            <b-form-group label="PIN:" label-for="pin" class="m-auto">
                <b-form-input
                    v-model="pin"
                    type="text"
                    class="ml-3"
                />
            </b-form-group>
        </div>

        <div slot="modal-footer">
            <b-btn variant="default" @click.prevent="onCancel()">Cancel</b-btn>
            <b-btn variant="danger" @click.prevent="onYes()">Submit</b-btn>
        </div>
    </b-modal>
</template>

<script>
export default {
    name: 'PinConfirmationModel',

    props: {
    },

    data: () => ({
        show: false,
        action: 'continue',
        callback: null,
        cancelCallback: null,
        pin: '',
    }),

    methods: {
        confirm(actionText, callback, cancelCallback = null) {
            this.pin = '';
            this.show = true;
            this.action = actionText;
            this.callback = callback;
            this.cancelCallback = cancelCallback;
        },

        onYes() {
            if (this.pin == '') {
                return;
            }

            this.show = false;
            if (this.callback) {
                this.callback(this.pin);
            }
        },

        onCancel() {
            this.show = false;
            if (this.cancelCallback) {
                this.cancelCallback();
            }
        }
    }
}
</script>
