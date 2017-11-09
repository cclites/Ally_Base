<template>
    <input type="text" class="form-control" autocomplete="off" maxlength="14" :id="id" :name="name" :value="localValue" :type="localType" />
</template>

<script>
    const TYPES = [
        'phone','ssn'
    ];
    export default {
        data() {
            return {
                localValue: this.value
            }
        },

        props: {
            value: {
                default: null
            },
            type: {
                type: String,
                default: 'phone',
            },
            id: {
                type: String,
                default: null,
            },
            name: {
                type: String,
                default: null,
            },
        },

        mounted() {
            switch(this.type)
            {
                case 'phone':
                    $(this.$el).mask('+0 (000) 000-0000');
                    break;
                case 'ssn':
                    $(this.$el).mask('000-00-0000');
                    break;
            }
        },

        computed: {
            localType() {
                // We only allow certain types
                return this.type;
            },
        },

        watch:{
            value(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.localValue = newVal;
                }
            },
            localValue(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.$emit('input', newVal);
                }
            }
        },

        methods: {
            onInput(value, e) {
                this.localValue = value;
            },
        }
    }
</script>