<template>
    <div :class="messageClass" role="alert" v-if="visible">
        <button type="button" class="close" aria-label="Close" @click="dismiss()"><span aria-hidden="true">&times;</span></button>
        <strong>{{ msg.type | capitalize }}!</strong> <span v-html="$options.filters.nl2br(msg.message)"></span>
    </div>
</template>

<script>
    export default {
        props: ['msg'],

        data() {
            return {
                'visible': true,
            }
        },

        mounted() {
            var message = this;
            setTimeout(function() {
                message.dismiss();
            }, 5000);
        },

        methods: {

            classFromType(type) {
                if (type === 'error') return 'danger';
                return type;
            },

            dismiss() {
                this.visible = false;
            }

        },

        computed: {
            messageClass: function() {
                return 'alert alert-' + this.classFromType(this.msg.type);
            }
        }
    }
</script>
