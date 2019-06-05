<template>
    <b-modal id="confirmDelete" :title="title" v-model="show">
        <div v-if="message"></div>
        <slot></slot>
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
            default: 'Are you sure?',
        },
        message: {
            type: String,
            default: '',
        },
        cancelButton: {
            type: String,
            default: 'Cancel',
        },
        yesButton: {
            type: String,
            default: 'Yes',
        },
        noButton: {
            type: String,
            default: '',
        },
        yesVariant: {
            type: String,
            default: 'info',
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
    },

    data: () => ({
        show: false,
        callback: null,
        cancelCallback: null,
        noCallback: null,
    }),

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
                this.callback();
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
