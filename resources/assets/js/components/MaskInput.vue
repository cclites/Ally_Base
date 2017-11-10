<template>
    <b-input
           :autocomplete="autocomplete"
           :id="id"
           :name="name"
           v-model="localValue"
           @change="updateInput()"
    ></b-input>
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
                default: '',
            },
            name: {
                type: String,
                default: '',
            },
        },

        mounted() {
            switch(this.type)
            {
                case 'phone':
                    $(this.$el).mask('(000) 000-0000');
                    break;
                case 'ssn':
                    $(this.$el).mask('***-**-****', {'translation': {
                        '*': {pattern: /[\*0-9]/},
                    }});
                    break;
            }
        },

        computed: {
            autocomplete() {
                switch(this.type)
                {
                    case 'ssn':
                        return 'off';
                    default:
                        return 'on';
                }
            }
        },

        watch:{
            value(newVal, oldVal) {
                if (newVal !== oldVal){
                    this.localValue = newVal;
                }
            },
        },

        methods: {
            updateInput() {
                this.$emit('input', $(this.$el).val());
            },
        }
    }
</script>